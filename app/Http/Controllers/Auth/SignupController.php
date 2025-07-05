<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;

class SignUpController extends Controller
{
    public function showStudentRegistrationForm()
    {
        return view('auth.register-student'); // Create this Blade view
    }

    /**
     * Show the mentor registration form.
     */
    public function showMentorRegistrationForm()
    {
        return view('auth.register-mentor'); // Create this Blade view
    }

    public function showAdminRegistrationForm()
    {
        return view('auth.register-admin'); // Create this Blade view
    }

    /**
     * Handle student registration.
     */
    public function registerStudent(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'line_id' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'student_id' => 'required|string|unique:students,student_id',
            'faculty' => 'required|string|max:255',
            'language' => 'required|string|max:255',
            'level' => 'required|string|max:255',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'nickname' => $data['nickname'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']), // Use bcrypt for hashing
            'role' => 'student',
            'line_id' => $data['line_id'],
            'phone_number' => $data['phone_number'],
            'faculty' => $data['faculty'],
            'language' => $data['language'],
            'level' => $data['level'],
        ]);

        Student::create([
            'user_id' => $user->id,
            'student_id' => $data['student_id'],
        ]);

        return redirect()->route('login')->with('success', 'Student account created successfully!');
    }

}
