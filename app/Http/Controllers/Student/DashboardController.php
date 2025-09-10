<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // Allowed types based on your DB schema
    private const TYPES = ['lesson', 'worksheet', 'self_study'];

    // Normalize type param from route (self-study → self_study)
    private function normalizeType(string $type): string
    {
        return $type === 'self-study' ? 'self_study' : $type;
    }

    /**
     * Show student dashboard with their latest course and announcements
     */
    public function index()
    {
        $student = Auth::user()->student;
        $course  = $student?->enrollments()
            ->with('course')
            ->latest()
            ->first()?->course;

        // Get all enrollments for the student
        $enrollments = $student->enrollments()->with('course')->get();
        $activeCourse = optional($enrollments->first())->course;

        if (! $course) {
            return view('student.dashboard', [
                'student'       => $student,
                'course'        => null,
                'announcements' => collect(),
                'assignments'   => collect(),
                'materials'     => collect(),
            ]);
        }



        $announcements = $course->announcements()
            ->orderByDesc('posted_at') // exists in schema
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        $assignments = $course->assignments()
            ->latest('due_at')
            ->take(5)
            ->get();

        $materials = $course->materials()
            ->where('is_published', true)
            ->latest('uploaded_at')
            ->take(5)
            ->get();

        $student_level = $enrollments->firstWhere('course_id', $course->id)?->level;


        return view('student.dashboard', compact(
            'student', 'course', 'announcements', 'assignments', 'materials', 'activeCourse', 'enrollments', 'student_level'
        ));
    }

    /***
     * View a single course details

    public function course(Course $course)
    {
        $this->authorize('view', $course);

        $course->load(['materials', 'assignments']);

        return view('student.courses.show', compact('course'));
    }
    */


    /**
     * View assignments in a course
     */
    public function assignments(Course $course)
    {
        // 1. Authorize that the student can view the course
        $this->authorize('view', $course);

        // 2. Get the student's enrolled level for this course *before* the main query
        $student_level = Enrollment::where('student_id', Auth::id()) // Recommended to use user_id to match Auth::id()
            ->where('course_id', $course->id)
            ->value('level');

        // 3. Build the query, filtering assignments by the student's level
        $assignments = $course->assignments()
            // ✅ THIS IS THE KEY IMPROVEMENT:
            // Securely filter the assignments at the database level.
            ->when($student_level, function ($query) use ($student_level) {
                $query->where(function ($subQuery) use ($student_level) {
                    $subQuery->where('level', '<=', $student_level)
                            ->orWhereNull('level'); // Also include assignments without a level
                });
            })
            // Eager load this student's submissions for the filtered assignments
            ->with(['submissions' => function ($q) {
                $q->where('student_id', Auth::id()); // Using Auth::id() for consistency
            }])
            ->latest('due_at')
            ->paginate(20)
            ->withQueryString();

        // 4. Return the view with the securely filtered data.
        // The old, unsafe if/else block is no longer needed.
        return view('student.assignments.index', compact(
            'course',
            'assignments',
            'student_level'
        ));
    }
    
}
