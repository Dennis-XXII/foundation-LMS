<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $courses = Course::latest()->paginate(12);
        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('admin.courses.create');
    }

    public function store(Request $request)
    {
        // Admins control the course meta
        $data = $request->validate([
            'code'        => ['required','string','max:255','unique:courses,code'],
            'name'        => ['required','string','max:255'],
            'level'       => ['nullable','string','max:50'],
            'description' => ['nullable','string'],
        ]);

        $course = Course::create($data);

        return to_route('admin.courses.edit', $course)
            ->with('success', 'Course created by admin: '.Auth::id());
    }

    public function show(Course $course)
    {
        return view('admin.courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        return view('admin.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'code'        => ['required','string','max:255','unique:courses,code,'.$course->id],
            'name'        => ['required','string','max:255'],
            'level'       => ['nullable','string','max:50'],
            'description' => ['nullable','string'],
        ]);

        $course->update($data);

        return to_route('admin.courses.index')->with('success', 'Course updated by admin: '.Auth::id());
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return to_route('admin.courses.index')->with('success', 'Course deleted.');
    }
}
