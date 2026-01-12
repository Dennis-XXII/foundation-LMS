<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Material;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /** KPIs: course header, recent submissions, due soon, recent uploads, announcements */
    public function index()
    {
        $lecturer = Auth::user()->lecturer;

        // pick the most recent course taught by this lecturer (via pivot)
        $course = $lecturer->courses()->latest('courses.created_at')->first();
        abort_unless($course, 404, 'No course found for this lecturer.');

        // show latest submissions for this course
        $recentSubmissions = Submission::query()
            ->whereHas('assignment', fn($q) => $q->where('course_id', $course->id))
            ->with(['assignment', 'student.user'])
            ->latest()->take(10)->get();

        // assignments due soon
        $dueSoon = Assignment::query()
            ->where('course_id', $course->id)
            ->whereNotNull('due_at')
            ->where('due_at', '>=', now())
            ->orderBy('due_at')
            ->take(10)->get();

        // latest materials created
        $recentMaterials = Material::query()
            ->where('course_id', $course->id)
            ->latest('uploaded_at')->take(8)->get();

        // course‑scoped announcements
        $announcements = $course->announcements()
            ->orderByDesc('posted_at')
            ->orderByDesc('created_at')
            ->take(8)->get();

        return view('lecturer.dashboard', compact(
            'lecturer', 'course', 'recentSubmissions', 'dueSoon', 'recentMaterials', 'announcements'
        ));
    }
}
