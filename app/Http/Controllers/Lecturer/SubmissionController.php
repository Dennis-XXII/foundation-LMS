<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\SpecialProject;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    /**
     * Display a list of submissions for a specific special project.
     * Route: lecturer.special_projects.submissions.index
     */
    public function index(Request $request, SpecialProject $specialProject)
    {
        // Authorize viewing the course this special project belongs to
        $this->authorize('update', $specialProject->course);

        // Eager load submissions with necessary related data for the list view
        $specialProject->load([
            'course', // For breadcrumbs/header
            'submissions' => function ($query) {
                $query->orderBy('student_id');
            },
            'submissions.student.user', // Student details
            'submissions.assessment'    // Load assessment to check status/score briefly
        ]);

        return view('lecturer.submissions.index', compact('specialProject'));
    }

    /**
     * Handle download of a student's submission file.
     * Route: lecturer.submissions.download
     */
    public function download(Submission $submission)
    {
        $this->authorize('update', $submission->specialProject->course);

        if (!$submission->file_path) {
            abort(404, 'No file attached.');
        }

        $diskName = 'submissions';
        $disk = Storage::disk($diskName);
        $path = $submission->file_path;

        if (!$disk->exists($path)) {
            abort(404, 'File not found on server.');
        }

        // Generate a nice download name
        $studentName = $submission->student?->user?->name ?? 'student';
        $specialProjectTitle = $submission->specialProject?->title ?? 'special_project';
        $downloadName = \Illuminate\Support\Str::slug("{$specialProjectTitle}_{$studentName}_") . basename($path);

        return response()->download($disk->path($path), $downloadName);
    }
}
