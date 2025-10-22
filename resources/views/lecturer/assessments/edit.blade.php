{{-- resources/views/lecturer/assessments/edit.blade.php --}}
<x-layout>
    @php
        // $submission and potentially $assessment (may be null or non-existent model if creating)
        $assignment = $submission->assignment->load('course');
        $course = $assignment->course;
        $student = $submission->student->user;

        // More explicit check: Does the assessment model exist in the database?
        $isUpdating = $assessment && $assessment->exists;

        // Determine form action
        $formAction = $isUpdating
            ? route('lecturer.submissions.assessments.update', ['submission' => $submission, 'assessment' => $assessment])
            : route('lecturer.submissions.assessments.store', ['submission' => $submission]);
    @endphp

    {{-- ... breadcrumbs, header, flash messages ... --}}
     <nav class="mb-6 text-sm text-gray-600" aria-label="Breadcrumb">
        <ol class="list-reset flex">
            <li><a href="{{ route('lecturer.dashboard') }}" class="hover:underline">Dashboard</a><span class="mx-2">/</span></li>
            <li><a href="{{ route('lecturer.courses.assessments.index', $course) }}" class="hover:underline">Assess Student Uploads</a><span class="mx-2">/</span></li>
            <li><a href="{{ route('lecturer.assignments.submissions.index', $assignment) }}" class="hover:underline">{{ $assignment->title }} - Submissions</a><span class="mx-2">/</span></li>
            <li class="text-black font-semibold">Grade: {{ $student->name }}</li>
        </ol>
    </nav>

    <div class="mb-6 p-4 rounded-lg bg-blue-100 border border-blue-200">
        <h1 class="text-2xl font-semibold text-blue-800">Grading Submission</h1>
        <p class="text-blue-700">Assignment: {{ $assignment->title }} (Level {{ $assignment->level ?? 'N/A' }})</p>
        <p class="text-blue-700">Student: {{ $student->name }} ({{ $student->email }})</p>
         <p class="text-blue-700">Submitted At: {{ optional($submission->submitted_at)->format('d M Y, H:i') ?? 'N/A' }}</p>
    </div>

     @if(session('success')) <div class="mb-4 bg-green-50 text-green-700 border border-green-200 px-4 py-2 rounded">{{ session('success') }}</div> @endif
     @if($errors->any())
        <div class="mb-4 bg-rose-50 text-rose-800 border border-rose-200 px-4 py-2 rounded">
            <p class="font-semibold">Error:</p>
            <ul class="list-disc list-inside">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>
     @endif


    {{-- ===================== ASSESSMENT FORM ===================== --}}
    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow border p-6 space-y-4">
        @csrf
        {{-- Use more explicit check for adding @method --}}

        {{-- Display Student Submission --}}
        @if($submission->file_path)
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Student Submission</label>
                {{-- Ensure lecturer.submissions.download route exists --}}
                <a href="{{ route('lecturer.submissions.download', $submission) }}" class="text-blue-600 hover:underline font-medium">Download Submitted File</a>
            </div>
            <hr class="my-4">
        @else
            <p class="text-gray-500">No file was submitted.</p>
             <hr class="my-4">
        @endif

        {{-- Score --}}
        <div>
            <label for="score" class="block text-sm font-medium text-gray-700">Score</label>
            <div class="mt-1 flex items-center gap-2">
                 <input type="number" id="score" name="score" min="0" max="10" value="{{ old('score', $assessment->score ?? '') }}" class="w-20 border rounded px-2 py-1 text-sm @error('score') border-red-500 @enderror">
                 <span class="text-sm text-gray-600">/ 10</span>
            </div>
             @error('score') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Comment --}}
        <div>
             <label for="comment" class="block text-sm font-medium text-gray-700">Comment / Feedback</label>
             <textarea id="comment" name="comment" rows="4" placeholder="Provide feedback here..." class="mt-1 w-full border rounded px-3 py-2 text-sm @error('comment') border-red-500 @enderror">{{ old('comment', $assessment->comment ?? '') }}</textarea>
             @error('comment') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Feedback File --}}
        <div>
             <label for="feedback_file" class="block text-sm font-medium text-gray-700">Upload Feedback File (Optional)</label>
             <input type="file" id="feedback_file" name="feedback_file" class="mt-1 block text-sm @error('feedback_file') border border-red-500 @enderror">
             @error('feedback_file') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

             {{-- Use more explicit check --}}
             @if($isUpdating && $assessment->feedback_file_path)
                 <div class="mt-2 text-sm">
                     <span class="text-gray-600">Current feedback file:</span>
                     {{-- Ensure lecturer.assessments.downloadFeedback route exists --}}
                     <a href="{{ route('lecturer.assessments.downloadFeedback', $assessment) }}" class="text-green-700 hover:underline ml-2">Download Feedback</a>
                     <label class="text-xs text-red-600 ml-4"><input type="checkbox" name="clear_feedback_file" value="1"> Clear file on save</label>
                 </div>
             @endif
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-4 pt-4 border-t">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm font-medium hover:bg-blue-700">
                {{ $isUpdating ? 'Update Grade' : 'Save Grade' }}
            </button>
            {{-- Link back to the list of submissions for this assignment --}}
            <a href="{{ route('lecturer.assignments.submissions.index', $assignment) }}" class="px-4 py-2 border rounded text-sm">
                Cancel
            </a>
            {{-- Use more explicit check --}}
            @if($isUpdating)
                {{-- Delete Assessment button --}}
                <form method="POST" action="{{ route('lecturer.submissions.assessments.destroy', ['submission' => $submission, 'assessment' => $assessment]) }}" onsubmit="return confirm('Remove this grade?');" class="ml-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:underline text-sm">Remove Grade</button>
                </form>
            @endif
        </div>
    </form>

</x-layout>