<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    // List enrolled + add form
    public function index(Course $course)
    {
        // eager load for speed
        $enrollments = Enrollment::with('student.user')
            ->where('course_id', $course->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $allStudents = Student::with('user')->orderBy('id','desc')->get();

        return view('admin/courses/enrollments/index', compact('course','enrollments','allStudents'));
    }

    // Enroll a student to a course
    public function store(Request $request, Course $course)
    {
        $data = $request->validate([
            'student_id' => ['required','exists:students,id'],
            'level'      => ['nullable','integer','min:1'],
        ]);

        Enrollment::firstOrCreate([
            'course_id'  => $course->id,
            'student_id' => $data['student_id'],
        ], [
            'level'  => $data['level'] ?? null,
            'status' => 'active',
        ]);

        return back()->with('success', 'Student enrolled to course.');
    }

    // Remove enrollment (not delete student)
    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();
        return back()->with('success', 'Enrollment removed.');
    }
}
