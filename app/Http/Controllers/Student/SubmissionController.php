<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    /**
     * Show form to create a submission for an assignment
     */
    public function create(Assignment $assignment)
    {
        $this->authorize('view', $assignment->course);

        return view('student.submissions.create', compact('assignment'));
    }

    /**
     * Store a new submission
     */
    public function store(Request $request, Assignment $assignment)
    {
        $this->authorize('view', $assignment->course);

        $data = $request->validate([
            'file' => ['nullable', 'file', 'max:10240'], // 10MB
        ]);

        $studentId = Auth::user()->student->id;

        DB::transaction(function () use ($assignment, $studentId, $request) {
            $submission = Submission::firstOrCreate(
                ['assignment_id' => $assignment->id, 'student_id' => $studentId],
                []
            );

            if ($request->hasFile('file')) {
    if ($submission->file_path) {
        Storage::disk('submissions')->delete($submission->file_path);
    }

    $file = $request->file('file');
$originalName = $file->getClientOriginalName();
$uniqueName = $submission->student_id . '_' . time() . '_' . $originalName;

$submission->file_path = $file->storeAs(
    date('Y/m/d'),
    $uniqueName,
    'submissions'
);
}

            $submission->submitted_at = now();
            $submission->save();
        });

        return to_route('student.assignments.show', $assignment)
            ->with('success', 'Submission saved.');
    }

    /**
     * Show a submission
     */
    public function show(Assignment $assignment, Submission $submission)
    {
        $this->authorize('view', $assignment->course);

        return view('student.submissions.show', compact('assignment', 'submission'));
    }

    /**
     * Edit a submission
     */
    public function edit(Assignment $assignment, Submission $submission)
    {
        $this->authorize('update', $submission);

        return view('student.submissions.edit', compact('assignment', 'submission'));
    }

    /**
     * Update a submission
     */
    public function update(Request $request, Assignment $assignment, Submission $submission)
    {
        $this->authorize('update', $submission);

        $data = $request->validate([
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        if ($request->hasFile('file')) {
    if ($submission->file_path) {
        Storage::disk('submissions')->delete($submission->file_path);
    }

    $file = $request->file('file');
    $originalName = $file->getClientOriginalName();
    $uniqueName = $submission->student_id . '_' . time() . '_' . $originalName;

    $submission->file_path = $file->storeAs(
        date('Y/m/d'),
        $uniqueName,
        'submissions'
    );
}

        $submission->submitted_at = now();
        $submission->save();

        return to_route('student.assignments.show', $assignment)
            ->with('success', 'Submission updated.');
    }

    public function download(Submission $submission)
    {
        $this->authorize('view', $submission->assignment->course);

        if (!$submission->file_path || !Storage::disk('submissions')->exists($submission->file_path)) {
            return to_route('student.assignments.show', $submission->assignment)
                ->with('error', 'File not found.');
        }

        return Storage::disk('submissions')->download($submission->file_path);
    }
}
