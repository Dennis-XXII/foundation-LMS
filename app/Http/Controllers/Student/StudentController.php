<?php

namespace App\Http\Controllers\Student;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StudentController extends Controller
{
        public function dashboard()
    {

        return view('student.dashboard');
    }
    // Show Student Profile
   public function show()
{
    // 1. Get the user
    $user = auth()->user();

    // 2. Eager load the student relationship to prevent a second database query in the view
    $user->load('student');

    return view('student.profile', [
        'user' => $user,
        'student' => $user->student, // This is now already loaded
    ]);
}
}
