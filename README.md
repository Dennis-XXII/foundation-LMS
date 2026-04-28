# Foundation LMS

A role-based Learning Management System built for a language/foundation school. Supports three distinct user roles — Admin, Lecturer, and Student — with level-aware content delivery, file management, and assignment grading workflows.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| Language | PHP 8.2 |
| Database | SQLite (local dev) |
| Frontend | Blade templates + Vite |
| Authentication | Laravel session-based auth |
| File Storage | Local disk (multi-disk setup) |

---

## Getting Started

```bash
# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
touch database/database.sqlite
php artisan migrate --seed

# Create storage symlink
php artisan storage:link

# Run all services (server + queue + logs + vite)
composer run dev
```

---

## Role Architecture

The system uses a **single `users` table with a `role` column**, plus separate profile tables per role. Authentication is session-based using Laravel's built-in `Authenticatable`.

```
users  (role: admin | lecturer | student)
 ├── admins     (1:1)
 ├── lecturers  (1:1)
 └── students   (1:1)  →  stores institutional student_id
```

Each role is enforced at the routing layer via dedicated middleware:

| Middleware | File |
|---|---|
| Admin | `app/Http/Middleware/AdminMiddleware.php` |
| Lecturer | `app/Http/Middleware/LecturerMiddleware.php` |
| Student | `app/Http/Middleware/StudentMiddleware.php` |

Each role gets its own URL prefix and controller namespace:

| Role | URL Prefix | Route Prefix |
|---|---|---|
| Admin | `/admin/*` | `admin.` |
| Lecturer | `/lecturer/*` | `lecturer.` |
| Student | `/student/*` | `student.` |

---

## Student Registration: Whitelist System

Students cannot register freely. Admins pre-populate an `eligible_students` table with allowed institutional student IDs. On registration, the provided ID is validated against this whitelist before an account is created.

```
Admin adds student_id → eligible_students table
        ↓
Student submits registration form with student_id
        ↓
System checks whitelist → if valid, create User + Student records
```

---

## Data Model

```
EligibleStudent (whitelist)
        |
        └── Student
                |
                ├── Enrollment ──── Course
                │    (level, status)    │
                │                      ├── CourseLecturer ──── Lecturer
                │                      ├── Material
                │                      ├── Assignment
                │                      └── AnnouncementCourse ──── Announcement
                │
                └── Submission
                        ├── belongs to Assignment
                        └── Assessment (score + comment, by Lecturer)
```

### Key Relationships

| Model | Relationships |
|---|---|
| `User` | hasOne Student / Lecturer / Admin; hasMany Announcements |
| `Course` | belongsToMany Lecturers (via `course_lecturers`); belongsToMany Students (via `enrollments`); hasMany Materials, Assignments, Announcements |
| `Student` | hasMany Enrollments, Submissions |
| `Enrollment` | belongsTo Student, Course — pivot holds `level` and `status` |
| `Material` | belongsTo Course — has `type`, `level`, `week`, `day`, `file_path`, `url` |
| `Assignment` | belongsTo Course; hasMany Submissions — has `level`, `week`, `day`, `due_at`, `file_path` |
| `Submission` | belongsTo Assignment, Student; hasOne Assessment |
| `Assessment` | belongsTo Submission, Lecturer — holds `score` and `comment` |
| `Announcement` | belongsToMany Courses (via `announcement_courses`) |
| `EligibleStudent` | hasOne Student (via `student_id`) |

All core models use **soft deletes** (`deleted_at`) — nothing is hard-deleted.

---

## Level System

Materials and Assignments have an optional `level` field (1, 2, or 3). Students have a `level` stored on the `Enrollment` pivot per course. The system filters content using Eloquent query scopes:

```php
// Only show content at or below the student's enrolled level
scopeVisibleForLevel($query, int $studentLevel)
// Applies: is_published = true AND (level IS NULL OR level <= studentLevel)
```

This lets one course serve students at different proficiency levels simultaneously, with each student only seeing appropriate content.

Materials and Assignments also carry `week` (1–8) and `day` (Monday–Friday, REVIEW) fields, enabling a timetable-style browsing interface.

---

## Controllers

### Student (`app/Http/Controllers/Student/`)

