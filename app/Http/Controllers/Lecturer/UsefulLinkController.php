<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\UsefulLink;
use Illuminate\Http\Request;

class UsefulLinkController extends Controller
{
    public function index(Course $course)
    {
        $this->authorize('update', $course);

        $usefulLinks = $course->usefulLinks()->latest()->get();

        return view('lecturer.useful_links.index', compact('course', 'usefulLinks'));
    }

    public function create(Course $course)
    {
        $this->authorize('update', $course);

        return view('lecturer.useful_links.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'link'        => ['required', 'url', 'max:2000'],
        ]);

        $course->usefulLinks()->create($validated);

        return redirect()->route('lecturer.courses.useful_links.index', $course)
            ->with('success', 'Useful Link added successfully!');
    }

    public function edit(UsefulLink $usefulLink)
    {
        $course = $usefulLink->course;
        $this->authorize('update', $course);

        return view('lecturer.useful_links.edit', compact('course', 'usefulLink'));
    }

    public function update(Request $request, UsefulLink $usefulLink)
    {
        $course = $usefulLink->course;
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'link'        => ['required', 'url', 'max:2000'],
        ]);

        $usefulLink->update($validated);

        return redirect()->route('lecturer.courses.useful_links.index', $course)
            ->with('success', 'Useful Link updated successfully!');
    }

    public function destroy(UsefulLink $usefulLink)
    {
        $course = $usefulLink->course;
        $this->authorize('update', $course);

        $usefulLink->delete();

        return redirect()->route('lecturer.courses.useful_links.index', $course)
            ->with('success', 'Useful Link deleted successfully!');
    }
}
