<x-layout title="Submit Assignment">
<div class="max-w-2xl mx-auto px-4 py-6 space-y-6">
    <h1 class="text-xl font-semibold">{{ $assignment->title }}</h1>

    <form method="POST" action="{{ route('student.assignments.submissions.store', $assignment) }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow p-6 space-y-4">
        @csrf
        {{-- URL (optional) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700">Submission URL</label>
            <input type="url" name="url" value="{{ old('url') }}" class="mt-1 w-full rounded border-gray-300" placeholder="https://...">
            @error('url')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        {{-- File (optional) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700">Upload File</label>
            <input type="file" name="file" class="mt-1 w-full">
            @error('file')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        {{-- Notes --}}
        <div>
            <label class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea name="notes" rows="4" class="mt-1 w-full rounded border-gray-300">{{ old('notes') }}</textarea>
            @error('notes')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center gap-3">
            <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Submit</button>
            <a href="{{ route('student.assignments.index', $assignment->course) }}" class="text-gray-600">Cancel</a>
        </div>
    </form>
</div>
</x-layout>