| Controller | Responsibility |
|---|---|
| `DashboardController` | Course overview, quick tiles |
| `MaterialController` | Browse timetable, list, view, download materials |
| `AssignmentController` | View, download assignments |
| `SubmissionController` | Submit, edit, view, download own submissions |
| `StudentController` | Own profile view |

### Lecturer (`app/Http/Controllers/Lecturer/`)

| Controller | Responsibility |
|---|---|
| `DashboardController` | Overview of own courses |
| `CourseController` | View and edit own courses |
| `MaterialController` | Full CRUD for materials (file + URL support) |
| `AssignmentController` | Full CRUD for assignments, view submissions per assignment |
| `AssessmentController` | Grade (score + comment) student submissions |
| `SubmissionController` | List and download student submissions |
| `EnrollmentController` | Manage enrolled students per course |
| `AnnouncementController` | Create and manage announcements |
| `StudentAnalysisController` | View student progress across a course |

### Admin (`app/Http/Controllers/Admin/`)

| Controller | Responsibility |
|---|---|
| `DashboardController` | System-wide overview |
| `CourseController` | Full course CRUD |
| `StudentController` | Manage students + whitelist (add/remove eligible IDs) |
| `LecturerController` | Manage lecturer accounts |
| `EnrollmentController` | Manage all enrollments across courses |
| `AnnouncementController` | System-wide announcements |

---

## Authorization (Policies)

Fine-grained authorization is handled by Laravel Policies in `app/Policies/`:

| Policy | Guards |
|---|---|
| `CoursePolicy` | Is the user enrolled in / assigned to this course? |
| `MaterialPolicy` | Is the material published and within the student's level? |
| `AssignmentPolicy` | Is the assignment published and within the student's level? |
| `SubmissionPolicy` | Does this submission belong to the requesting student? |
| `AssessmentPolicy` | Is the lecturer assigned to this course? |
| `AnnouncementPolicy` | Is the user a lecturer or admin? |

Authorization is enforced at three layers:

1. **Middleware** — role-level access (admin/lecturer/student prefix routes)
2. **Policies** — resource-level ownership and visibility
3. **Query scopes** — level-gated content filtering in DB queries

---

## File Storage System

Three custom Laravel disks handle file storage with different access levels:

```
storage/
└── app/
    ├── public/
    │   └── materials/        ← "materials" disk  (public visibility)
    └── secure/
        ├── assignments/      ← "assignments" disk (private, no public URL)
        └── submissions/      ← "submissions" disk (private, no public URL)
```

| Disk | Visibility | Purpose |
|---|---|---|
| `materials` | Public | Course PDFs, slides, worksheets |
| `assignments` | Private | Lecturer-uploaded assignment briefs |
| `submissions` | Private | Student-uploaded submission files |

### File Path Structure (`app/Support/FilePathBuilder.php`)

```
Materials:   course_{id}/{type}/YYYY/MM/{8rand}_{slug}.{ext}
Assignments: course_{id}/assignment_{id}/{slug}.{ext}
Submissions: {YYYY/mm/dd}/{studentId}_{timestamp}_{originalName}
```

### Upload Rules

| Type | Allowed Formats | Max Size |
|---|---|---|
| Material | pdf, doc, docx, ppt, pptx, zip | 20 MB |
| Assignment | pdf, doc, docx, zip | 20 MB |
| Submission | any | 10 MB |

### Download Flow

All file downloads are routed through controller methods that run policy checks before serving — no file is ever served via a direct public URL on private disks.

```
Request → authorize() policy check
        → disk->exists() check
        → response()->download($path, $cleanFilename)
```

Download filenames are auto-formatted:
- **Materials:** `{type}-L{level}-{title-slug}.{ext}`
- **Submissions:** `{assignment-slug}_{student-name}_{original-filename}`

### File Management

- **Replace:** uploading a new file deletes the old one from disk first
- **Remove only file:** `remove_file=1` checkbox deletes the file, keeps the DB record and URL
- **Clear file route:** dedicated `clearFile()` method per controller
- **On delete:** physical file is deleted from disk before soft-deleting the DB record

---

## Route Structure

