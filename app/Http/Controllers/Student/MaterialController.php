<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MaterialController extends Controller
{
    /**
     * Show all published materials for a course (with optional type/level filter).
     */
    public function index(Request $request, Course $course)
    {
        // 1. Authorize that the student can view the course in general
        $this->authorize('view', $course);

        // 2. Get and validate filters from the URL
        $type  = $this->normalizeType($request->query('type'));
        $level = $request->filled('level') ? (int)$request->query('level') : null;

        $validTypes = ['lesson', 'worksheet', 'self_study'];
        if ($type && !in_array($type, $validTypes, true)) { $type = null; }
        if ($level && !in_array($level, [1, 2, 3], true)) { $level = null; }

        // 3. Get the student's enrolled level for this course
        $student_level = Enrollment::where('student_id', Auth::id())
            ->where('course_id', $course->id)
            ->value('level');

        // 4. Build the database query
        $materials = Material::query()
            ->where('course_id', $course->id)
            ->where('is_published', true)
            
            // RE-ADD: Apply the user's optional filters from the URL
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($level, fn($q) => $q->where('level', $level))
            
            // Student's base-level security filter (unchanged)
            ->when($student_level, function ($query) use ($student_level) {
                $query->where(function ($subQuery) use ($student_level) {
                    $subQuery->where('level', '<=', $student_level)
                             ->orWhereNull('level'); // Also include materials for all levels
                });
            })
            ->orderBy('title')
            ->get(); // Get all filtered results

        // 5. Group the *filtered* materials by week, then by day.
        $materialsByWeekDay = $materials->groupBy(['week', 'day']);

        // 6. Return the view with all necessary data
        return view('student.materials.index', [
            'course' => $course,
            'student_level' => $student_level,
            'materialsByWeekDay' => $materialsByWeekDay,
            'type' => $type, // Pass the active filter
            'level' => $level, // Pass the active filter
        ]);
    }


    /**
     * Download a material file securely.
     */
    public function download(Material $material)
    {
        // Authorize against its course (CoursePolicy@view)
        Gate::authorize('view', $material->course);

        if (!$material->file_path) {
            abort(404, 'No file attached.');
        }

        $disk = Storage::disk('materials');
        if (!$disk->exists($material->file_path)) {
            abort(404, 'File not found.');
        }

        $path = $disk->path($material->file_path);
        $name = Str::slug($material->title) . '.' . pathinfo($material->file_path, PATHINFO_EXTENSION);
        
        return response()->download($path, $name);
    }

    // This helper is now used again
    private function normalizeType(?string $type): ?string
    {
        if (!$type) return null;
        return Str::slug($type) === 'self-study' ? 'self_study' : Str::slug($type);
    }
}