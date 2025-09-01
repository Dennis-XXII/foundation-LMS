<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
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
        $this->authorize('view', $course);

        $type  = $this->normalizeType($request->query('type'));
        $level = $request->filled('level') ? (int)$request->query('level') : null;

        $validTypes = ['lesson','worksheet','self_study'];
        if ($type && !in_array($type, $validTypes, true)) { $type = null; }
        if ($level && !in_array($level, [1,2,3], true))   { $level = null; }

        $materials = Material::query()
            ->where('course_id', $course->id)
            ->where('is_published', true)
            ->when($type,  fn($q) => $q->where('type', $type))
            ->when($level, fn($q) => $q->where('level', $level))
            ->latest('uploaded_at')
            ->paginate(10)
            ->withQueryString();

        return view('student.materials.index', [
            'course'    => $course,
            'materials' => $materials,
            'type'      => $type,
            'level'     => $level,
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

        // Get the full path and return file download response
        $path = $disk->path($material->file_path);
        $name = basename($material->file_path);
        
        return response()->download($path, $name);
    }
}
