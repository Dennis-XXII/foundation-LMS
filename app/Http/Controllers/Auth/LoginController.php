<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login_identifier' => ['required', 'string'], // This will be Email OR Student ID
            'password'         => ['required', 'string'],
        ]);

        // 1. Try to find user by email (for Admin/Lecturer)
        $user = \App\Models\User::where('email', $credentials['login_identifier'])->first();

        // 2. If not found, try to find by Student ID (via relationship)
        if (!$user) {
            $student = \App\Models\Student::where('student_id', $credentials['login_identifier'])->first();
            if ($student) {
                $user = $student->user;
            }
        }

        if ($user && Hash::check($credentials['password'], $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();

            return match ($user->role) {
                'student'  => to_route('student.dashboard'),
                'lecturer' => to_route('lecturer.dashboard'),
                'admin'    => to_route('admin.dashboard'),
                default    => to_route('welcome'),
            };
        }

        return back()->withErrors(['login_identifier' => 'Invalid credentials.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('welcome');
    }
}
