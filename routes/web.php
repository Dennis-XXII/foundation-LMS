<?php

use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
// Controllers
// ─────────────────────────────────────────────────────────────────────────────
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SignupController;

// Student
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Student\SubmissionController as StudentSubmissions;
use App\Http\Controllers\Student\MaterialController as StudentMaterials;

// Lecturer
use App\Http\Controllers\Lecturer\DashboardController as LecturerDashboard;
use App\Http\Controllers\Lecturer\MaterialController  as LecturerMaterials;
use App\Http\Controllers\Lecturer\AssignmentController as LecturerAssignments;
use App\Http\Controllers\Lecturer\AssessmentController as LecturerAssessments;
use App\Http\Controllers\Lecturer\AnnouncementController as LecturerAnnouncements;
use App\Http\Controllers\Lecturer\EnrollmentController as LecturerEnrollments;
use App\Http\Controllers\Lecturer\CourseController as LecturerCourses;
use App\Http\Controllers\Lecturer\SubmissionController as LecturerSubmissions;

// Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\CourseController as AdminCourses;
use App\Http\Controllers\Admin\StudentController as AdminStudents;
use App\Http\Controllers\Admin\LecturerController as AdminLecturers;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncements;
use App\Models\Student;

// ─────────────────────────────────────────────────────────────────────────────
// Public / Guest
// ─────────────────────────────────────────────────────────────────────────────
Route::view('/', 'welcome')->name('welcome');

