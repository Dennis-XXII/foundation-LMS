<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * List all students enrolled in the course.
     */
    public function index(Course $course)
    {
        $this->authorize('update', $course); // lecturers assigned to this course

        // Eager load student->user for name/email
        $enrollments = Enrollment::with(['student.user'])
            ->where('course_id', $course->id)
            ->orderByRelation('student.user.name') // Laravel 11 helper; if older: join or sort later.
            ->get();

        return view('lecturer.students.index', compact('course', 'enrollments'));
    }

    /**
     * Add a student by Student ID to the course.
     */
    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'student_id' => ['required', 'string', 'min:3', 'max:32'], // your student_id is 8 chars; allow flexible
            'level'      => ['nullable', 'integer', 'min:1', 'max:3'],
        ]);

        // Find student record by student_id (students table, not users)
        $student = Student::where('student_id', $validated['student_id'])->first();

        if (!$student) {
            return back()->withErrors(['student_id' => 'Student ID not found.'])->withInput();
        }

        try {
            Enrollment::updateOrCreate(
                ['student_id' => $student->id, 'course_id' => $course->id],
                ['level' => $validated['level'] ?? null, 'status' => 'active']
            );
        } catch (QueryException $e) {
            // In case unique constraint throws before updateOrCreate applies (older MySQL)
            return back()->withErrors(['student_id' => 'This student is already enrolled in the course.']);
        }

        return back()->with('success', 'Student added to course.');
    }

    /**
     * Remove ONE student from the course.
     */
    public function destroy(Course $course, Enrollment $enrollment)
    {
        $this->authorize('update', $course);

        // Safety: ensure this enrollment belongs to the given course
        if ($enrollment->course_id !== $course->id) {
            abort(404);
        }

        $enrollment->delete();

        return back()->with('success', 'Student removed from course.');
    }

    /**
     * Quick search helper: GET /find?student_id=...
     * Returns a very small view fragment or redirects back with a flash message.
     */
    public function find(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $request->validate([
            'student_id' => ['required', 'string', 'min:3'],
        ]);

        $student = Student::with('user')->where('student_id', $request->student_id)->first();

        if (!$student) {
            return back()->withErrors(['student_id' => 'Student not found.']);
        }

        return back()->with('info', "Found: {$student->user?->name} ({$student->student_id}) • {$student->user?->email}");
    }

    /**
     * Clear all students from this course (dangerous; you already have a confirm on the button).
     */
    public function clear(Course $course)
    {
        $this->authorize('update', $course);

        Enrollment::where('course_id', $course->id)->delete();

        return back()->with('success', 'All students removed from this course.');
    }
}
