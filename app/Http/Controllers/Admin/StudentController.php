<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with('user')->latest()->paginate(20);
        return view('admin.students.index', compact('students'));
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => [
                'required', 
                'string', 
                'unique:eligible_students,student_id', // Must not be in whitelist already
                'unique:students,student_id'           // Must not have an active account already
            ],
        ]);

        \App\Models\EligibleStudent::create([
            'student_id' => $request->student_id
        ]);

        return redirect()->route('admin.students.index')
            ->with('success', 'Student ID added to eligible list.');
    }

    /*
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required','string','max:255'],
            'email'      => ['required','email','max:255','unique:users,email'],
            'student_id' => ['required','string','max:8','unique:students,student_id'],
            'password'   => ['required','string','min:8'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'role'     => 'student',
            'password' => Hash::make($data['password']),
            'nickname' => 'Test Student',
        ]);

        Student::create([
            'user_id'    => $user->id,
            'student_id' => $data['student_id'],
        ]);

        return redirect()->route('admin.students.index')->with('success','Student created.');
    }
    */

    public function edit(Student $student)
    {
        $student->load('user');
        return view('admin.students.edit', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $student->load('user');

        $data = $request->validate([
            'name'       => ['required','string','max:255'],
            'email'      => ['required','email','max:255','unique:users,email,'.$student->user_id],
            'student_id' => ['required','string','max:8','unique:students,student_id,'.$student->id],
            'password'   => ['nullable','string','min:6'],
        ]);

        $student->user->update([
            'name'     => $data['name'],
            'email'    => $data['email'],
            // update password only if provided
            'password' => $data['password'] ? Hash::make($data['password']) : $student->user->password,
        ]);

        $student->update(['student_id' => $data['student_id']]);

        return redirect()->route('admin.students.index')->with('success','Student updated.');
    }

    public function destroy(Student $student)
    {
        // remove both student profile and user
        $student->user()->delete(); // cascades if you use soft deletes
        $student->delete();

        return back()->with('success','Student deleted.');
    }
}
