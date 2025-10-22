<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // For download

class SubmissionController extends Controller
{
    /**
     * Display a list of submissions for a specific assignment.
     * Route: lecturer.assignments.submissions.index
     */
    public function index(Request $request, Assignment $assignment)
    {
        // Authorize viewing the course this assignment belongs to
        $this->authorize('update', $assignment->course);

        // Eager load submissions with necessary related data for the list view
        $assignment->load([
            'course', // For breadcrumbs/header
            'submissions' => function ($query) {
                // Order submissions as needed (e.g., by student name or submission time)
                $query->orderBy('student_id');
            },
            'submissions.student.user', // Student details
            'submissions.assessment'    // Load assessment to check status/score briefly
        ]);

        // Pass the single assignment (with loaded submissions) to the new view
        return view('lecturer.submissions.index', compact('assignment'));
    }

    /**
     * Handle download of a student's submission file.
     * Route: lecturer.submissions.download (Example route name)
     */
    public function download(Submission $submission)
    {
        $this->authorize('update', $submission->assignment->course); // Or view policy

        if (!$submission->file_path) {
            abort(404, 'No file attached.');
        }

        // Determine the correct disk (e.g., 'submissions' or 'public')
        $diskName = 'public'; // Change if needed
        $disk = Storage::disk($diskName);
        $path = $submission->file_path;

        if (!$disk->exists($path)) {
            abort(404, 'File not found on server.');
        }

        // Generate a nice download name
        $studentName = $submission->student?->user?->name ?? 'student';
        $assignmentTitle = $submission->assignment?->title ?? 'assignment';
        $downloadName = \Illuminate\Support\Str::slug("{$assignmentTitle}_{$studentName}_") . basename($path);

        return response()->download($disk->path($path), $downloadName);
    }
}
