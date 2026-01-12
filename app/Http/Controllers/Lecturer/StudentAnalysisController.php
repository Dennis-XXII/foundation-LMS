<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class StudentAnalysisController extends Controller
{
    /**
     * Display the list of enrolled students with progress analysis.
     * Route: lecturer.courses.progress.index
     */
    public function index(Course $course)
    {
        $this->authorize('view', $course);

        // 1. Fetch Enrollments, eagerly loading Student, User, and Submissions
        $enrollments = Enrollment::query()
            ->with([
                'student.user',
                // Load only the current student's submissions for this course's assignments
                'student.submissions' => function ($q) use ($course) {
                    $q->whereHas('assignment', fn($qq) => $qq->where('course_id', $course->id))
                        ->with('assessment', 'assignment'); 
                }
            ])
            ->where('course_id', $course->id)
            // Join for sorting by name (Fixing orderByRelation issue)
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.name') 
            ->select('enrollments.*')
            ->get()
            // 2. Calculate completion metrics for each student
            ->map(function ($enrollment) use ($course) {
                
                // Get ALL published assignments relevant to this student's level
                $visibleAssignments = Assignment::where('course_id', $course->id)
                    ->where('is_published', true)
                    ->when($enrollment->level, fn($q) => $q->where(function ($w) use ($enrollment) {
                        $w->whereNull('level')->orWhere('level', '<=', $enrollment->level);
                    }))
                    ->get();

                $visibleAssignmentsCount = $visibleAssignments->count();

                $submittedCount = 0;
                $gradedCount = 0;

                $submissions = $enrollment->student->submissions;
                
                // Check submissions against the list of visible (relevant) assignments
                foreach($visibleAssignments as $assignment) {
                    $submission = $submissions->firstWhere('assignment_id', $assignment->id);
                    if ($submission) {
                        $submittedCount++;
                        if ($submission->assessment) {
                            $gradedCount++;
                        }
                    }
                }

                $completionPercentage = $visibleAssignmentsCount > 0 
                    ? round(($submittedCount / $visibleAssignmentsCount) * 100) 
                    : 0;

                $enrollment->assignment_stats = [
                    'visible_count' => $visibleAssignmentsCount,
                    'submitted_count' => $submittedCount,
                    'graded_count' => $gradedCount,
                    'completion_percentage' => $completionPercentage,
                ];

                return $enrollment;
            });

        return view('lecturer.students.progress', compact('course', 'enrollments'));
    }
}