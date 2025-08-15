<x-layout title="Edit Submission">
<div class="max-w-2xl mx-auto px-4 py-6 space-y-6">
    <h1 class="text-xl font-semibold">{{ $assignment->title }}</h1>

    <form method="POST" action="{{ route('student.assignments.submissions.update', [$assignment, $submission]) }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow p-6 space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700">Submission URL</label>
            <input type="url" name="url" value="{{ old('url', $submission->url) }}" class="mt-1 w-full rounded border-gray-300">
            @error('url')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Replace File</label>
            <input type="file" name="file" class="mt-1 w-full">
            @error('file')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            @if($submission->file_path)
                <p class="text-sm text-gray-500 mt-1">Current: <a class="text-blue-600 hover:underline" href="{{ route('submission.download', $submission) }}">download</a></p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea name="notes" rows="4" class="mt-1 w-full rounded border-gray-300">{{ old('notes', $submission->notes) }}</textarea>
            @error('notes')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center gap-3">
            <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update</button>
            <a href="{{ route('student.assignments.index', $assignment->course) }}" class="text-gray-600">Back</a>
        </div>
    </form>
</div>
</x-layout>
