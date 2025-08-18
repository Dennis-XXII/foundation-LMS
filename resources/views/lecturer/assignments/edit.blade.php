{{-- resources/views/lecturer/assignments/edit.blade.php --}}
<x-layout> {{-- or your app layout --}}

<!--breadcrumbs-->
  <nav class="mb-6 text-sm text-gray-600" aria-label="Breadcrumb">
    <ol class="list-reset flex">
      <li>
        <a href="{{ route('lecturer.dashboard') }}" class="hover:underline">Dashboard</a>
        <span class="mx-2">/</span>
      </li>
      <li class="text-black">
        <a href="{{ route('lecturer.courses.assignments.index', $assignment->course)}}" class="hover:underline">Upload Links</a>
        <span class="mx-2">/</span>
      </li>
      <li class="text-black font-semibold">
        Edit Assignment
      </li>
    </ol>
  </nav>
  <!--breadcrumbs end-->
  
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-2xl font-semibold">Edit Assignment</h1>
        <p class="text-sm text-gray-600 mt-1">
            Course: {{ $assignment->course->code }} — {{ $assignment->course->name }}
        </p>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="mt-4 p-3 rounded bg-green-50 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Validation errors --}}
        @if($errors->any())
            <div class="mt-4 p-3 rounded bg-red-50 text-red-700">
                <ul class="list-disc ml-5">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Update form --}}
        <form class="mt-6 space-y-4" method="POST" action="{{ route('lecturer.assignments.update', $assignment) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium">Title</label>
                <input name="title" type="text" class="mt-1 w-full border rounded px-3 py-2"
                       value="{{ old('title', $assignment->title) }}" required>
            </div>

            <div>
                <label class="block text-sm font-medium">Instruction</label>
                <textarea name="instruction" rows="5" class="mt-1 w-full border rounded px-3 py-2">{{ old('instruction', $assignment->instruction) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium">Level</label>
                    <input name="level" type="number" min="1" class="mt-1 w-full border rounded px-3 py-2"
                           value="{{ old('level', $assignment->level) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium">Due at</label>
                    <input name="due_at" type="datetime-local" class="mt-1 w-full border rounded px-3 py-2"
                           value="{{ old('due_at', optional($assignment->due_at)->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="flex items-end gap-2">
                    <label class="block text-sm font-medium sr-only">Published</label>
                    <input id="is_published" name="is_published" type="checkbox" value="1"
                           @checked(old('is_published', $assignment->is_published))>
                    <label for="is_published" class="text-sm">Published</label>
                </div>
            </div>

            {{-- Attachment / URL section (mutually exclusive UX) --}}
            <div class="border rounded p-4 space-y-3">
                <div class="text-sm font-medium">Attachment / URL</div>

                @if($assignment->file_path)
                    <div class="flex items-center gap-3">
                        <a class="text-blue-600 underline"
                           href="{{ route('lecturer.assignments.download', $assignment) }}">
                            Download current file
                        </a>

                        {{-- Remove current file --}}
                        <label class="inline-flex items-center gap-2 text-sm text-red-700">
                            <input type="checkbox" name="remove_file" value="1">
                            Remove current file on save
                        </label>
                    </div>
                @endif

                <div>
                    <label class="block text-xs text-gray-600">Upload new file (replaces current)</label>
                    <input name="file" type="file" class="mt-1 block">
                    <p class="text-xs text-gray-500 mt-1">Allowed: pdf, doc, docx, ppt, pptx, zip. Max 20MB.</p>
                </div>

                <div>
                    <label class="block text-xs text-gray-600">OR external URL</label>
                    <input name="url" type="url" class="mt-1 w-full border rounded px-3 py-2"
                           value="{{ old('url', $assignment->url) }}" placeholder="https://...">
                    <p class="text-xs text-gray-500 mt-1">Choose either a URL or a file, not both.</p>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 rounded bg-black text-white">Save changes</button>
                <a href="{{ route('lecturer.dashboard') }}" class="px-4 py-2 rounded border">Back</a>
            </div>
        </form>

        {{-- Submissions table (anchor #submissions) --}}
        <h2 id="submissions" class="mt-10 text-xl font-semibold">Submissions</h2>
        @if($assignment->submissions->isEmpty())
            <p class="text-sm text-gray-600 mt-2">No submissions yet.</p>
        @else
            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full text-sm border">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Student</th>
                            <th class="px-3 py-2 text-left">Submitted at</th>
                            <th class="px-3 py-2 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignment->submissions as $s)
                            <tr class="border-t">
                                <td class="px-3 py-2">
                                    {{ $s->student->user->name }}
                                    <span class="text-gray-500">({{ $s->student->user->email }})</span>
                                </td>
                                <td class="px-3 py-2">{{ optional($s->submitted_at)->format('Y-m-d H:i') }}</td>
                                <td class="px-3 py-2">
                                    @if($s->file_path)
                                        <a class="text-blue-600 underline"
                                           href="{{ route('student.submissions.download', $s) }}">Download</a>
                                    @else
                                        <span class="text-gray-500">No file</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layout>
