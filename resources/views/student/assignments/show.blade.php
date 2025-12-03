{{-- resources/views/student/assignments/show.blade.php --}}
<x-layout :title="$assignment->title">
  {{-- Breadcrumbs --}}
  <nav class="mb-6 text-sm text-gray-600" aria-label="Breadcrumb">
    <ol class="list-reset flex">
      <li>
        <a href="{{ route('student.dashboard') }}" class="hover:underline">Dashboard</a>
        <span class="mx-2">/</span>
      </li>
      <li>
        <a
                    href="{{ route("student.assignments.index", $assignment->course) }}?level={{ $assignment->level }}"
                    class="hover:underline"
                >
                    Assignments
                </a>
        <span class="mx-2">/</span>
      </li>
      <li class="text-black font-semibold">
        {{ $assignment->title }}
      </li>
    </ol>
  </nav>
  <div class="max-w-8xl mx-auto p-3">
        @php
            $levelColors = [
                3 => "bg-[#9bd1f8]",
                2 => "bg-[#c7f7cf]",
                1 => "bg-[#f0c6bc]",
            ];
            // Use level filter for header, default to gray
            $headerColor = $levelColors[$assignment->level ?? null] ?? "bg-gray-100";
        @endphp

  {{-- Main Content Card --}}
  <div class="max-w-4xl mx-auto p-6 rounded-lg shadow border">

    {{-- Header --}}
    <div class="flex items-center {{ $headerColor }} rounded-lg justify-between p-4">
        <div>
        <h1 class="text-2xl font-bold">{{ $assignment->title }}</h1>
        <h1 class="text-lg text-gray-600 font-thin">{{ $assignment->level ? "Level " . $assignment->level : "All Levels" }}</h1>
        </div>
        {{-- No Edit/Delete buttons for students --}}
    </div>

    {{-- Details Grid --}}
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Column 1 --}}
      <div class="md:col-span-2 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-500">Instruction</label>
          <div
                        class="mt-1 p-3 min-h-[100px] text-gray-800 bg-gray-50 rounded border"
                    >
            {{ $assignment->instruction ?? 'No instruction provided.' }}
          </div>
        </div>

        @if ($assignment->file_path)
          <div>
            <label class="block text-sm font-medium text-gray-500">Task File</label>
            <a href="{{ route('student.assignments.download', $assignment) }}" class="mt-1 text-blue-600 hover:underline font-medium">
              Download Attached File
            </a>
          </div>
        @endif
      </div>

      {{-- Column 2 (Sidebar): Assignment Meta --}}
      <div class="space-y-4 bg-gray-50 p-4 rounded-lg">
        <div>
          <label class="block text-sm font-medium text-gray-700">Level</label>
          <p class="mt-1 text-gray-900">{{ $assignment->level ?? '—' }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Week</label>
          <p class="mt-1 text-gray-900">{{ $assignment->week ?? '—' }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Day</label>
          <p class="mt-1 text-gray-900">{{ $assignment->day ?? '—' }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Due Date</label>
          <p class="mt-1 text-red-600">{{ optional($assignment->due_at)->format('M d, Y, H:i') ?? '—' }}</p>
        </div>
      </div>
    </div>

    {{-- Submission Status & Action Area --}}
    <div class="mt-8 pt-6 border-t">
        <h2 class="text-xl font-semibold mb-3">Your Submission</h2>

        <div class="p-4 rounded-lg bg-gray-50 border flex flex-col md:flex-row md:items-center justify-between gap-4">
            {{-- Status Display --}}
            <div>
                <label class="block ml-2 text-xs font-medium text-gray-700">Status</label>
                <span @class([
    // Static classes (always applied)
    'flex items-center mt-2 px-3 py-1 rounded-xl text-sm font-medium',
    
    // Dynamic classes (applied based on condition)
    'bg-green-100 text-green-800'   => $status === 'Graded',
    'bg-blue-100 text-blue-800'     => $status === 'Submitted',
    'bg-red-100 text-red-600'       => $status === 'Closed',
    'bg-yellow-100 text-yellow-800' => $status === 'Open',
])>
    {{ $status }}
</span>
                @if ($submission && $status !== 'Graded')
                   <p class="text-xs text-gray-500 mt-1">Submitted: {{ $submission->created_at->format('M d, Y, H:i') }}</p>
                @endif
            </div>

             {{-- Action Button --}}
            <div>
                @if($canSubmit)
                    <a href="{{ route('student.assignments.submissions.create', $assignment) }}"
                       class="px-4 py-2 bg-blue-600 text-white rounded text-sm font-medium hover:bg-blue-700">
                       Submit Now
                    </a>
                @elseif($canEdit)
                    <a href="{{ route('student.assignments.submissions.edit', [$assignment, $submission]) }}"
                       class="px-4 py-2 bg-yellow-500 text-white rounded text-sm font-medium hover:bg-yellow-600">
                       Edit Submission
                    </a>
                @elseif($canViewFeedback)
                    <a href="{{ route('student.assignments.submissions.show', [$assignment, $submission]) }}"
                       class="px-4 py-2 bg-green-600 text-white rounded text-sm font-medium hover:bg-green-700">
                       View Feedback
                    </a>
                @elseif($status === 'Closed')
                     <span class="px-4 py-2 bg-red-600 text-white rounded text-sm font-medium cursor-not-allowed">
                       Past Due
                    </span>
                @else
                     {{-- Fallback if needed --}}
                     <span class="text-sm text-gray-500">No action available</span>
                @endif
            </div>
        </div>

        {{-- Display Submission Details if Submitted --}}
        @if ($submission)
            <div class="mt-4 text-sm">
                <h3 class="font-medium text-gray-700 mb-2">Submitted Details:</h3>
                <div class="pl-4 space-y-1">
                    @if ($submission->file_path)
                         <p>File: <a href="{{ route('student.submissions.download', $submission) }}" class="text-blue-600 hover:underline">Download Your Submission</a></p>
                    @endif
                    {{-- Add URL or notes if your submission model has them --}}
                    {{-- @if ($submission->url) <p>URL: <a href="{{ $submission->url }}" target="_blank" class="text-blue-600 hover:underline">{{ $submission->url }}</a></p> @endif --}}
                    {{-- @if ($submission->notes) <p>Notes: {{ $submission->notes }}</p> @endif --}}
                </div>
            </div>
        @endif
    </div>

    {{-- Back Link --}}
    <div class="mt-8 pt-4 border-t">
        <a
                    href="{{ route("student.assignments.index", $assignment->course) }}?level={{ $assignment->level }}"
                class="px-4 py-2 rounded border text-sm"
            >
            &larr; Back
        </a>
    </div>
  </div>
</x-layout>