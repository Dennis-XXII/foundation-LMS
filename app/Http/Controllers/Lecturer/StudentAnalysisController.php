<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\SpecialProject;
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
                // Load only the current student's submissions for this course's special projects
                'student.submissions' => function ($q) use ($course) {
                    $q->whereHas('specialProject', fn($qq) => $qq->where('course_id', $course->id))
                        ->with('assessment', 'specialProject'); 
                }
            ])
            ->where('course_id', $course->id)
            // Join for sorting by name (Fixing orderByRelation issue)
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->whereNull('students.deleted_at')
            ->whereNull('users.deleted_at')
            ->orderBy('users.name') 
            ->select('enrollments.*')
            ->get()
            // 2. Calculate completion metrics for each student
            ->map(function ($enrollment) use ($course) {
                if (!$enrollment->student) {
                    return null;
                }
                
                // Get ALL published special projects relevant to this student's level
                $visibleSpecialProjects = SpecialProject::where('course_id', $course->id)
                    ->where('is_published', true)
                    ->when($enrollment->level, fn($q) => $q->where(function ($w) use ($enrollment) {
                        $w->whereNull('level')->orWhere('level', '<=', $enrollment->level);
                    }))
                    ->get();

                $visibleSpecialProjectsCount = $visibleSpecialProjects->count();

                $submittedCount = 0;
                $gradedCount = 0;

                $submissions = $enrollment->student->submissions;
                
                // Check submissions against the list of visible special projects
                foreach($visibleSpecialProjects as $specialProject) {
                    $submission = $submissions->firstWhere('special_project_id', $specialProject->id);
                    if ($submission) {
                        $submittedCount++;
                        if ($submission->assessment) {
                            $gradedCount++;
                        }
                    }
                }

                $completionPercentage = $visibleSpecialProjectsCount > 0 
                    ? round(($submittedCount / $visibleSpecialProjectsCount) * 100) 
                    : 0;

                $enrollment->special_project_stats = [
                    'visible_count' => $visibleSpecialProjectsCount,
                    'submitted_count' => $submittedCount,
                    'graded_count' => $gradedCount,
                    'completion_percentage' => $completionPercentage,
                ];

                return $enrollment;
            })
            ->filter()
            ->values();

        return view('lecturer.students.progress', compact('course', 'enrollments'));
    }
}