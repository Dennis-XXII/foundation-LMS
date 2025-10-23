<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Needed for download

class AssignmentController extends Controller
{
    /**
     * Display a list of assignments for a course, filtered by student level and request parameters.
     * Route: student.assignments.index
     */
    public function index(Request $request, Course $course)
    {
        // 1. Authorize that the student can view the course
        $this->authorize('view', $course);

        // 2. Get and validate filters (similar to Lecturer controller)
        $level = $request->integer('level');
        $week  = $request->integer('week');
        $day   = $request->query('day');

        $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'REVIEW'];
        if ($level && !in_array($level, [1, 2, 3], true)) { $level = null; }
        if ($week && !in_array($week, range(1, 8), true)) { $week = null; }
        if ($day && !in_array($day, $validDays, true)) { $day = null; }

        // 3. Get the student's enrolled level for this course *before* the main query
        $student_level = Enrollment::where('student_id', Auth::user()->student->id) // Use student relation ID
            ->where('course_id', $course->id)
            ->value('level');

        // 4. Build the query, filtering assignments by the student's level AND the selected filters
        $assignments = $course->assignments()
            // Only show published assignments to students
            ->where('is_published', true)
            // Securely filter by student's max level
            ->when($student_level !== null, function ($query) use ($student_level) {
                $query->where(function ($subQuery) use ($student_level) {
                    $subQuery->where('level', '<=', $student_level)
                            ->orWhereNull('level');
                });
            })
            // Apply user's selected filters
            ->when($level, fn($q) => $q->where('level', $level))
            ->when($week,  fn($q) => $q->where('week', $week))
            ->when($day,   fn($q) => $q->where('day', $day))
            // Eager load this student's submissions for the filtered assignments
            ->with(['submissions' => function ($q) {
                 // Use student relation ID consistently
                $q->where('student_id', Auth::user()->student->id);
            }])
            // Eager load assessment existence check - more efficient
            ->withExists(['submissions as has_assessment' => function($query) {
                // Check if a submission exists for this student AND that submission has an assessment
                $query->where('student_id', Auth::user()->student->id)
                      ->whereHas('assessment');
            }])
            ->latest('due_at') // Or created_at, depending on desired default sort
            ->paginate(15) // Match lecturer pagination
            ->withQueryString();

        // 5. Return the view with the securely filtered data and filters.
        return view('student.assignments.index', compact(
            'course',
            'assignments',
            'student_level',
            'level', // Pass filter value
            'week',  // Pass filter value
            'day'    // Pass filter value
        ));
    }

    /**
     * Allow students to download assignment attachments.
     * Route: student.assignments.download
     */
    public function download(Assignment $assignment)
    {
        // Authorize: Use AssignmentPolicy view check (handles published status and level)
        $this->authorize('view', $assignment);

        if (!$assignment->file_path) {
            abort(404, 'No file attached to this assignment.');
        }

        // Use the 'assignments' disk configured in filesystems.php
        $disk = Storage::disk('assignments');

        if (!$disk->exists($assignment->file_path)) {
             abort(404, 'File not found on disk.');
        }

        // Generate a user-friendly download name
        $fileName = pathinfo($assignment->file_path, PATHINFO_BASENAME); // Get original filename part if stored
        $downloadName = $assignment->title . '_' . $fileName; // Customize as needed

        return response()->download(
            $disk->path($assignment->file_path),
            $downloadName // Optional: provide a nice filename
        );
    }
}
