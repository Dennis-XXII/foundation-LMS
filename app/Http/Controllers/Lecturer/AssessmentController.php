<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{
    /**
     * List all submissions for a course (for review/assessment).
     * Shows latest first, with student & assignment eager-loaded.
     */
    public function index(Course $course)
    {
        // Ensure this lecturer can see this course
        $this->authorize('view', $course);

        $submissions = Submission::with(['student.user', 'assignment'])
            ->whereHas('assignment', fn ($q) => $q->where('course_id', $course->id))
            ->latest('submitted_at')
            ->paginate(20);

        return view('lecturer.assessments.index', compact('course', 'submissions'));
    }

    /**
     * Show single submission detail page with assess form (score/comment).
     */
    public function show(Course $course, Submission $submission)
    {
        $this->authorize('view', $course);
        $this->authorize('view', $submission); // Ensure lecturer is tied to the course of this submission

        // Defensive: submission must belong to this course
        abort_unless(
            optional($submission->assignment)->course_id === $course->id,
            404,
            'Submission not found for this course.'
        );

        $submission->load(['student.user', 'assignment']);

        return view('lecturer.assessments.show', compact('course', 'submission'));
    }

    /**
     * Download the student's uploaded file.
     * Uses response()->download(...) per your note.
     */
    public function download(Course $course, Submission $submission)
    {
        $this->authorize('view', $course);
        $this->authorize('view', $submission);

        abort_unless(
            optional($submission->assignment)->course_id === $course->id,
            404
        );

        if (!$submission->file_path) {
            abort(404, 'No file attached.');
        }

        $fullPath = storage_path('app/public/' . ltrim($submission->file_path, '/'));
        if (!is_file($fullPath)) {
            abort(404, 'File not found on server.');
        }

        // Optionally set a nice download name:
        $downloadName = sprintf(
            '%s-%s-%s',
            $submission->assignment?->title ?? 'assignment',
            $submission->student?->user?->name ?? 'student',
            basename($fullPath)
        );

        return response()->download($fullPath, $downloadName);
    }

    /**
     * Store/update the assessment (score, comment, feedback file).
     * Idempotent: if already assessed, update it.
     */
    public function store(Request $request, Course $course, Submission $submission)
    {
        $this->authorize('update', $submission); // “assess” ability can be mapped to update
        abort_unless(optional($submission->assignment)->course_id === $course->id, 404);

        // Validate inputs. Score is 0–100 by default; change as needed.
        $validated = $request->validate([
            'score'               => ['nullable', 'numeric', 'min:0', 'max:100'],
            'comment'             => ['nullable', 'string', 'max:2000'],
            'feedback_file'       => ['nullable', 'file', 'max:10240'], // 10MB
            'clear_feedback_file' => ['nullable', 'boolean'],
        ]);

        // Handle optional feedback file upload
        if ($request->boolean('clear_feedback_file')) {
            $submission->feedback_file_path = null;
        }
        if ($request->hasFile('feedback_file')) {
            $submission->feedback_file_path = $request->file('feedback_file')->store('assessments', 'public');
        }

        // Save score/comment & audit fields (assessor + assessed_at)
        $submission->score        = array_key_exists('score', $validated) ? $validated['score'] : $submission->score;
        $submission->comment      = array_key_exists('comment', $validated) ? $validated['comment'] : $submission->comment;
        $submission->assessed_by  = Auth::id();
        $submission->assessed_at  = now();
        $submission->save();

        return back()->with('success', 'Assessment saved 👍');
    }

    /**
     * Download the lecturer’s feedback file (if any).
     */
    public function downloadFeedback(Course $course, Submission $submission)
    {
        $this->authorize('view', $course);
        $this->authorize('view', $submission);
        abort_unless(optional($submission->assignment)->course_id === $course->id, 404);

        if (!$submission->feedback_file_path) {
            abort(404, 'No feedback file found.');
        }

        $fullPath = storage_path('app/public/' . ltrim($submission->feedback_file_path, '/'));
        if (!is_file($fullPath)) {
            abort(404, 'Feedback file not found on server.');
        }

        return response()->download($fullPath, basename($fullPath));
    }
}
