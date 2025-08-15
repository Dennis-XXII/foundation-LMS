<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Support\FilePathBuilder; // <-- make sure this exists (see note below)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    /**
     * New assignment form (Upload Links) under a course.
     * Route: lecturer.courses.assignments.create
     */


    public function show(Assignment $assignment)
    {
        $this->authorize('view', $assignment);
        return redirect()->route('lecturer.assignments.edit', $assignment);
    }
    public function create(Course $course)
    {
        $this->authorize('update', $course); // CoursePolicy
        return view('lecturer.assignments.create', compact('course'));
    }

    /**
     * Store a new assignment. Attachment optional but mutually exclusive with URL.
     * Route: lecturer.courses.assignments.store
     */
    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'instruction'  => ['nullable', 'string', 'max:4000'],
            'level'        => ['nullable', 'integer', 'min:1'],
            'due_at'       => ['nullable', 'date'],
            'url'          => ['nullable', 'url', 'max:2048'],
            'file'         => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,zip', 'max:20480'], // 20MB
            'is_published' => ['sometimes', 'boolean'],
        ]);

        // Hard guard: don't allow both URL and file at once.
        if ($request->filled('url') && $request->hasFile('file')) {
            return back()->withErrors(['url' => 'Choose either a URL or a file, not both.'])->withInput();
        }

        $data['course_id']    = $course->id;
        $data['is_published'] = (int)($data['is_published'] ?? 1);

        // Save first (so we have an ID for a clean path)
        $assignment = DB::transaction(function () use ($data, $request, $course) {
            $assignment = Assignment::create($data);

            // If a file was uploaded, store on private "assignments" disk
            if ($request->hasFile('file')) {
                // Build a deterministic path like: course_{courseId}/assignment_{id}/<slug>.<ext>
                $finalPath = FilePathBuilder::assignmentPath($course->id, $assignment->id, $request->file('file'));
                // If you don't have FilePathBuilder, replace the line above with:
                // $finalPath = "course_{$course->id}/assignment_{$assignment->id}/".$request->file('file')->getClientOriginalName();

                Storage::disk('assignments')->putFileAs(
                    dirname($finalPath),
                    $request->file('file'),
                    basename($finalPath)
                );

                $assignment->update([
                    'file_path' => $finalPath,
                    'url'       => null, // ensure URL cleared if a file is uploaded
                ]);
            }

            return $assignment;
        });

        return to_route('lecturer.assignments.edit', $assignment)
            ->with('success', 'Assignment created.');
    }

    /**
     * Edit form (also used as the "Assess Student Uploads" page).
     * Route: lecturer.assignments.edit (shallow)
     */
    public function edit(Assignment $assignment)
    {
        $this->authorize('update', $assignment);

        // Preload for table/view performance
        $assignment->load([
            'course',
            'submissions.student.user' // student name/email
        ]);

        return view('lecturer.assignments.edit', compact('assignment'));
    }

    /**
     * Update assignment meta or replace attachment (exclusive with URL).
     * Route: lecturer.assignments.update
     */
    public function update(Request $request, Assignment $assignment)
    {
        $this->authorize('update', $assignment);

        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'instruction'  => ['nullable', 'string', 'max:4000'],
            'level'        => ['nullable', 'integer', 'min:1'],
            'due_at'       => ['nullable', 'date'],
            'url'          => ['nullable', 'url', 'max:2048'],
            'file'         => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,zip', 'max:20480'],
            'is_published' => ['sometimes', 'boolean'],
            'remove_file'  => ['sometimes', Rule::in(['1'])], // checkbox to clear file
        ]);

        // Same mutual exclusivity guard
        if ($request->filled('url') && $request->hasFile('file')) {
            return back()->withErrors(['url' => 'Choose either a URL or a file, not both.'])->withInput();
        }

        DB::transaction(function () use ($request, $assignment, &$data) {
            // Handle file removal
            if ($request->boolean('remove_file') && $assignment->file_path) {
                Storage::disk('assignments')->delete($assignment->file_path);
                $assignment->file_path = null;
            }

            // Handle new file upload (replaces old one)
            if ($request->hasFile('file')) {
                if ($assignment->file_path) {
                    Storage::disk('assignments')->delete($assignment->file_path);
                }

                $finalPath = FilePathBuilder::assignmentPath($assignment->course_id, $assignment->id, $request->file('file'));
                // If you don't have FilePathBuilder, replace with:
                // $finalPath = "course_{$assignment->course_id}/assignment_{$assignment->id}/".$request->file('file')->getClientOriginalName();

                Storage::disk('assignments')->putFileAs(
                    dirname($finalPath),
                    $request->file('file'),
                    basename($finalPath)
                );

                $assignment->file_path = $finalPath;
                $data['url'] = null; // clear URL if a file is set
            }

            // Set publish flag (keep previous if not provided)
            $data['is_published'] = (int)($data['is_published'] ?? $assignment->is_published);

            $assignment->fill($data)->save();
        });

        return back()->with('success', 'Assignment updated.');
    }

    /**
     * Secure file download from the private "assignments" disk.
     * Route (add): lecturer.assignments.download
     */
    public function download(Assignment $assignment)
    {
        $this->authorize('view', $assignment); // AssignmentPolicy::view should allow lecturers on the course

        if (!$assignment->file_path) {
            abort(404, 'No file attached to this assignment.');
        }

        return response()->download(
            Storage::disk('assignments')->path($assignment->file_path)
        );
    }

    /**
     * Clear the current attachment (keeps the record).
     * Route (add): lecturer.assignments.clear-file
     */
    public function clearFile(Assignment $assignment)
    {
        $this->authorize('update', $assignment);

        if ($assignment->file_path) {
            Storage::disk('assignments')->delete($assignment->file_path);
            $assignment->update(['file_path' => null]);
        }

        return back()->with('success', 'Attachment removed.');
    }

    /**
     * Delete an assignment (soft delete if model uses SoftDeletes).
     * Route: lecturer.assignments.destroy
     */
    public function destroy(Assignment $assignment)
    {
        $this->authorize('delete', $assignment);

        // Optional: also delete the file on force delete only.
        // If you prefer immediate deletion of file on soft delete, uncomment below.
        if ($assignment->file_path) {
            Storage::disk('assignments')->delete($assignment->file_path);
        }

        $assignment->delete();

        return to_route('lecturer.dashboard')->with('success', 'Assignment deleted.');
    }

    /**
     * Optional index if enabled in routes (list assignments for a course).
     * Route: lecturer.courses.assignments.index
     */
    public function index(Course $course)
    {
        $this->authorize('view', $course);

        $assignments = $course->assignments()
            ->latest('due_at')
            ->paginate(20)
            ->withQueryString();

        return view('lecturer.assignments.index', compact('course', 'assignments'));
    }

    /**
     * Submissions table for an assignment (used by “Assess Student Uploads”).
     * Route: GET /lecturer/assignments/{assignment}/submissions
     */
    public function submissions(Assignment $assignment)
    {
        $this->authorize('update', $assignment);

        $submissions = Submission::query()
            ->where('assignment_id', $assignment->id)
            ->with(['student.user'])
            ->latest('submitted_at')
            ->paginate(30);

        return view('lecturer.assignments.submissions', compact('assignment', 'submissions'));
    }
}
