<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\SpecialProject;
use App\Models\Course;
use App\Models\Submission;
use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AssessmentController extends Controller
{
    /**
     * Display the assessment overview page for a course.
     * Fetches special projects and their submissions for assessment.
     * Route: lecturer.courses.assessments.index
     */
    public function index(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $level = (int) $request->query('level');
        if (!$level) $level = null;

        // Fetch special projects
        $specialProjectsQuery = SpecialProject::query()
            ->where('course_id', $course->id)
            ->when($level, fn ($query) => $query->where('level', $level))
            ->withCount('submissions')
            ->latest('due_at');

        // Paginate the list of special projects
        $specialProjects = $specialProjectsQuery->paginate(20)->withQueryString();

        return view('lecturer.assessments.index', compact(
            'course',
            'specialProjects',
            'level'
        ));
    }

    /**
     * Show the form for creating a new assessment resource.
     * Route: GET lecturer/submissions/{submission}/assessments/create
     */
    public function create(Submission $submission)
    {
        $this->authorize('update', $submission->specialProject->course);

        // Eager load necessary data for the view
        $submission->load(['student.user', 'specialProject.course']);
        
        $assessment = null; 

        return view('lecturer.assessments.edit', compact('submission', 'assessment'));
    }

    /**
     * Store a newly created assessment resource in storage.
     * Route: POST lecturer/submissions/{submission}/assessments
     */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'submission_id' => ['required', 'exists:submissions,id'],
            'score'         => ['nullable', 'numeric', 'min:0', 'max:10'],
            'comment'       => ['nullable', 'string', 'max:2000'],
            'feedback_file' => ['nullable', 'file', 'max:10240'],
            'clear_feedback_file' => ['nullable', 'boolean'],
        ]);
        
        $submission = Submission::findOrFail($validated['submission_id']);
        $this->authorize('update', $submission->specialProject->course);
        abort_unless($submission->submitted_at, 403, 'Cannot assess a special project that has not been submitted.');

        $validated = $request->validate([
            'score'         => ['nullable', 'numeric', 'min:0', 'max:10'],
            'comment'       => ['nullable', 'string', 'max:2000'],
            'feedback_file' => ['nullable', 'file', 'max:10240'],
            'clear_feedback_file' => ['nullable', 'boolean'],
        ]);

        $assessment = Assessment::firstOrNew(['submission_id' => $submission->id]);

        $assessmentData = [
            'score'       => $validated['score'] ?? null,
            'comment'     => $validated['comment'] ?? null,
            'lecturer_id' => Auth::user()->lecturer->id,
            'assessed_at'   => now(),
            'feedback_file_path' => $assessment->feedback_file_path,
        ];

        $diskName = 'public';
        $disk = Storage::disk($diskName);

        if ($request->boolean('clear_feedback_file') && $assessment->feedback_file_path) {
            $disk->delete($assessment->feedback_file_path);
            $assessmentData['feedback_file_path'] = null;
        }
        elseif ($request->hasFile('feedback_file')) {
            if ($assessment->feedback_file_path) {
                $disk->delete($assessment->feedback_file_path);
            }
            $feedbackPath = $request->file('feedback_file')->store('feedback/'.$submission->id, $diskName);
            $assessmentData['feedback_file_path'] = $feedbackPath;
        }

        Assessment::updateOrCreate(
            ['submission_id' => $submission->id],
            $assessmentData
        );

        return redirect()->route('lecturer.special_projects.submissions.index', $submission->specialProject)
                    ->with('success', 'Assessment saved successfully.')
                    ->withFragment('sub-' . $submission->id);
    }

    public function showSubmissions(Request $request, SpecialProject $specialProject)
    {
        $this->authorize('update', $specialProject->course);

        $specialProject->load([
            'course',
            'submissions' => function ($query) {
                $query->orderBy('student_id');
            },
            'submissions.student.user',
            'submissions.assessment'
        ]);

        return view('lecturer.assessments.edit', compact(
            'specialProject'
        ));
    }

    /**
     * Display the specified assessment resource.
     * Route: GET lecturer/submissions/{submission}/assessments/{assessment}
     */
    public function show(Submission $submission, Assessment $assessment)
    {
        $this->authorize('update', $submission->specialProject->course);
        abort_unless($assessment->submission_id === $submission->id, 404);

        return redirect()->route('lecturer.courses.assessments.index', $submission->specialProject->course)
               ->withFragment('assess-' . $submission->id);
    }

    /**
     * Show the form for editing the specified assessment resource.
     * Route: GET lecturer/submissions/{submission}/assessments/{assessment}/edit
     */
    public function edit(Submission $submission, Assessment $assessment)
    {
        abort_unless($assessment->submission_id === $submission->id, 404);

        $this->authorize('update', $submission->specialProject->course);

        $submission->load(['student.user', 'specialProject.course']);

        return view('lecturer.assessments.edit', compact('submission', 'assessment'));
    }

    /**
     * Remove the specified assessment resource from storage.
     * Route: DELETE lecturer/submissions/{submission}/assessments/{assessment}
     */
    public function destroy(Submission $submission, Assessment $assessment)
    {
        $this->authorize('delete', $submission->specialProject->course);
        abort_unless($assessment->submission_id === $submission->id, 404);

        if ($assessment->feedback_file_path) {
            Storage::disk('public')->delete($assessment->feedback_file_path);
        }

        $assessment->delete();

        return back()->with('success', 'Assessment removed successfully.')
                     ->withFragment('assess-' . $submission->id);
    }
}