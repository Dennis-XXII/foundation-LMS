<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
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

        return view('student.dashboard', compact(
            'student', 'course', 'announcements', 'assignments', 'materials'
        ));
    }

    /**
     * View a single course details
     */
    public function course(Course $course)
    {
        $this->authorize('view', $course);

        $course->load(['materials', 'assignments']);

        return view('student.courses.show', compact('course'));
    }

    /**
     * View all materials in a course (with optional filters)
     */
    public function materials(Course $course)
    {
        $this->authorize('view', $course);

        $materials = $course->materials()
            ->where('is_published', true)
            ->orderByDesc('uploaded_at')
            ->paginate(20);

        return view('student.materials.index', compact('course', 'materials'));
    }

    /**
     * View materials by type and level
     */
    public function materialsByTypeLevel(Course $course, string $type, ?int $level = null)
    {
        $this->authorize('view', $course);

        $dbType = $this->normalizeType($type);

        abort_unless(in_array($dbType, self::TYPES, true), 404);

        $materials = $course->materials()
            ->where('is_published', true)
            ->where('type', $dbType)
            ->when($level, fn($q) => $q->where('level', $level))
            ->orderByDesc('uploaded_at')
            ->paginate(20);

        return view('student.materials.index', compact('course', 'materials', 'dbType', 'level'));
    }

    /**
     * View assignments in a course
     */
    public function assignments(Course $course)
    {
        $this->authorize('view', $course);

    $assignments = $course->assignments()
        ->latest('due_at')
        ->with(['submissions' => function ($q) {
            $q->where('student_id', Auth::user()->student->id);
        }])
       ->paginate(20)
       ->withQueryString();

        return view('student.assignments.index', compact('course', 'assignments'));
    }
}