Route::middleware('guest')->group(function () {
    // Auth: login
    Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');

    // Auth: role‑specific registration
    Route::controller(SignupController::class)->group(function () {
        // Students
        Route::get('/register/student',  'showStudentRegistrationForm')->name('register.student');
        Route::post('/register/student', 'registerStudent');

        // Lecturers
        Route::get('/register/lecturer',  'showLecturerRegistrationForm')->name('register.lecturer');
        Route::post('/register/lecturer', 'registerLecturer');

        // Admins
        Route::get('/register/admin',  'showAdminRegistrationForm')->name('register.admin');
        Route::post('/register/admin', 'registerAdmin');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Authenticated (all roles)
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// ─────────────────────────────────────────────────────────────────────────────
// STUDENT AREA  (UI: “Home page for students”, tiles: Materials / Worksheets / Self‑study / Upload Links / Scores)
// Students only see their ONE enrolled course on dashboard
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth','student'])
    ->prefix('student')->as('student.')
    ->group(function () {
        // Landing: show the single course shortcut + quick tiles
        Route::get('/dashboard', [StudentDashboard::class, 'index'])->name('dashboard');

        // Course home (detail page)
        Route::get('/courses/{course}', [StudentDashboard::class, 'course'])
            ->middleware('can:view,course')
            ->name('courses.show');

        // Materials (three tiles: lesson / worksheet / self‑study) + optional level filter
        Route::get('/courses/{course}/materials', [StudentMaterials::class, 'index'])
            ->middleware('can:view,course')
            ->name('materials.index');
            
        Route::get('materials/{material}', [StudentMaterials::class, 'show'])
            ->name('materials.show');

        // Assignments (aka “Upload links”)
        Route::get('/courses/{course}/assignments', [StudentDashboard::class, 'assignments'])
            ->middleware('can:view,course')
            ->name('assignments.index');

        // Submissions (student creates/updates their own submission to an assignment)
        Route::resource('assignments.submissions', StudentSubmissions::class)
            ->only(['create','store','edit','update','show'])
            ->scoped(); // {assignment}/{submission}

        Route::get('/materials/{material}/download', [StudentMaterials::class, 'download'])
            ->name('materials.download');
    });

// ─────────────────────────────────────────────────────────────────────────────
// LECTURER AREA (UI: manage Lesson Materials / Worksheets / Self‑study; create Upload Links; Assess; Announcements)
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth','lecturer'])
    ->prefix('lecturer')->as('lecturer.')
    ->group(function () {
        Route::get('/dashboard', [LecturerDashboard::class, 'index'])->name('dashboard');

        Route::resource('courses', LecturerCourses::class)
        ->only(['index', 'show', 'edit', 'update']);

        // Materials (files or links; types: lesson/worksheet/self‑study)
        Route::resource('courses.materials', LecturerMaterials::class)
            ->parameters(['materials' => 'material'])
            ->shallow();


        Route::get('/materials/{material}/download', [LecturerMaterials::class, 'download'])
            ->name('materials.download');

        Route::delete('/materials/{material}/file', [LecturerMaterials::class, 'clearFile'])
            ->name('lecturer.materials.clear-file');

        // Assignments (“upload links” w/ title, instructions, due_at, attachment)
        Route::resource('courses.assignments', LecturerAssignments::class)
            ->parameters(['assignments' => 'assignment'])
            ->shallow();

        Route::get('/assignments/{assignment}/download', [LecturerAssignments::class, 'download'])
            ->name('assignments.download');


        // Assessments (grade + comment on submissions)

        // --- Assessment Overview Page (NEW) ---
        Route::get('courses/{course}/assessments', [LecturerAssessments::class, 'index'])
            ->name('courses.assessments.index');

        // --- Create / Edit Assessments for a Submission ---

        Route::get('/submissions/{submission}/assessments/create', [LecturerAssessments::class, 'create'])
            ->name('submissions.assessments.create');

        Route::get('/submissions/{submission}/assessments/{assessment}/edit', [LecturerAssessments::class, 'edit'])
            ->name('submissions.assessments.edit');

        Route::post('/assessments/save', [LecturerAssessments::class, 'save'])
            ->name('submissions.assessments.save');

        Route::delete('/submissions/{submission}/assessments/{assessment}', [LecturerAssessments::class, 'destroy'])
            ->name('submissions.assessments.destroy');

        // --- Show Submissions for a Single Assignment ---
        Route::get('/assignments/{assignment}/submissions', [LecturerSubmissions::class, 'index'])
         ->name('assignments.submissions.index');

        Route::get('/submissions/{submission}/download', [LecturerSubmissions::class, 'download']) // Example
        ->name('submissions.download');

        // Announcements authored by lecturer (course‑scoped or global if your policy allows)
        Route::resource('announcements', LecturerAnnouncements::class)
            ->except(['show']);

        // Enrollments: manage students in the lecturer's courses
        Route::prefix('courses/{course}')->name('courses.')->group(function () {
        Route::get('students', [LecturerEnrollments::class, 'index'])
            ->name('students.index');
        Route::get('students/create', [LecturerEnrollments::class, 'create'])
            ->name('students.create');
        Route::post('students', [LecturerEnrollments::class, 'store'])
            ->name('students.store');
        Route::delete('students/{student}', [LecturerEnrollments::class, 'destroy'])
            ->name('students.destroy');
        });

    });

// ─────────────────────────────────────────────────────────────────────────────
// ADMIN AREA (UI: one course management, add/remove students, lecturers roster, announcements)
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth','admin'])
    ->prefix('admin')->as('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

        // Course CRUD (even if you run a single “active” course, keep REST — future‑proof)
        Route::resource('courses', AdminCourses::class)
            ->parameters(['courses' => 'course']);


        Route::resource('courses.enrollments', \App\Http\Controllers\Admin\EnrollmentController::class)
            ->only(['index','store','destroy'])
            ->parameters(['enrollments' => 'enrollment'])
            ->shallow(); // => /admin/enrollments/{enrollment}

         Route::resource('students', AdminStudents::class)
            ->only(['index','create','store','edit','update','destroy']);

        // Lecturers roster
        Route::resource('lecturers', AdminLecturers::class)
            ->parameters(['lecturers' => 'lecturer']);

        // Global / course announcements
        Route::resource('announcements', AdminAnnouncements::class);
    });

// (Optional) Fallback → welcome
// Route::fallback(fn () => to_route('welcome'));
