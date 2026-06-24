<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\SpecialProject;
use App\Models\Course;
use App\Models\Submission;
use App\Support\FilePathBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SpecialProjectController extends Controller
{
    /**
     * UPDATED: Show the details page for a single special project.
     * Route: lecturer.special_projects.show
     */
    public function show(SpecialProject $specialProject)
    {
        $this->authorize('view', $specialProject->course); // Use CoursePolicy for viewing
        $specialProject->load(['course', 'submissions.student.user']); // Eager load necessary relations

        return view('lecturer.special_projects.show', compact('specialProject'));
    }

    /**
     * NEW: Handle file downloads.
     * Route: lecturer.special_projects.download
     */
    public function download(SpecialProject $specialProject)
    {
        $this->authorize('view', $specialProject); // SpecialProjectPolicy::view

        if (!$specialProject->file_path) {
            abort(404, 'No file attached to this special project.');
        }

        // Use 'special_projects' disk
        if (!Storage::disk('special_projects')->exists($specialProject->file_path)) {
             abort(404, 'File not found on disk.');
        }

        return response()->download(
            Storage::disk('special_projects')->path($specialProject->file_path)
        );
    }

    /**
     * Show the main special projects page (Post or Assess tab).
     * Route: lecturer.courses.special_projects.index
     */
    public function index(Request $request, Course $course)
    {
        $this->authorize('view', $course);
        $level = (int) $request->query('level');
        $tab   = $request->query('tab');
        $week  = (int) $request->query('week');
        $day   = $request->query('day');
        $isAssess = $tab === 'assess';
        $specialProjects = null;
        $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'REVIEW'];
        if ($day && !in_array($day, $validDays, true)) $day = null;
        if (!$week) $week = null;
        if (!$level) $level = null;

        if ($isAssess) {
            $specialProjects = SpecialProject::query()
                ->where('course_id', $course->id)
                ->when($level, fn($q) => $q->where('level', $level))
                ->with(['submissions', 'submissions.student.user', 'submissions.assessment'])
                ->latest('due_at')->get();
        } else {
            $specialProjects = SpecialProject::query()
                ->where('course_id', $course->id)
                ->when($level, fn($q) => $q->where('level', $level))
                ->when($week,  fn($q) => $q->where('week', $week))
                ->when($day,   fn($q) => $q->where('day', $day))
                ->latest('due_at')->paginate(15)->withQueryString();
        }
        return view('lecturer.special_projects.index', compact('course','specialProjects','level','tab','isAssess','week','day'));
    }

    /**
     * New special project form (Upload Links) under a course.
     * Route: lecturer.courses.special_projects.create
     */
    public function create(Course $course)
    {
        $this->authorize('update', $course);
        return view('lecturer.special_projects.create', compact('course'));
    }

    /**
     * Store a new special project.
     * Route: lecturer.courses.special_projects.store
     */
    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'instruction'  => ['nullable', 'string', 'max:65535'],
            'level'        => ['required', 'integer', 'min:1', 'max:3'],
            'due_at'       => ['nullable', 'date'],
            'file'         => ['nullable', 'file', 'extensions:pdf,doc,docx,zip', 'max:20480'],
            'is_published' => ['sometimes', 'boolean'],
            'week'         => ['nullable', 'integer', 'min:1', 'max:8'],
            'day'          => ['nullable', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,REVIEW'],
        ]);
        $data['course_id'] = $course->id;
        $data['is_published'] = (int)($data['is_published'] ?? 1);

        $specialProject = DB::transaction(function () use ($request, $course, $data) {
            $specialProject = SpecialProject::create($data);
            if ($request->hasFile('file')) {
                $finalPath = FilePathBuilder::specialProjectPath($specialProject->course_id, $specialProject->id, $request->file('file'));
                Storage::disk('special_projects')->putFileAs(dirname($finalPath), $request->file('file'), basename($finalPath));
                $specialProject->update(['file_path' => $finalPath]);
            }
            return $specialProject;
        });

        return to_route('lecturer.special_projects.show', $specialProject)
            ->with('success', 'Special Project created successfully.');
    }

    /**
     * Edit form for a special project.
     * Route: lecturer.special_projects.edit (shallow)
     */
    public function edit(SpecialProject $specialProject)
    {
        $this->authorize('update', $specialProject);
        $specialProject->load(['course']);
        return view('lecturer.special_projects.edit', compact('specialProject'));
    }

    /**
     * Update special project meta or replace attachment.
     * Route: lecturer.special_projects.update
     */
    public function update(Request $request, SpecialProject $specialProject)
    {
        $this->authorize('update', $specialProject);
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'instruction'  => ['nullable', 'string', 'max:4000'],
            'level'        => ['nullable', 'integer', 'min:1'],
            'due_at'       => ['nullable', 'date'],
            'url'          => ['nullable', 'url', 'max:2048'],
            'file'         => ['nullable', 'file', 'extensions:pdf,doc,docx,ppt,pptx,zip', 'max:20480'],
            'is_published' => ['sometimes', 'boolean'],
            'remove_file'  => ['sometimes', Rule::in(['1'])],
            'week'         => ['nullable', 'integer', 'min:1', 'max:8'],
            'day'          => ['nullable', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,REVIEW'],
        ]);
        if ($request->filled('url') && $request->hasFile('file')) {
             return back()->withErrors(['url' => 'Choose either a URL or a file, not both.'])->withInput();
        }

        DB::transaction(function () use ($request, $specialProject, &$data) {
            if ($request->boolean('remove_file') && $specialProject->file_path) {
                Storage::disk('special_projects')->delete($specialProject->file_path);
                $specialProject->file_path = null;
            }
            if ($request->hasFile('file')) {
                if ($specialProject->file_path) { Storage::disk('special_projects')->delete($specialProject->file_path); }
                $finalPath = FilePathBuilder::specialProjectPath($specialProject->course_id, $specialProject->id, $request->file('file'));
                Storage::disk('special_projects')->putFileAs(dirname($finalPath), $request->file('file'), basename($finalPath));
                $specialProject->file_path = $finalPath;
                 if (isset($data['url'])) { $data['url'] = null; }
            }
            $data['is_published'] = (int)($data['is_published'] ?? $specialProject->is_published);
            $specialProject->fill($data)->save();
        });

        return to_route('lecturer.special_projects.show', $specialProject)
            ->with('success', 'Special Project updated.');
    }

    /**
     * Clear the current attachment (keeps the record).
     * Route: lecturer.special_projects.clear-file
     */
    public function clearFile(SpecialProject $specialProject)
    {
        $this->authorize('update', $specialProject);
        if ($specialProject->file_path) {
            Storage::disk('special_projects')->delete($specialProject->file_path);
            $specialProject->update(['file_path' => null]);
        }
        return back()->with('success', 'Attachment removed.');
    }

    /**
     * Delete a special project.
     * Route: lecturer.special_projects.destroy
     */
    public function destroy(SpecialProject $specialProject)
    {
        $this->authorize('delete', $specialProject);
        $course = $specialProject->course;
        if ($specialProject->file_path) {
            Storage::disk('special_projects')->delete($specialProject->file_path);
        }
        $specialProject->delete();
        $redirectParams = http_build_query(array_filter(['level' => request('level'), 'week' => request('week'), 'day' => request('day')]));
        $url = route('lecturer.courses.special_projects.index', $course) . ($redirectParams ? '?'.$redirectParams : '');
        return redirect($url)->with('success', 'Special Project deleted.');
    }

    /**
     * Submissions table for a special project.
     * Route: GET /lecturer/special-projects/{special_project}/submissions
     */
    public function submissions(SpecialProject $specialProject)
    {
        $this->authorize('update', $specialProject);
        $submissions = Submission::query()
            ->where('special_project_id', $specialProject->id)
            ->with(['student.user'])
            ->latest('submitted_at')->paginate(30);
        return view('lecturer.special_projects.submissions', compact('specialProject', 'submissions'));
    }
}