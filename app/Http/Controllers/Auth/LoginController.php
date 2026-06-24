<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showStudentLoginForm()
    {
        return response()
            ->view('auth.login-student')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    public function showLecturerLoginForm()
    {
        return response()
            ->view('auth.login-lecturer')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    public function loginStudent(Request $request)
    {
        $credentials = $request->validate([
            'login_identifier' => ['required', 'string'],
            'password'         => ['required', 'string'],
        ]);

        // 1. Try by Student ID
        $student = \App\Models\Student::where('student_id', $credentials['login_identifier'])->first();
        if ($student) {
            $user = $student->user;
            if ($user && Hash::check($credentials['password'], $user->password)) {
                Auth::login($user);
                $request->session()->regenerate();
                return to_route('student.dashboard');
            }
        }

        // 2. Try by Email
        $user = \App\Models\User::where('email', $credentials['login_identifier'])->first();
        if ($user && $user->role === 'student' && Hash::check($credentials['password'], $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();
            return to_route('student.dashboard');
        }

        return back()->withErrors(['login_identifier' => 'Invalid student credentials.'])->withInput();
    }

    public function loginLecturer(Request $request)
    {
        $credentials = $request->validate([
            'login_identifier' => ['required', 'string'],
            'password'         => ['required', 'string'],
        ]);

        $user = \App\Models\User::where('email', $credentials['login_identifier'])->first();

        if ($user && in_array($user->role, ['lecturer', 'admin']) && Hash::check($credentials['password'], $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();

            return match ($user->role) {
                'lecturer' => to_route('lecturer.dashboard'),
                'admin'    => to_route('admin.dashboard'),
                default    => to_route('welcome'),
            };
        }

        return back()->withErrors(['login_identifier' => 'Invalid staff credentials.'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('welcome');
    }
}
