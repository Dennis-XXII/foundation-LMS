<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

// Models
use App\Models\User;
use App\Models\Student;

class SignupController extends Controller
{
    public function showStudentRegistrationForm()
    {
        return view('auth.register-student');
    }

    // NOTE: your routes use "lecturer", not "mentor".
    // Either rename this method OR change your routes.
    public function showLecturerRegistrationForm()
    {
        return view('auth.register-lecturer');
    }

    public function showAdminRegistrationForm()
    {
        return view('auth.register-admin');
    }

    /**
     * Handle student registration.
     */
    public function registerStudent(Request $request)
    {
        // 1) Validate input
        $data = $request->validate([
            'name'         => ['required','string','max:255'],
            'nickname'     => ['required','string','max:255'],
            'email'        => ['required','string','email:rfc,dns','max:255', Rule::unique('users','email')],
            'password'     => ['required','string','min:8','confirmed'],
            'line_id'      => ['nullable','string','max:255'],
            'phone_number' => ['nullable','string','max:255'],
            'student_id'   => ['required','string','max:64', Rule::unique('students','student_id')],
            'faculty'      => ['required','string','max:255'],
            'language'     => ['required','string','max:255'],
            'level'        => ['required','string','max:255'],
        ]);

        // 2) Normalize a couple of fields (helps avoid duplicate emails differing by case/space)
        $data['email'] = Str::lower(trim($data['email']));

        // 3) Create user + student atomically
        DB::transaction(function () use (&$data) {
            /** @var \App\Models\User $user */
            $user = User::query()->create([
                'name'         => $data['name'],
                'nickname'     => $data['nickname'],
                'email'        => $data['email'],
                'password'     => Hash::make($data['password']),
                'role'         => 'student',

                // if these columns exist on your users table:
                'line_id'      => $data['line_id']      ?? null,
                'phone_number' => $data['phone_number'] ?? null,
                'faculty'      => $data['faculty'],
                'language'     => $data['language'],
                'level'        => $data['level'],
            ]);

            Student::query()->create([
                'user_id'    => $user->id,
                'student_id' => $data['student_id'],
            ]);
        });

        // 4) Keep your current UX: send them to login page with a flash
        return to_route('login')->with('success', 'Student account created successfully! Please log in.');
    }
}
