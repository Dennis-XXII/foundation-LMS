<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Support\FilePathBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    /**
     * UPDATED: Show the details page for a single assignment.
     * Route: lecturer.assignments.show
     */
    public function show(Assignment $assignment)
    {
        $this->authorize('view', $assignment->course); // Use CoursePolicy for viewing
        $assignment->load(['course', 'submissions.student.user']); // Eager load necessary relations

        return view('lecturer.assignments.show', compact('assignment'));
    }

    /**
     * NEW: Handle file downloads.
     * Route: lecturer.assignments.download
     */
    public function download(Assignment $assignment)
    {
        $this->authorize('view', $assignment); // AssignmentPolicy::view

        if (!$assignment->file_path) {
            abort(404, 'No file attached to this assignment.');
        }

        // Use 'assignments' disk
        if (!Storage::disk('assignments')->exists($assignment->file_path)) {
             abort(404, 'File not found on disk.');
        }

        return response()->download(
            Storage::disk('assignments')->path($assignment->file_path)
        );
    }

    /**
     * Show the main assignments page (Post or Assess tab).
     * Route: lecturer.courses.assignments.index
     */
    public function index(Request $request, Course $course)
    {
        // ... (index logic remains the same) ...
        $this->authorize('view', $course);
        $level = (int) $request->query('level');
        $tab   = $request->query('tab');
        $week  = (int) $request->query('week');
        $day   = $request->query('day');
        $isAssess = $tab === 'assess';
        $assignments = null;
        $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'REVIEW'];
        if ($day && !in_array($day, $validDays, true)) $day = null;
        if (!$week) $week = null;
        if (!$level) $level = null;

        if ($isAssess) {
            $assignments = Assignment::query()
                ->where('course_id', $course->id)
                ->when($level, fn($q) => $q->where('level', $level))
                ->with(['submissions', 'submissions.student.user', 'submissions.assessment'])
                ->latest('due_at')->get();
        } else {
            $assignments = Assignment::query()
                ->where('course_id', $course->id)
                ->when($level, fn($q) => $q->where('level', $level))
                ->when($week,  fn($q) => $q->where('week', $week))
                ->when($day,   fn($q) => $q->where('day', $day))
                ->latest('due_at')->paginate(15)->withQueryString();
        }
        return view('lecturer.assignments.index', compact('course','assignments','level','tab','isAssess','week','day'));
    }

    /**
     * New assignment form (Upload Links) under a course.
     * Route: lecturer.courses.assignments.create
     */
    public function create(Course $course)
    {
        // ... (create logic remains the same) ...
        $this->authorize('update', $course);
        return view('lecturer.assignments.create', compact('course'));
    }

    /**
     * Store a new assignment.
     * Route: lecturer.courses.assignments.store
     */
    public function store(Request $request, Course $course)
    {
        // ... (validation and file handling remain the same) ...
        $this->authorize('update', $course);
        $data = $request->validate([ /* validation rules */
            'title'        => ['required', 'string', 'max:255'],
            'instruction'  => ['nullable', 'string', 'max:65535'],
            'level'        => ['required', 'integer', 'min:1', 'max:3'],
            'due_at'       => ['nullable', 'date'],
            'file'         => ['nullable', 'file', 'mimes:pdf,doc,docx,zip', 'max:20480'],
            'is_published' => ['sometimes', 'boolean'],
            'week'         => ['nullable', 'integer', 'min:1', 'max:8'],
            'day'          => ['nullable', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,REVIEW'],
        ]);
        $data['course_id'] = $course->id;
        $data['is_published'] = (int)($data['is_published'] ?? 1);

        $assignment = DB::transaction(function () use ($request, $course, $data) {
            $assignment = Assignment::create($data);
            if ($request->hasFile('file')) {
                $finalPath = FilePathBuilder::assignmentPath($assignment->course_id, $assignment->id, $request->file('file'));
                Storage::disk('assignments')->putFileAs(dirname($finalPath), $request->file('file'), basename($finalPath));
                $assignment->update(['file_path' => $finalPath]);
            }
            return $assignment;
        });

        // UPDATED: Redirect to the new 'show' page
        return to_route('lecturer.assignments.show', $assignment)
            ->with('success', 'Assignment created successfully.');
    }

    /**
     * Edit form for an assignment.
     * Route: lecturer.assignments.edit (shallow)
     */
    public function edit(Assignment $assignment)
    {
        // ... (edit logic remains the same) ...
        $this->authorize('update', $assignment);
        $assignment->load(['course']);
        return view('lecturer.assignments.edit', compact('assignment'));
    }

    /**
     * Update assignment meta or replace attachment.
     * Route: lecturer.assignments.update
     */
    public function update(Request $request, Assignment $assignment)
    {
        // ... (validation and file handling remain the same) ...
        $this->authorize('update', $assignment);
        $data = $request->validate([ /* validation rules */
            'title'        => ['required', 'string', 'max:255'],
            'instruction'  => ['nullable', 'string', 'max:4000'],
            'level'        => ['nullable', 'integer', 'min:1'],
            'due_at'       => ['nullable', 'date'],
            'url'          => ['nullable', 'url', 'max:2048'], // Assuming URL might be added later
            'file'         => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,zip', 'max:20480'],
            'is_published' => ['sometimes', 'boolean'],
            'remove_file'  => ['sometimes', Rule::in(['1'])],
            'week'         => ['nullable', 'integer', 'min:1', 'max:8'],
            'day'          => ['nullable', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,REVIEW'],
        ]);
        if ($request->filled('url') && $request->hasFile('file')) { /* url/file conflict */
             return back()->withErrors(['url' => 'Choose either a URL or a file, not both.'])->withInput();
        }

        DB::transaction(function () use ($request, $assignment, &$data) {
            if ($request->boolean('remove_file') && $assignment->file_path) {
                Storage::disk('assignments')->delete($assignment->file_path);
                $assignment->file_path = null; // Important: Clear the path in DB too
            }
            if ($request->hasFile('file')) {
                if ($assignment->file_path) { Storage::disk('assignments')->delete($assignment->file_path); }
                $finalPath = FilePathBuilder::assignmentPath($assignment->course_id, $assignment->id, $request->file('file'));
                Storage::disk('assignments')->putFileAs(dirname($finalPath), $request->file('file'), basename($finalPath));
                $assignment->file_path = $finalPath;
                 if (isset($data['url'])) { $data['url'] = null; }
            }
            $data['is_published'] = (int)($data['is_published'] ?? $assignment->is_published);
            $assignment->fill($data)->save(); // Use fill()->save() to trigger events/mutators if any
        });

        // UPDATED: Redirect to the 'show' page
        return to_route('lecturer.assignments.show', $assignment)
            ->with('success', 'Assignment updated.');
    }

    /**
     * Clear the current attachment (keeps the record).
     * Route: lecturer.assignments.clear-file
     */
    public function clearFile(Assignment $assignment)
    {
        // ... (clearFile logic remains the same) ...
        $this->authorize('update', $assignment);
        if ($assignment->file_path) {
            Storage::disk('assignments')->delete($assignment->file_path);
            $assignment->update(['file_path' => null]);
        }
        return back()->with('success', 'Attachment removed.');
    }

    /**
     * Delete an assignment.
     * Route: lecturer.assignments.destroy
     */
    public function destroy(Assignment $assignment)
    {
        // ... (destroy logic remains the same, redirects to index) ...
        $this->authorize('delete', $assignment);
        $course = $assignment->course;
        if ($assignment->file_path) {
            Storage::disk('assignments')->delete($assignment->file_path);
        }
        $assignment->delete();
        $redirectParams = http_build_query(array_filter(['level' => request('level'), 'week' => request('week'), 'day' => request('day')]));
        $url = route('lecturer.courses.assignments.index', $course) . ($redirectParams ? '?'.$redirectParams : '');
        return redirect($url)->with('success', 'Assignment deleted.');
    }

    /**
     * Submissions table for an assignment (used by “Assess Student Uploads”).
     * Route: GET /lecturer/assignments/{assignment}/submissions
     */
    public function submissions(Assignment $assignment)
    {
        // ... (submissions logic remains the same) ...
        $this->authorize('update', $assignment);
        $submissions = Submission::query()
            ->where('assignment_id', $assignment->id)
            ->with(['student.user'])
            ->latest('submitted_at')->paginate(30);
        return view('lecturer.assignments.submissions', compact('assignment', 'submissions'));
    }
}