<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SpecialProject;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SpecialProjectController extends Controller
{
    /**
     * Display a list of special projects for a course, filtered by student level and request parameters.
     * Route: student.special_projects.index
     */
    public function index(Request $request, Course $course)
    {
        // 1. Authorize that the student can view the course
        $this->authorize('view', $course);

        // 2. Get and validate filters
        $level = $request->integer('level');
        $week  = $request->integer('week');
        $day   = $request->query('day');

        $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'REVIEW'];
        if ($level && !in_array($level, [1, 2, 3], true)) { $level = null; }
        if ($week && !in_array($week, range(1, 8), true)) { $week = null; }
        if ($day && !in_array($day, $validDays, true)) { $day = null; }

        // 3. Get the student's enrolled level for this course
        $student_level = Enrollment::where('student_id', Auth::user()->student->id)
            ->where('course_id', $course->id)
            ->value('level');

        // 4. Build the query, filtering special projects by student's level and selected filters
        $specialProjects = $course->specialProjects()
            ->where('is_published', true)
            ->when($student_level !== null, function ($query) use ($student_level) {
                $query->where(function ($subQuery) use ($student_level) {
                    $subQuery->where('level', '<=', $student_level)
                            ->orWhereNull('level');
                });
            })
            ->when($level, fn($q) => $q->where('level', $level))
            ->when($week,  fn($q) => $q->where('week', $week))
            ->when($day,   fn($q) => $q->where('day', $day))
            ->with(['submissions' => function ($q) {
                $q->where('student_id', Auth::user()->student->id);
            }])
            ->withExists(['submissions as has_assessment' => function($query) {
                $query->where('student_id', Auth::user()->student->id)
                      ->whereHas('assessment');
            }])
            ->latest('due_at')
            ->paginate(15)
            ->withQueryString();

        return view('student.special_projects.index', compact(
            'course',
            'specialProjects',
            'student_level',
            'level',
            'week',
            'day'
        ));
    }

    public function show(SpecialProject $specialProject)
    {
        // 1. Authorize: Use SpecialProjectPolicy view check
        $this->authorize('view', $specialProject);

        $specialProject->load('course');
        $course = $specialProject->course;
        $student_id = Auth::user()->student->id;

        // 2. Find the student's submission for this special project
        $submission = Submission::query()
            ->where('special_project_id', $specialProject->id)
            ->where('student_id', $student_id)
            ->with('assessment')
            ->first();

        // 3. Determine status and available actions
        $hasAssessment = $submission?->assessment;
        $status = 'Open';
        if ($submission) {
            $status = $hasAssessment ? 'Graded' : 'Submitted';
        } elseif ($specialProject->due_at && $specialProject->due_at->isPast()) {
            $status = 'Closed';
        }

        $canSubmit = !$submission && (!$specialProject->due_at || $specialProject->due_at->isFuture());
        $canEdit = $submission && !$hasAssessment && (!$specialProject->due_at || $specialProject->due_at->isFuture());
        $canViewFeedback = $submission && $hasAssessment;

        return view('student.special_projects.show', compact(
            'specialProject',
            'course',
            'submission',
            'status',
            'canSubmit',
            'canEdit',
            'canViewFeedback'
        ));
    }

    /**
     * Allow students to download special project attachments.
     * Route: student.special_projects.download
     */
    public function download(SpecialProject $specialProject)
    {
        $this->authorize('view', $specialProject);

        if (!$specialProject->file_path) {
            abort(404, 'No file attached to this special project.');
        }

        $disk = Storage::disk('special_projects');

        if (!$disk->exists($specialProject->file_path)) {
             abort(404, 'File not found on disk.');
        }

        $fileName = pathinfo($specialProject->file_path, PATHINFO_BASENAME);
        $downloadName = $specialProject->title . '_' . $fileName;

        return response()->download(
            $disk->path($specialProject->file_path),
            $downloadName
        );
    }
}
