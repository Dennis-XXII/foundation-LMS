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
use App\Http\Controllers\Student\SpecialProjectController as StudentSpecialProjects;
use App\Http\Controllers\Student\UsefulLinkController as StudentUsefulLinks;

// Lecturer
use App\Http\Controllers\Lecturer\DashboardController as LecturerDashboard;
use App\Http\Controllers\Lecturer\MaterialController  as LecturerMaterials;
use App\Http\Controllers\Lecturer\SpecialProjectController as LecturerSpecialProjects;
use App\Http\Controllers\Lecturer\AssessmentController as LecturerAssessments;
use App\Http\Controllers\Lecturer\AnnouncementController as LecturerAnnouncements;
use App\Http\Controllers\Lecturer\EnrollmentController as LecturerEnrollments;
use App\Http\Controllers\Lecturer\CourseController as LecturerCourses;
use App\Http\Controllers\Lecturer\SubmissionController as LecturerSubmissions;
use App\Http\Controllers\Lecturer\StudentAnalysisController as StudentAnalysisController;
use App\Http\Controllers\Lecturer\UsefulLinkController as LecturerUsefulLinks;

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
    // Auth: login (Students)
    Route::get('/login',  [LoginController::class, 'showStudentLoginForm'])->name('login');
    Route::post('/login/student', [LoginController::class, 'loginStudent'])->name('login.student.post')->middleware('throttle:5,1');

    // Auth: login (Lecturers & Admins)
    Route::get('/login/lecturer', [LoginController::class, 'showLecturerLoginForm'])->name('login.lecturer');
    Route::post('/login/lecturer', [LoginController::class, 'loginLecturer'])->name('login.lecturer.post')->middleware('throttle:5,1');

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
// STUDENT AREA
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth','student'])
    ->prefix('student')->as('student.')
    ->group(function () {
        // Landing: show the single course shortcut + quick tiles
        Route::get('/dashboard', [StudentDashboard::class, 'index'])->name('dashboard');

        // Course home (detail page) - Optional, currently handled by DashboardController?
        // Route::get('/courses/{course}', [StudentDashboard::class, 'course'])
        //     ->middleware('can:view,course')
        //     ->name('courses.show');

        // Profile
        Route::get('/profile', [\App\Http\Controllers\Student\StudentController::class, 'show'])
            ->name('profile.show');

        // Materials routes
        Route::get('/courses/{course}/materials', [StudentMaterials::class, 'index'])
            ->middleware('can:view,course')
            ->name('materials.index');
        Route::get('/courses/{course}/materials/list', [StudentMaterials::class, 'list']) 
            ->middleware('can:view,course')
            ->name('materials.list');
        Route::get('materials/{material}', [StudentMaterials::class, 'show'])
             ->middleware('can:view,material') // Assuming MaterialPolicy exists
            ->name('materials.show');
        Route::get('/materials/{material}/download', [StudentMaterials::class, 'download'])
            ->middleware('can:view,material') // Assuming MaterialPolicy exists
            ->name('materials.download');

        // --- Special Projects routes ---
        Route::get('/courses/{course}/special-projects', [StudentSpecialProjects::class, 'index'])
            ->middleware('can:view,course')
            ->name('special_projects.index');

        // Useful Links
        Route::get('/courses/{course}/useful-links', [StudentUsefulLinks::class, 'index'])
            ->middleware('can:view,course')
            ->name('courses.useful_links.index');
        Route::get('/special-projects/{special_project}', [StudentSpecialProjects::class, 'show'])
            ->middleware('can:view,special_project') // Assuming SpecialProjectPolicy exists
            ->name('special_projects.show');
        Route::get('/special-projects/{special_project}/download', [StudentSpecialProjects::class, 'download'])
            ->middleware('can:view,special_project') // Assuming SpecialProjectPolicy exists
            ->name('special_projects.download');

        // --- Submissions routes ---
        Route::resource('special-projects.submissions', StudentSubmissions::class)
            ->only(['create','store','edit','update','show'])
            ->scoped(); // {special_project}/{submission}

         // Need a download route for student submissions too
        Route::get('/submissions/{submission}/download', [StudentSubmissions::class, 'download']) // <-- ADDED (assuming download method exists)
            ->middleware('can:view,submission') // Assuming SubmissionPolicy exists
            ->name('submissions.download'); // Make sure name is unique if needed

    });

