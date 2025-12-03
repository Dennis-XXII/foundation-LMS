<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use App\Models\Enrollment;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MaterialController extends Controller
{
    /**
     * Show all published materials for a course (with optional filters).
     */
    public function index(Request $request, Course $course) // <--- MODIFIED
        {
            // 1. Authorize that the student can view the course
            $this->authorize('view', $course);

            // 2. Get and validate filters just for display/links (no material fetch yet)
            $type  = $this->normalizeType($request->query('type'));
            $level = $request->integer('level');
            $week  = $request->integer('week');
            $day   = $request->query('day');

            $validTypes = ['lesson', 'worksheet', 'self_study'];
            if ($type && !in_array($type, $validTypes, true)) { $type = null; }
            if ($level && !in_array($level, [1, 2, 3], true)) { $level = null; }

            // 3. Get the student's enrolled level for this course
            $student_level = Enrollment::where('student_id', Auth::id())
                ->where('course_id', $course->id)
                ->value('level');

            // Fetch materials for timetable display (filtered by type/level only)
            $materials = Material::query()
                ->where('course_id', $course->id)
                ->where('is_published', true); // Students only see published

            // 4. Return the new timetable view
            return view('student.materials.timetable', compact(
                'course',
                'type',
                'level',
                'week',
                'day',
                'materials',
                'student_level' 
            ));
        }

    public function list(Request $request, Course $course) // <--- NEW METHOD
    {
        // 1. Authorize that the student can view the course
        $this->authorize('view', $course);

        // 2. Get and validate all filters
        $type  = $this->normalizeType($request->query('type'));
        $level = $request->integer('level');
        $week  = $request->integer('week');
        $day   = $request->query('day');

        $validTypes = ['lesson', 'worksheet', 'self_study'];
        if ($type && !in_array($type, $validTypes, true)) { $type = null; }
        if ($level && !in_array($level, [1, 2, 3], true)) { $level = null; }
        if ($week && !in_array($week, range(1, 8), true)) { $week = null; }
        $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'REVIEW'];
        if ($day && !in_array($day, $validDays, true)) { $day = null; }
        
        // Sanity Check: Must have Week and Day selected for this page (user flow enforcement)
        if (!$week || !$day) {
             return redirect()->route('student.materials.index', [
                'course' => $course, 
                'type' => $type, 
                'level' => $level
            ])->with('error', 'Please select a specific week and day from the timetable.');
        }


        // 3. Get the student's enrolled level for this course (CRITICAL)
        $student_level = Enrollment::where('student_id', Auth::id())
            ->where('course_id', $course->id)
            ->value('level');

        // 4. Build the database query (Original logic from old index)
        $materials = Material::query()
            ->where('course_id', $course->id)
            ->where('is_published', true) // Students only see published
            
            // --- CRITICAL: Student's base-level security filter ---
            ->when($student_level, function ($query) use ($student_level) {
                $query->where(function ($subQuery) use ($student_level) {
                    $subQuery->where('level', '<=', $student_level)
                             ->orWhereNull('level'); // Also include materials for all levels
                });
            })
            // --- End Security Filter ---

            // --- User's Selected Filters (from URL) ---
            ->when($type,  fn($q) => $q->where('type', $type))
            ->when($level, fn($q) => $q->where('level', $level))
            ->when($week,  fn($q) => $q->where('week', $week))
            ->when($day,   fn($q) => $q->where('day', $day))
            // --- End Selected Filters ---
            
            ->latest('uploaded_at') // Match lecturer's sorting
            ->paginate(20)          // Match lecturer's pagination
            ->withQueryString();    // Keep filters on pagination links

        // 5. Return the new list view with all necessary data
        return view('student.materials.list', compact(
            'course',
            'materials',
            'type',
            'level',
            'week',
            'day',
            'student_level' 
        ));
    }

    /**
     * NEW: Show a single material page.
     */
    public function show(Material $material)
    {
        // 1. Run all security checks
        $this->authorizeStudentAccess($material);

        // 2. Load the course relationship
        $material->load('course');
        $course = $material->course;
        $level = Enrollment::where('student_id', Auth::id())
            ->where('course_id', $course->id)
            ->value('level');
        $type = $this->normalizeType($material->type);

        $relatedAssignment = null;
        // Only look for assignment if material has week AND day defined
        if ($material->week && $material->day && $material->level) {
             $relatedAssignment = Assignment::query()
                ->where('course_id', $course->id)
                ->where('week', $material->week)
                ->where('day', $material->day)
                ->where('level', $material->level)
                ->where('is_published', true) // Only published assignments
                // Ensure assignment is accessible by student's level
                ->when($level !== null, function ($query) use ($level) {
                    $query->where(function ($subQuery) use ($level) {
                        $subQuery->where('level', '<=', $level)
                                ->orWhereNull('level');
                    });
                })
                ->first(); // Get the first matching assignment
        }

        // 3. Show the view
        return view('student.materials.show', compact('material','course','level','type', 'relatedAssignment'));
    }


    /**
     * Download a material file securely.
     */
    public function download(Material $material)
    {
        // 1. Run all security checks
        $this->authorizeStudentAccess($material);

        // 2. Check for file path
        if (!$material->file_path) {
            abort(404, 'No file attached.');
        }

        $disk = Storage::disk('materials');
        if (!$disk->exists($material->file_path)) {
            abort(404, 'File not found.');
        }

        // 3. Download
        $path = $disk->path($material->file_path);
        $name = Str::slug($material->title) . '.' . pathinfo($material->file_path, PATHINFO_EXTENSION);
        
        return response()->download($path, $name);
    }

    /**
     * REFACTORED: Private helper to authorize student access for show/download.
     */
    private function authorizeStudentAccess(Material $material)
    {
        // 1. Authorize against its course (CoursePolicy@view)
        Gate::authorize('view', $material->course);

        // 2. Student-specific check: Must be published
        if (!$material->is_published) {
            abort(403, 'This material is not available.');
        }

        // 3. Student-specific check: Must be within level
        $student_level = Enrollment::where('student_id', Auth::id())
            ->where('course_id', $material->course_id)
            ->value('level');
        
        if ($material->level && $student_level && $material->level > $student_level) {
             abort(403, 'This material is not available for your level.');
        }
    }

    private function normalizeType(?string $type): ?string
    {
        if (!$type) return null;
        return Str::slug($type) === 'self-study' ? 'self_study' : Str::slug($type);
    }
}