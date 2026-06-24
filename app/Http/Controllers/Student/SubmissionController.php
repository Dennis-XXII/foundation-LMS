<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SpecialProject;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    /**
     * Show form to create a submission for a special project
     */
    public function create(SpecialProject $specialProject)
    {
        $this->authorize('view', $specialProject->course);

        return view('student.submissions.create', compact('specialProject'));
    }

    /**
     * Store a new submission
     */
    public function store(Request $request, SpecialProject $specialProject)
    {
        $this->authorize('view', $specialProject->course);

        $data = $request->validate([
            'file' => ['nullable', 'file', 'max:10240'], // 10MB
        ]);

        $studentId = Auth::user()->student->id;

        DB::transaction(function () use ($specialProject, $studentId, $request) {
            $submission = Submission::firstOrCreate(
                ['special_project_id' => $specialProject->id, 'student_id' => $studentId],
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

        return to_route('student.special_projects.show', $specialProject)
            ->with('success', 'Submission saved.');
    }

    /**
     * Show a submission
     */
    public function show(SpecialProject $specialProject, Submission $submission)
    {
        $this->authorize('view', $specialProject->course);

        return view('student.submissions.show', compact('specialProject', 'submission'));
    }

    /**
     * Edit a submission
     */
    public function edit(SpecialProject $specialProject, Submission $submission)
    {
        $this->authorize('update', $submission);

        return view('student.submissions.edit', compact('specialProject', 'submission'));
    }

    /**
     * Update a submission
     */
    public function update(Request $request, SpecialProject $specialProject, Submission $submission)
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

        return to_route('student.special_projects.show', $specialProject)
            ->with('success', 'Submission updated.');
    }

    public function download(Submission $submission)
    {
        $this->authorize('view', $submission->specialProject->course);

        if (!$submission->file_path || !Storage::disk('submissions')->exists($submission->file_path)) {
            return to_route('student.special_projects.show', $submission->specialProject)
                ->with('error', 'File not found.');
        }

        return Storage::disk('submissions')->download($submission->file_path);
    }
}