// ─────────────────────────────────────────────────────────────────────────────
// LECTURER AREA
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
        Route::get('/courses/{course}/materials', [LecturerMaterials::class, 'index']) 
            ->name('courses.materials.index');
        Route::get('/courses/{course}/materials/list', [LecturerMaterials::class, 'list']) 
            ->name('materials.list');
        Route::get('/courses/{course}/materials/all', [LecturerMaterials::class, 'listAll']) 
            ->name('materials.all');
        Route::get('/materials/{material}/download', [LecturerMaterials::class, 'download'])
            ->name('materials.download');
        Route::delete('/materials/{material}/file', [LecturerMaterials::class, 'clearFile'])
            ->name('materials.clear-file'); // Corrected name mismatch

        // Special Projects (“upload links” w/ title, instructions, due_at, attachment)
        Route::resource('courses.special-projects', LecturerSpecialProjects::class)
            ->parameters(['special-projects' => 'special_project'])
            ->names([
                'index' => 'courses.special_projects.index',
                'create' => 'courses.special_projects.create',
                'store' => 'courses.special_projects.store',
                'show' => 'special_projects.show',
                'edit' => 'special_projects.edit',
                'update' => 'special_projects.update',
                'destroy' => 'special_projects.destroy',
            ])
            ->shallow();

        // Useful Links CRUD
        Route::resource('courses.useful-links', LecturerUsefulLinks::class)
            ->parameters(['useful-links' => 'useful_link'])
            ->names([
                'index' => 'courses.useful_links.index',
                'create' => 'courses.useful_links.create',
                'store' => 'courses.useful_links.store',
                'show' => 'useful_links.show',
                'edit' => 'useful_links.edit',
                'update' => 'useful_links.update',
                'destroy' => 'useful_links.destroy',
            ])
            ->shallow();
        Route::get('/special-projects/{special_project}/download', [LecturerSpecialProjects::class, 'download'])
            ->name('special_projects.download');

        // Assessments (grade + comment on submissions)
        Route::get('courses/{course}/assessments', [LecturerAssessments::class, 'index'])
            ->name('courses.assessments.index');
        Route::get('/submissions/{submission}/assessments/create', [LecturerAssessments::class, 'create'])
            ->name('submissions.assessments.create');
        Route::get('/submissions/{submission}/assessments/{assessment}/edit', [LecturerAssessments::class, 'edit'])
            ->name('submissions.assessments.edit');

        Route::post('/assessments/save', [LecturerAssessments::class, 'save'])
            ->name('submissions.assessments.save');
        Route::delete('/submissions/{submission}/assessments/{assessment}', [LecturerAssessments::class, 'destroy'])
            ->name('submissions.assessments.destroy');

        // Show Submissions for a Single Special Project (Lecturer view)
        Route::get('/special-projects/{special_project}/submissions', [LecturerSubmissions::class, 'index'])
            ->name('special_projects.submissions.index');
        Route::get('/submissions/{submission}/download', [LecturerSubmissions::class, 'download'])
            ->name('submissions.download');

        // Announcements
        Route::resource('announcements', LecturerAnnouncements::class)
            ->except(['show']);

        // Course CRUD (full)
        Route::resource('courses', LecturerCourses::class)
            ->parameters(['courses' => 'course']);
  
        // Enrollments
        Route::prefix('courses/{course}')->name('courses.')->group(function () {
            Route::get('students', [LecturerEnrollments::class, 'index'])
                ->name('students.index');
            Route::get('students/{student}', [LecturerEnrollments::class, 'show'])
                ->name('students.show');
            Route::get('students/create', [LecturerEnrollments::class, 'create'])
                ->name('students.create');
            Route::post('students', [LecturerEnrollments::class, 'store'])
                ->name('students.store');
            Route::delete('students/{student}', [LecturerEnrollments::class, 'destroy']) // Consider using enrollment ID if possible
                ->name('students.destroy');
        });

        // Student Progress Analysis
        Route::get('courses/{course}/progress', [StudentAnalysisController::class, 'index'])
            ->name('courses.progress.index');
    });

// ─────────────────────────────────────────────────────────────────────────────
// ADMIN AREA
// ─────────────────────────────────────────────────────────────────────────────
    Route::middleware(['auth', 'admin'])
    ->prefix('admin')->as('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

        // Course CRUD
        Route::resource('courses', AdminCourses::class)
            ->parameters(['courses' => 'course']);

        // Admin Enrollments Management
        Route::resource('courses.enrollments', \App\Http\Controllers\Admin\EnrollmentController::class)
            ->only(['index', 'store', 'destroy'])
            ->parameters(['enrollments' => 'enrollment'])
            ->shallow();

        // 1. Whitelist Routes (Must be ABOVE the resource)
        Route::get('students/whitelist', [AdminStudents::class, 'wlCreate'])->name('students.wlCreate');
        Route::post('students/whitelist', [AdminStudents::class, 'wlStore'])->name('students.wlStore');
        Route::delete('students/whitelist/{eligible}', [AdminStudents::class, 'wlDestroy'])->name('students.wlDestroy');

        // 2. Student CRUD (Exclude 'create' and 'store' as they are now handled by whitelist)
        Route::resource('students', AdminStudents::class)
            ->only(['index', 'edit', 'update', 'destroy']);

        // Lecturers CRUD
        Route::resource('lecturers', AdminLecturers::class)
            ->parameters(['lecturers' => 'lecturer']);

        // Announcements CRUD
        Route::resource('announcements', AdminAnnouncements::class);
    });

// (Optional) Fallback → welcome
// Route::fallback(fn () => to_route('welcome'));
