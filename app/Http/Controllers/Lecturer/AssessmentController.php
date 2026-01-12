<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\Assessment; // Ensure Assessment model is imported
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule; // Import Rule

class AssessmentController extends Controller // Ensure class name matches file/routes (e.g., LecturerAssessmentsController)
{
    /**
     * Display the assessment overview page for a course.
     * Fetches assignments and their submissions for assessment.
     * Route: lecturer.courses.assessments.index
     */
    public function index(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $level = (int) $request->query('level');
        if (!$level) $level = null;

        // Fetch assignments, maybe only those with submissions or published ones
        $assignmentsQuery = Assignment::query()
            ->where('course_id', $course->id)
            ->when($level, fn ($query) => $query->where('level', $level))
            // Optionally add ->where('is_published', true)
            // Optionally add ->whereHas('submissions')
            ->withCount('submissions') // Get count of submissions for display
            ->latest('due_at');

        // Paginate the list of assignments
        $assignments = $assignmentsQuery->paginate(20)->withQueryString(); // Adjust pagination size

        return view('lecturer.assessments.index', compact( // Note: New view path
            'course',
            'assignments',
            'level'
        ));
    }

    /**
     * Show the form for creating a new assessment resource.
     * Typically handled inline, but included for resource controller completeness.
     * Route: GET lecturer/submissions/{submission}/assessments/create
     * Note: Your route alias 'submissions.assessments.create.alias' might point here too.
     */
    public function create(Submission $submission)
    {
        $this->authorize('update', $submission->assignment->course); // Authorize

        // Eager load necessary data for the view
        $submission->load(['student.user', 'assignment.course']);
        
        // Pass the submission and a null assessment to the edit view
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
        
        // Manually find the submission
        $submission = Submission::findOrFail($validated['submission_id']);
        $this->authorize('update', $submission->assignment->course); // Authorize the action
        abort_unless($submission->submitted_at, 403, 'Cannot assess an assignment that has not been submitted.');

        $validated = $request->validate([
            'score'         => ['nullable', 'numeric', 'min:0', 'max:10'],
            'comment'       => ['nullable', 'string', 'max:2000'],
            'feedback_file' => ['nullable', 'file', 'max:10240'],
            // Add clear_feedback_file to validation if the checkbox exists in the form
            'clear_feedback_file' => ['nullable', 'boolean'],
        ]);

        // Find existing assessment or create a new instance (without saving yet)
        $assessment = Assessment::firstOrNew(['submission_id' => $submission->id]);

        // Prepare data
        $assessmentData = [
            'score'       => $validated['score'] ?? null,
            'comment'     => $validated['comment'] ?? null,
            'lecturer_id' => Auth::user()->lecturer->id,
            'assessed_at'   => now(),
            // Keep existing feedback_file_path unless cleared or replaced
            'feedback_file_path' => $assessment->feedback_file_path,
        ];

        $diskName = 'public'; // Change if needed
        $disk = Storage::disk($diskName);

        // Handle feedback file update/clear logic (moved from update method)
        if ($request->boolean('clear_feedback_file') && $assessment->feedback_file_path) {
            $disk->delete($assessment->feedback_file_path);
            $assessmentData['feedback_file_path'] = null; // Mark for update
        }
        // Check for new file *after* checking clear, use elseif
        elseif ($request->hasFile('feedback_file')) {
            // Delete old file if it exists before storing new one
            if ($assessment->feedback_file_path) {
                $disk->delete($assessment->feedback_file_path);
            }
            $feedbackPath = $request->file('feedback_file')->store('feedback/'.$submission->id, $diskName);
            $assessmentData['feedback_file_path'] = $feedbackPath; // Mark for update
        }

        // Update or Create the assessment record
        Assessment::updateOrCreate(
            ['submission_id' => $submission->id], // Find condition
            $assessmentData // Data to insert or update with
        );

        return redirect()->route('lecturer.assignments.submissions.index', $submission->assignment)
                    ->with('success', 'Assessment saved successfully.')
                    ->withFragment('sub-' . $submission->id);
    }

    public function showSubmissions(Request $request, Assignment $assignment)
    {
        // Authorize viewing/updating the course this assignment belongs to
        $this->authorize('update', $assignment->course);

        // Eager load submissions with necessary related data
        $assignment->load([
            'course', // Load course for breadcrumbs/header
            'submissions' => function ($query) {
                // Order submissions as needed
                $query->orderBy('student_id');
            },
            'submissions.student.user', // Student details
            'submissions.assessment'    // Existing assessment data
        ]);

        // Pass the single assignment (with loaded submissions) to the new view
        return view('lecturer.assessments.edit', compact( // Note: New view path
            'assignment'
            // 'course' is available via $assignment->course
        ));
    }

    /**
     * Display the specified assessment resource.
     * Often not needed as details are shown inline. Included for completeness.
     * Route: GET lecturer/submissions/{submission}/assessments/{assessment}
     */
    public function show(Submission $submission, Assessment $assessment)
    {
        $this->authorize('update', $submission->assignment->course);
        // Ensure assessment belongs to submission
        abort_unless($assessment->submission_id === $submission->id, 404);

        // Load relationships if needed for a dedicated view
        // $assessment->load(['lecturer.user', 'submission.student.user']);
        // If you had a dedicated show view: return view('lecturer.assessments.show', compact('submission', 'assessment'));

        // Redirect back to the main assess page, potentially highlighting the row
         return redirect()->route('lecturer.courses.assessments.index', $submission->assignment->course)
               ->withFragment('assess-' . $submission->id);
    }

/**
     * Show the form for editing the specified assessment resource.
     * Route: GET lecturer/submissions/{submission}/assessments/{assessment}/edit
     */
    public function edit(Submission $submission, Assessment $assessment) // Make assessment optional for create case? Or handle via store
    {
        abort_unless($assessment->submission_id === $submission->id, 404);

        $this->authorize('update', $submission->assignment->course); // Authorize

        // Eager load necessary data for the view
        $submission->load(['student.user', 'assignment.course']);
        // $assessment might be null if creating via edit route, or already loaded

        // Return the dedicated edit view
        return view('lecturer.assessments.edit', compact('submission', 'assessment'));
    }



    /**
     * Remove the specified assessment resource from storage.
     * Route: DELETE lecturer/submissions/{submission}/assessments/{assessment}
     */
    public function destroy(Submission $submission, Assessment $assessment)
    {
        $this->authorize('delete', $submission->assignment->course); // Or a more specific policy
        abort_unless($assessment->submission_id === $submission->id, 404);

        // Delete feedback file if it exists
        if ($assessment->feedback_file_path) {
            Storage::disk('public')->delete($assessment->feedback_file_path); // Use your feedback disk
        }

        $assessment->delete(); // Use forceDelete() if using SoftDeletes and want permanent removal

        return back()->with('success', 'Assessment removed successfully.')
                     ->withFragment('assess-' . $submission->id);
    }

}