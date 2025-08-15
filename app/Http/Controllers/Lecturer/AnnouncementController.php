<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    /** List my announcements (optionally filter by course) */
    public function index(Request $request)
    {
        $announcements = Announcement::query()
            ->where('user_id', Auth::id()) // author
            ->when(
                $request->filled('course_id'),
                fn($q) => $q->whereHas('courses', fn($qq) => $qq->where('course_id', $request->integer('course_id')))
            )
            ->orderByDesc('posted_at')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('lecturer.announcements.index', compact('announcements'));
    }

    /** Create form */
    public function create()
    {
        $this->authorize('create', Announcement::class);
        return view('lecturer.announcements.create');
    }

    /** Store (attach courses through pivot announcement_courses) */
    public function store(Request $request)
    {
        $this->authorize('create', Announcement::class);

        $data = $request->validate([
            'title'        => ['required','string','max:255'],
            'description'  => ['nullable','string','max:8000'],
            'course_ids'   => ['nullable','array'],
            'course_ids.*' => ['integer','exists:courses,id'],
            'posted_at'    => ['nullable','date'],
            'is_global'    => ['nullable','boolean'],
        ]);

        $announcement = Announcement::create([
            'user_id'    => Auth::id(),
            'title'      => $data['title'],
            'description'=> $data['description'] ?? null,
            'is_global'  => (int)($data['is_global'] ?? 0),
            'posted_at'  => $data['posted_at'] ?? now(),
        ]);

        if (!empty($data['course_ids'])) {
            $announcement->courses()->sync($data['course_ids']);
        }

        return to_route('lecturer.announcements.index')->with('success', 'Announcement published.');
    }

    /** Edit form */
    public function edit(Announcement $announcement)
    {
        $this->authorize('update', $announcement);
        $announcement->load('courses');
        return view('lecturer.announcements.edit', compact('announcement'));
    }

    /** Update + retarget courses */
    public function update(Request $request, Announcement $announcement)
    {
        $this->authorize('update', $announcement);

        $data = $request->validate([
            'title'        => ['required','string','max:255'],
            'description'  => ['nullable','string','max:8000'],
            'course_ids'   => ['nullable','array'],
            'course_ids.*' => ['integer','exists:courses,id'],
            'posted_at'    => ['nullable','date'],
            'is_global'    => ['nullable','boolean'],
        ]);

        $announcement->update([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'posted_at'   => $data['posted_at'] ?? $announcement->posted_at,
            'is_global'   => (int)($data['is_global'] ?? $announcement->is_global),
        ]);

        $announcement->courses()->sync($data['course_ids'] ?? []);

        return back()->with('success', 'Announcement updated.');
    }

    /** Delete */
    public function destroy(Announcement $announcement)
    {
        $this->authorize('delete', $announcement);
        $announcement->delete();
        return to_route('lecturer.announcements.index')->with('success', 'Announcement deleted.');
    }
}
