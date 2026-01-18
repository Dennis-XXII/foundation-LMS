<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\EligibleStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with('user')->latest()->paginate(20);
        return view('admin.students.index', compact('students'));
    }

// app/Http/Controllers/Admin/StudentController.php

    public function wlCreate()
    {
        // Eager load 'registeredStudent' to avoid N+1 query issues
        $whitelistedStudents = \App\Models\EligibleStudent::with('registeredStudent')
            ->latest()
            ->get();
        
        return view('admin.students.create', compact('whitelistedStudents'));
    }

    // New Whitelist Store Method
    public function wlStore(Request $request)
    {
        $request->validate([
            'student_id' => [
                'required', 
                'string', 
                'unique:eligible_students,student_id',
                'unique:students,student_id'
            ],
        ]);

        EligibleStudent::create(['student_id' => $request->student_id]);

        return redirect()->route('admin.students.wlCreate')
            ->with('success', 'Student ID added to whitelist.');
    }

    // New Whitelist Destroy Method
    public function wlDestroy(EligibleStudent $eligible)
    {
        $eligible->delete();
        return back()->with('success', 'Student ID removed from whitelist.');
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
