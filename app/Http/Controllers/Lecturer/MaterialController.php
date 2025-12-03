<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use App\Support\FilePathBuilder; // uses our helper for predictable paths
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{   
    
    public function show(Material $material)
    {
        $this->authorize('view', $material); // Or 'view', $material->course
        $material->load('course'); // Eager load the course relationship

        return view('lecturer.materials.show', compact('material'));
    }
     /**
     * List materials for a course with optional type & level filters.
     * Route: lecturer.courses.materials.index
     * Query: ?type=lesson|worksheet|self_study&level=1|2|3
     * NEW: Query: ?view=list|grid
     */
    public function index(Request $request, Course $course) // <--- MODIFIED
        {
            $this->authorize('view', $course);

            // Normalize & soft-validate only filters used on the timetable page
            $type  = $this->normalizeType($request->query('type'));
            $level = $request->integer('level');

            $validTypes = ['lesson','worksheet','self_study'];
            if ($type && !in_array($type, $validTypes, true))   $type = null;
            if ($level && !in_array($level, [1,2,3], true))     $level = null;

            return view('lecturer.materials.timetable', compact(
                'course',
                'type',
                'level',
            ));
        }
    public function list(Request $request, Course $course)
    {
        $this->authorize('view', $course);

        // Normalize & soft-validate all filters
        $type  = $this->normalizeType($request->query('type'));
        $level = $request->integer('level');
        $week  = $request->integer('week');
        $day   = $request->query('day'); 

        // Validation checks
        $validTypes = ['lesson','worksheet','self_study'];
        if ($type && !in_array($type, $validTypes, true))   $type = null;
        if ($level && !in_array($level, [1,2,3], true))     $level = null;
        if ($week && !in_array($week, range(1, 8), true))   $week = null;
        $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'REVIEW'];
        if ($day && !in_array($day, $validDays, true))      $day = null;
        
        // Redirect back to timetable if no specific filters are set (Original logic restored for safety)
        if (!$week || !$day) {
             return redirect()->route('lecturer.courses.materials.index', [
                 'course' => $course, 
                 'type' => $type, 
                 'level' => $level
             ])->with('error', 'Please select a specific week and day from the timetable.');
        }

        $materials = Material::query()
            ->where('course_id', $course->id)
            ->when($type,  fn($q) => $q->where('type', $type))
            ->when($level, fn($q) => $q->where('level', $level))
            ->when($week,  fn($q) => $q->where('week', $week))
            ->when($day,   fn($q) => $q->where('day', $day))
            ->latest('uploaded_at')
            ->paginate(20)
            ->withQueryString(); 

        return view('lecturer.materials.list', compact(
            'course',
            'materials',
            'type',
            'level',
            'week',
            'day'
        ));
    }

    public function listAll(Request $request, Course $course)
    {
        $this->authorize('view', $course);

        // Normalize & soft-validate filters (Optional filters allowed)
        $type  = $this->normalizeType($request->query('type'));
        $level = $request->integer('level');
        // Week/Day filters are allowed but not required here
        $week  = $request->integer('week');
        $day   = $request->query('day'); 

        $validTypes = ['lesson','worksheet','self_study'];
        if ($type && !in_array($type, $validTypes, true))   $type = null;
        if ($level && !in_array($level, [1,2,3], true))     $level = null;
        if ($week && !in_array($week, range(1, 8), true))   $week = null;
        $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'REVIEW'];
        if ($day && !in_array($day, $validDays, true))      $day = null;

        $materials = Material::query()
            ->where('course_id', $course->id)
            ->when($type,  fn($q) => $q->where('type', $type))
            ->when($level, fn($q) => $q->where('level', $level))
            ->when($week,  fn($q) => $q->where('week', $week))
            ->when($day,   fn($q) => $q->where('day', $day))
            ->latest('uploaded_at')
            ->paginate(20)
            ->withQueryString(); 

        return view('lecturer.materials.all_list', compact(
            'course',
            'materials',
            'type',
            'level',
            'week',
            'day'
        ));
    }

    /** Show create form (nested) */
    public function create(Course $course)
    {
        $this->authorize('update', $course);

        $type  = $this->normalizeType(request('type'));
        $level = request('level');
        $week  = request('week');
        $day   = request('day');

        return view('lecturer.materials.create', compact('course','type','level','week','day'));
    }

    /**
     * Store a file and/or URL (both allowed for materials).
     * Route: lecturer.courses.materials.store
     */
    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);
        $lockedLevel = $request->query('level'); // from ?level=
        $lockedType  = $request->query('type');  // from ?type=

        $data = $request->validate([
            'title'        => ['required','string','max:255'],
            'descriptions' => ['nullable','string','max:2000'],
            'type'         => ['required','in:lesson,worksheet,self_study'],
            'level'        => ['nullable','integer','min:1','max:3'],
            'day'          => ['nullable','in:Monday,Tuesday,Wednesday,Thursday,Friday,Review'],
            'week'        => ['nullable','integer','min:1','max:8'],
            'is_published' => ['sometimes','boolean'],
            'url'          => ['nullable','url','max:2048'],
            'file'         => ['nullable','file','mimes:pdf,doc,docx,ppt,pptx,zip','max:20480'], // 20MB
            'uploaded_at'  => ['nullable','date'],
        ]);

        if ($lockedLevel) $data['level'] = (int) $lockedLevel;
        if ($lockedType)  $data['type']  = $lockedType;

        // Require at least a file or a URL (allow both)
        if (!$request->hasFile('file') && empty($data['url'])) {
            return back()->withErrors(['file' => 'Upload a file or provide a URL.'])->withInput();
        }

        $data['course_id']    = $course->id;
        $data['is_published'] = (int)($data['is_published'] ?? 1);
        $data['uploaded_at']  = $data['uploaded_at'] ?? now();

        $material = DB::transaction(function () use ($request, $course, $data) {
            /** @var Material $material */
            $material = Material::create($data);

            if ($request->hasFile('file')) {
                // Path like: course_{course}/lesson|worksheet|self_study/Y/m/<rand>_name.ext
                $path = FilePathBuilder::materialPath($course->id, $data['type'], $request->file('file'));
                Storage::disk('materials')->putFileAs(dirname($path), $request->file('file'), basename($path));
                $material->update(['file_path' => $path]);
            }

            return $material;
        });

        return to_route('lecturer.materials.edit', $material)
            ->with('success','Material created.');
    }

    /** Edit (shallow) */
    public function edit(Material $material)
    {
        $this->authorize('update', $material);
        $material->load('course');

        $course = $material->course;
        $type   = $this->normalizeType(request('type'));
        $level  = request('level');
        return view('lecturer.materials.edit', compact('course','material','type','level'));
    }

    /**
     * Update meta/file (shallow)
     * Materials may keep both URL and file; replacing the file deletes the old file.
     */
    public function update(Request $request, Material $material)
    {
        $this->authorize('update', $material);

        $data = $request->validate([
            'title'        => ['required','string','max:255'],
            'descriptions' => ['nullable','string','max:2000'],
            'type'         => ['required','in:lesson,worksheet,self_study'],
            'level'        => ['nullable','integer','min:1','max:3'],
            'day'          => ['nullable','in:Monday,Tuesday,Wednesday,Thursday,Friday,Review'],
            'week'        => ['nullable','integer','min:1','max:8'],
            'is_published' => ['sometimes','boolean'],
            'url'          => ['nullable','url','max:2048'],
            'file'         => ['nullable','file','mimes:pdf,doc,docx,ppt,pptx,zip','max:20480'],
            'uploaded_at'  => ['nullable','date'],
            'remove_file'  => ['sometimes','in:1'], // optional checkbox
        ]);

        DB::transaction(function () use ($request, $material, &$data) {
            // Remove current file if requested
            if ($request->boolean('remove_file') && $material->file_path) {
                Storage::disk('materials')->delete($material->file_path);
                $material->file_path = null;
            }

            // Replace with new upload if present
            if ($request->hasFile('file')) {
                if ($material->file_path) {
                    Storage::disk('materials')->delete($material->file_path);
                }
                $path = FilePathBuilder::materialPath($material->course_id, $data['type'], $request->file('file'));
                Storage::disk('materials')->putFileAs(dirname($path), $request->file('file'), basename($path));
                $material->file_path = $path;
            }

            // Preserve publish flag if not provided
            $data['is_published'] = (int)($data['is_published'] ?? $material->is_published);

            $material->fill($data)->save();
        });

        return back()->with('success','Material updated.');
    }

    /**
     * Download file (public materials disk but served via response()->download for consistency).
     * Route: lecturer.materials.download
     */
    public function download(Material $material)
    {
        $this->authorize('view', $material);

        if (!$material->file_path || !Storage::disk('materials')->exists($material->file_path)) {
            abort(404, 'File not found.');
        }

        $absolute = Storage::disk('materials')->path($material->file_path);

        // Nice download name: <type>-L<level>-<title>.<ext>
        $ext = pathinfo($absolute, PATHINFO_EXTENSION);
        $name = sprintf(
            '%s-L%s-%s.%s',
            $material->type ?? 'material',
            $material->level ?? 'x',
            str($material->title)->slug('_'),
            $ext
        );

        return response()->download($absolute, $name);
    }

    /**
     * Clear only the file (keep the record and URL).
     * Route: lecturer.materials.clear-file
     */
    public function clearFile(Material $material)
    {
        $this->authorize('update', $material);

        if ($material->file_path) {
            Storage::disk('materials')->delete($material->file_path);
            $material->update(['file_path' => null]);
        }

        return back()->with('success','File removed from material.');
    }

    /**
     * Delete material (also remove physical file for cleanliness).
     * Route: lecturer.materials.destroy
     */
    public function destroy(Material $material)
    {
        $this->authorize('delete', $material);

        $course = $material->course; // for redirect with filters
        $type   = request('type');
        $level  = request('level');

        if ($material->file_path) {
            Storage::disk('materials')->delete($material->file_path);
        }
        $material->delete();

        // Preserve filters on redirect
        $url = route('lecturer.courses.materials.index', $course)
            . ($type  ? '?type='.$type : '')
            . ($level ? (empty($type) ? '?' : '&').'level='.$level : '');

        return redirect($url)->with('success','Material deleted.');
    }

    /** Helper: normalize UI "self-study" -> DB "self_study" */
    private function normalizeType(?string $type): ?string
    {
        if (!$type) return null;
        return $type === 'self-study' ? 'self_study' : $type;
    }
}
