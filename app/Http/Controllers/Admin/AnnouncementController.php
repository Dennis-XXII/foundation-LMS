<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Announcement;
use App\Models\Course;

class AnnouncementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $announcements = Announcement::latest()->paginate(15);
        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        $courses = Course::orderBy('name')->get();
        return view('admin.announcements.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'is_global'   => ['nullable','boolean'],
            'file'        => ['nullable','file','max:20480'],
            'course_ids'  => ['array'], // optional pin to courses
            'course_ids.*'=> ['exists:courses,id'],
            'posted_at'   => ['nullable','date'],
        ]);

        $payload = [
            'user_id'    => Auth::id(),           // <-- using Auth facade
            'title'      => $data['title'],
            'description'=> $data['description'] ?? null,
            'is_global'  => (int)($data['is_global'] ?? 0),
            'posted_at'  => $data['posted_at'] ?? now(),
        ];

        if ($request->hasFile('file')) {
            // Prefer a dedicated disk like 'announcements' if you’ve configured it, else 'public'
            $payload['file_path'] = $request->file('file')->store(date('Y/m/d'), config('filesystems.disks.announcements') ? 'announcements' : 'public');
        }

        $announcement = Announcement::create($payload);

        if (!empty($data['course_ids']) && !$payload['is_global']) {
            $announcement->courses()->sync($data['course_ids']);
        }

        return to_route('admin.announcements.index')->with('success', 'Announcement posted.');
    }

    public function edit(Announcement $announcement)
    {
        $courses = Course::orderBy('name')->get();
        $announcement->load('courses');
        return view('admin.announcements.edit', compact('announcement','courses'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'title'       => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'is_global'   => ['nullable','boolean'],
            'file'        => ['nullable','file','max:20480'],
            'course_ids'  => ['array'],
            'course_ids.*'=> ['exists:courses,id'],
            'posted_at'   => ['nullable','date'],
        ]);

        $payload = [
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'is_global'   => (int)($data['is_global'] ?? 0),
            'posted_at'   => $data['posted_at'] ?? $announcement->posted_at,
        ];

        if ($request->hasFile('file')) {
            if ($announcement->file_path) {
                Storage::disk(config('filesystems.disks.announcements') ? 'announcements' : 'public')
                    ->delete($announcement->file_path);
            }
            $payload['file_path'] = $request->file('file')->store(date('Y/m/d'), config('filesystems.disks.announcements') ? 'announcements' : 'public');
        }

        $announcement->update($payload);

        if (!empty($data['course_ids']) && !$payload['is_global']) {
            $announcement->courses()->sync($data['course_ids']);
        } else {
            $announcement->courses()->detach();
        }

        return back()->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement)
    {
        // Keep file by default; delete the file if you prefer a purge:
        // if ($announcement->file_path) Storage::disk('announcements')->delete($announcement->file_path);
        $announcement->delete();
        return to_route('admin.announcements.index')->with('success', 'Announcement deleted.');
    }
}
