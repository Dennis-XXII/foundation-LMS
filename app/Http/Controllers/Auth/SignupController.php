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
use App\Models\Lecturer; // <-- add this model

class SignupController extends Controller
{
    /** STUDENT: show form */
    public function showStudentRegistrationForm()
    {
        return view('auth.register-student');
    }

    /** LECTURER: show form */
    public function showLecturerRegistrationForm()
    {
        return view('auth.register-lecturer');
    }

    /** ADMIN: show form */
    public function showAdminRegistrationForm()
    {
        return view('auth.register-admin');
    }

    /**
     * STUDENT: handle registration.
     */
    public function registerStudent(Request $request)
    {
        $data = $request->validate([
            'student_id'   => ['required','string',
            'exists:eligible_students,student_id', // Check if Admin added it
            Rule::unique('students', 'student_id')  // Check if already registered
            ],
            'name'         => ['required','string','max:255'],
            'nickname'     => ['required','string','max:255'],
            'email'        => ['required','string','email:rfc,dns','max:255', Rule::unique('users','email')],
            'password'     => ['required','string','min:8','confirmed'],
            'line_id'      => ['nullable','string','max:255'],
            'phone_number' => ['nullable','string','max:255'],
            'faculty'      => ['required','string','max:255'],
            'language'     => ['required','string','max:255'],
            'level'        => ['required','string','max:255'],
        ]);

        $data['email'] = Str::lower(trim($data['email']));

        DB::transaction(function () use (&$data) {
            $user = User::query()->create([
                'name'         => $data['name'],
                'nickname'     => $data['nickname'],
                'email'        => $data['email'],
                'password'     => Hash::make($data['password']),
                'role'         => 'student',
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

        return to_route('login')->with('success', 'Student account created successfully! Please log in.');
    }

    /**
     * LECTURER: handle registration.
     * Adjust column names to your actual lecturers table schema.
     * Common columns: employee_id / staff_id / lecturer_code, department, faculty.
     */
    public function registerLecturer(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required','string','max:255'],
            'nickname'     => ['required','string','max:255'],
            'email'        => ['required','string','email:rfc,dns','max:255', Rule::unique('users','email')],
            'password'     => ['required','string','min:8','confirmed'],
            'line_id'      => ['nullable','string','max:255'],
            'phone_number' => ['nullable','string','max:255'],

            // Lecturer profile (customize as per your migration)
            'employee_id'  => ['nullable','string','max:64', Rule::unique('lecturers','employee_id')], // if column exists
            'faculty'      => ['nullable','string','max:255'],
            'department'   => ['nullable','string','max:255'],
            'language'     => ['nullable','string','max:255'],
        ]);

        $data['email'] = Str::lower(trim($data['email']));

        DB::transaction(function () use (&$data) {
            $user = User::query()->create([
                'name'         => $data['name'],
                'nickname'     => $data['nickname'],
                'email'        => $data['email'],
                'password'     => Hash::make($data['password']),
                'role'         => 'lecturer',
                'line_id'      => $data['line_id']      ?? null,
                'phone_number' => $data['phone_number'] ?? null,

                // Optional, if you store these on users:
                'faculty'      => $data['faculty']    ?? null,
                'language'     => $data['language']   ?? null,
            ]);

            $lecturer = Lecturer::query()->create([
                'user_id'     => $user->id,
                'employee_id' => $data['employee_id'] ?? null, // or 'staff_id' / 'lecturer_code'
                'faculty'     => $data['faculty']     ?? null,
                'department'  => $data['department']  ?? null,
            ]);

            // Auto-enrol to Course #1 (id = 1) without breaking existing links
            $lecturer->courses()->syncWithoutDetaching([1]);
        });

        return to_route('login')->with('success', 'Lecturer account created successfully! You can now log in.');
    }

    /**
     * ADMIN: handle registration.
     * Usually no separate Admin model — just set role='admin'.
     */
    public function registerAdmin(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required','string','max:255'],
            'nickname'     => ['required','string','max:255'],
            'email'        => ['required','string','email:rfc,dns','max:255', Rule::unique('users','email')],
            'password'     => ['required','string','min:8','confirmed'],
            'line_id'      => ['nullable','string','max:255'],
            'phone_number' => ['nullable','string','max:255'],
        ]);

        $data['email'] = Str::lower(trim($data['email']));

        User::query()->create([
            'name'         => $data['name'],
            'nickname'     => $data['nickname'],
            'email'        => $data['email'],
            'password'     => Hash::make($data['password']),
            'role'         => 'admin',
            'line_id'      => $data['line_id']      ?? null,
            'phone_number' => $data['phone_number'] ?? null,
        ]);

        return to_route('login')->with('success', 'Admin account created successfully! You can now log in.');
    }
}
