<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Auth::user()->lecturer->courses()->latest()->get();
        return view('lecturer.courses.index', compact('courses'));
    }

    public function show(Course $course)
    {
        $this->authorize('view', $course);
        return view('lecturer.courses.edit', compact('course'));
    }

    public function edit(Course $course)
    {
        $this->authorize('update', $course);
        return view('lecturer.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $course->update($validated);

        return redirect()
            ->route('lecturer.courses.show', $course)
            ->with('success', 'Course updated successfully.');
    }
}

