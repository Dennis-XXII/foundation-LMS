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
    public function index(Course $course)
    {
        // Ensure student is enrolled in this course
        $this->authorize('view', $course);

        // Filter materials by type only
        $materials = $course->materials()
            ->where('is_published', true);

        if (request()->has('type') && request('type') !== '') {
            $materials = $materials->where('type', request('type'));
        }

        $materials = $materials->latest('uploaded_at')->paginate(10);
        
        return view('student.materials.index', [
            'course' => $course,
            'materials' => $materials,
            'filters' => request()->only(['type'])
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