```
/                               Guest welcome page
/login                          Login form (guest only)
/register/student               Student registration (guest only)
/register/lecturer              Lecturer registration (guest only)
/register/admin                 Admin registration (guest only)
/logout                         POST (authenticated)

/student/dashboard              Student home
/student/profile                Own profile
/student/courses/{course}/materials         Material timetable
/student/courses/{course}/materials/list    Material list (week+day filtered)
/student/materials/{material}              Material detail + related assignment
/student/materials/{material}/download     Secure download
/student/courses/{course}/assignments      Assignment list
/student/assignments/{assignment}          Assignment detail
/student/assignments/{assignment}/download Secure download
/student/assignments/{assignment}/submissions/create    Submit
/student/assignments/{assignment}/submissions/{sub}/edit Re-submit
/student/submissions/{submission}/download  Own submission download

/lecturer/dashboard
/lecturer/courses/{course}/materials        Material timetable
/lecturer/courses/{course}/materials/list   Filtered list (week+day)
/lecturer/courses/{course}/materials/all    All materials list
/lecturer/materials/{material}             Material detail
/lecturer/materials/{material}/download    Download material file
/lecturer/materials/{material}/file        DELETE - clear file only
/lecturer/courses/{course}/assignments     Assignment index (post/assess tabs)
/lecturer/assignments/{assignment}         Assignment detail + submission list
/lecturer/assignments/{assignment}/download Download assignment file
/lecturer/assignments/{assignment}/submissions  All submissions for assignment
/lecturer/submissions/{submission}/download     Download student submission
/lecturer/submissions/{submission}/assessments/create   Grade form
/lecturer/submissions/{submission}/assessments/{a}/edit Edit grade
/lecturer/assessments/save                  Save grade (POST)
/lecturer/courses/{course}/students        Enrolled students
/lecturer/courses/{course}/progress        Student progress analysis
/lecturer/announcements                    Announcement CRUD

/admin/dashboard
/admin/courses                  Course CRUD
/admin/courses/{course}/enrollments   Enrollment management
/admin/students                 Student list + edit
/admin/students/whitelist       Whitelist management (add/remove eligible IDs)
/admin/lecturers                Lecturer CRUD
/admin/announcements            Announcement CRUD
```

---

## Database Migrations (Chronological)

| Migration | Table |
|---|---|
| `0001_01_01_000000` | `users` |
| `0001_01_01_000001` | `cache` |
| `0001_01_01_000002` | `jobs` |
| `2025_07_05` | `students` |
| `2025_08_13` | `admins`, `lecturers`, `courses`, `course_lecturers`, `enrollments`, `materials`, `assignments`, `submissions`, `assessments`, `announcements`, `announcement_courses` |
| `2025_10_22` | Add `day` + `week` to `materials` and `assignments`; update `assessments` |
| `2026_01_12` | `eligible_students` (whitelist) |

---

## Directory Structure

```
foundation-LMS/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   ├── Auth/
│   │   │   ├── Lecturer/
│   │   │   └── Student/
│   │   └── Middleware/
│   │       ├── AdminMiddleware.php
│   │       ├── LecturerMiddleware.php
│   │       └── StudentMiddleware.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Admin.php
│   │   ├── Lecturer.php
│   │   ├── Student.php
│   │   ├── EligibleStudent.php
│   │   ├── Course.php
│   │   ├── CourseLecturer.php
│   │   ├── Enrollment.php
│   │   ├── Material.php
│   │   ├── Assignment.php
│   │   ├── Submission.php
│   │   ├── Assessment.php
│   │   ├── Announcement.php
│   │   └── AnnouncementCourse.php
│   ├── Policies/
│   │   ├── CoursePolicy.php
│   │   ├── MaterialPolicy.php
│   │   ├── AssignmentPolicy.php
│   │   ├── SubmissionPolicy.php
│   │   ├── AssessmentPolicy.php
│   │   └── AnnouncementPolicy.php
│   └── Support/
│       └── FilePathBuilder.php
├── config/
│   └── filesystems.php         (materials / assignments / submissions disks)
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── database.sqlite
├── resources/
│   └── views/
│       ├── admin/
│       ├── auth/
│       ├── components/
│       ├── lecturer/
│       ├── student/
│       └── welcome.blade.php
├── routes/
│   └── web.php
└── storage/
    └── app/
        ├── public/materials/   (symlinked to public/storage)
        └── secure/
            ├── assignments/
            └── submissions/
```

---

## Known Issues

- `Lecturer\SubmissionController::download()` uses `Storage::disk('public')` instead of `Storage::disk('submissions')`. Since student submission files are stored on the `submissions` (private) disk, lecturer downloads of student submissions will return 404. The disk name should be `'submissions'`.