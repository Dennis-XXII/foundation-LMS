<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // Allowed types based on your DB schema
    private const TYPES = ['lesson', 'homework', 'self_study'];

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
                'specialProjects' => collect(),
                'materials'     => collect(),
                'student_level'=> null,
            ]);
        }

        $announcements = $course->announcements()
            ->orderByDesc('posted_at')
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        $specialProjects = $course->specialProjects()
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
            'student', 'course', 'announcements', 'specialProjects', 'materials', 'activeCourse', 'enrollments', 'student_level'
        ));
    }

    /**
     * View special projects in a course
     */
    public function specialProjects(Course $course)
    {
        $this->authorize('view', $course);

        $student_level = Enrollment::where('student_id', Auth::id())
            ->where('course_id', $course->id)
            ->value('level');

        $specialProjects = $course->specialProjects()
            ->when($student_level, function ($query) use ($student_level) {
                $query->where(function ($subQuery) use ($student_level) {
                    $subQuery->where('level', '<=', $student_level)
                            ->orWhereNull('level');
                });
            })
            ->with(['submissions' => function ($q) {
                $q->where('student_id', Auth::id());
            }])
            ->latest('due_at')
            ->paginate(20)
            ->withQueryString();

        return view('student.special_projects.index', compact(
            'course',
            'specialProjects',
            'student_level'
        ));
    }
}
