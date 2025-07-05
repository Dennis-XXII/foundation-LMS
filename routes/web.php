<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\StudentMiddleware;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SignupController;
use App\Http\Controllers\Student\StudentController;

//  Always accessible
Route::get('/', function () {
    return view('welcome');
})->name('welcome');


Route::middleware(RedirectIfAuthenticated::class)->group(function () {
    Route::get('/register/student', [SignupController::class, 'showStudentRegistrationForm'])->name('register.student');
    Route::post('/register/student', [SignupController::class, 'registerStudent']);

    Route::get('/register/lecturer', [SignupController::class, 'showLeturerRegistrationForm'])->name('register.mentor');
    Route::post('/register/lecturer', [SignupController::class, 'registerLecturer']);

    Route::get('/register/admin', [SignupController::class, 'showAdminRegistrationForm'])->name('register.admin');
    Route::post('/register/admin', [SignupController::class, 'registerAdmin']);

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
});

//  Student-only routes
Route::middleware(['auth', StudentMiddleware::class])->group(function () {
    Route::get('/student/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
});

//Logout

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');