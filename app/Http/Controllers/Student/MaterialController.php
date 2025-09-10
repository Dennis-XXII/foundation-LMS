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

        // 4. Build the database query with the security check included
        $materials = Material::query()
            ->where('course_id', $course->id)
            ->where('is_published', true)
            // Apply the user's optional filters from the URL
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($level, fn($q) => $q->where('level', $level))
            // filter by student's enrolled level
            ->when($student_level, function ($query) use ($student_level) {
                $query->where(function ($subQuery) use ($student_level) {
                    $subQuery->where('level', '<=', $student_level)
                             ->orWhereNull('level'); // Also include materials for all levels
                });
            })
            ->latest('uploaded_at')
            ->paginate(10)
            ->withQueryString();

        // 5. Return the view with the correctly filtered materials
        return view('student.materials.index', compact(
            'course',
            'materials',
            'type',
            'level',
            'student_level'
        ));
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

        // Get the full path and return file download response
        $path = $disk->path($material->file_path);
        $name = basename($material->file_path);
        
        return response()->download($path, $name);
    }

    private function normalizeType(?string $type): ?string
    {
        if (!$type) return null;
        return $type === 'self-study' ? 'self_study' : $type;
    }
}
