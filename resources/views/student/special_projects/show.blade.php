{{-- resources/views/student/special_projects/show.blade.php --}}
<x-layout :title="$specialProject->title">
  {{-- Breadcrumbs --}}
  <nav class="hidden lg:flex mb-2 text-sm text-gray-600 p-3" aria-label="Breadcrumb">
    <ol class="list-reset flex">
      <li>
        <a href="{{ route('student.dashboard') }}" class="hover:underline">Dashboard</a>
        <span class="mx-2">/</span>
      </li>
      <li>
        <a
            href="{{ route("student.special_projects.index", $specialProject->course) }}?level={{ $specialProject->level }}"
            class="hover:underline"
        >
            Special Projects
        </a>
        <span class="mx-2">/</span>
      </li>
      <li class="text-black font-semibold">
        {{ $specialProject->title }}
      </li>
    </ol>
  </nav>
    <a
            href="{{ route("student.special_projects.index", $specialProject->course) }}?level={{ $specialProject->level }}"
            class="lg:hidden text-sm text-blue-600 hover:underline px-4 py-2 rounded border mb-4 inline-block"
        >
            &larr; Back to Special Projects
        </a>
        @php
            $levelColors = [
                3 => "bg-[#9bd1f8]",
                2 => "bg-[#c7f7cf]",
                1 => "bg-[#f0c6bc]",
            ];
            $headerColor = $levelColors[$specialProject->level ?? null] ?? "bg-gray-100";
        @endphp

  {{-- Main Content Card --}}
  <section class="max-w-4xl mx-auto rounded-lg lg:shadow lg:border border-gray-300">

  {{-- Flashes / Errors --}}
    @if (session("success"))
        <div
            class="mb-4 bg-green-50 text-green-800 border border-green-200 px-4 py-2 rounded"
        >
            {{ session("success") }}
        </div>
    @endif

    @if (session("info"))
        <div
            class="mb-4 bg-blue-50 text-blue-800 border border-blue-200 px-4 py-2 rounded"
        >
            {{ session("info") }}
        </div>
    @endif

    @if ($errors->any())
        <div
            class="mb-4 bg-rose-50 text-rose-800 border border-rose-200 px-4 py-2 rounded"
        >
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center {{ $headerColor }} rounded-t-lg justify-between p-4">
        <div>
        <h1 class="text-2xl font-bold">{{ $specialProject->title }}</h1>
        <h1 class="text-lg text-gray-600 font-thin">{{ $specialProject->level ? "Level " . $specialProject->level : "All Levels" }}</h1>
        </div>
    </div>

    {{-- Details Grid --}}
    <div class="p-2 lg:p-4">
    <div class=" grid grid-cols-1 md:grid-cols-3 gap-6 rounded-b-lg">
            {{-- Column 1 --}}
      <div class="md:col-span-2 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-500">Instruction</label>
          <div
                        class="mt-1 p-3 min-h-[100px] text-gray-800 bg-gray-50 rounded-lg border border-gray-300"
                    >
            {{ $specialProject->instruction ?? 'No instruction provided.' }}
          </div>
        </div>

        @if ($specialProject->file_path)
          <div>
            <label class="block text-sm font-medium text-gray-500">Task File</label>
            <a href="{{ route('student.special_projects.download', $specialProject) }}" class="mt-1 text-blue-600 hover:underline font-medium">
              Download Attached File
            </a>
          </div>
        @endif
      </div>

      {{-- Column 2 (Sidebar): Special Project Meta --}}
      <div>
        <label class="block text-sm font-medium text-gray-500">Details</label>
      <div class="space-y-4 bg-gray-50 p-4 rounded-lg border border-gray-100 mt-1">
        <div class="pb-2 border-b border-gray-200">
          <label class="block text-xs font-medium text-gray-700">Level</label>
          <p class="mt-1 text-gray-900">{{ $specialProject->level ?? '—' }}</p>
        </div>
        <div class="pb-2 border-b border-gray-200">
          <label class="block text-xs font-medium text-gray-700">Week / Day</label>
          <p class="mt-1 text-gray-900">Week {{ $specialProject->week ?? '—' }} &mdash; {{ $specialProject->day ?? '—' }}</p>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700">Due Date</label>
          <p class="mt-1 text-red-600">{{ optional($specialProject->due_at)->format('M d, Y, H:i') ?? '—' }}</p>
        </div>
      </div>
      </div>
    </div>

    {{-- Submission Status & Action Area --}}

        <div class="mt-2 p-4 rounded-lg bg-gray-50 border border-gray-300 flex flex-col md:flex-row md:items-center justify-between gap-4">
            {{-- Status Display --}}
            <div>
                <label class="block ml-1 text-xl font-medium text-gray-700 mb-2">Submission Status : <span @class([
    ' items-center px-2 py-1.5 rounded-full text-sm font-medium',
    'bg-green-100 text-green-800'   => $status === 'Graded',
    'bg-blue-100 text-blue-800'     => $status === 'Submitted',
    'bg-red-100 text-red-600'       => $status === 'Closed',
    'bg-yellow-100 text-yellow-800' => $status === 'Open',
])>
    {{ $status }}
</span></label>
                
                @if ($submission && $status !== 'Graded')
                   <p class="text-xs text-gray-700 mt-2 ml-1">Submitted on : {{ $submission->created_at->format('M d, Y - H:i') }}</p>
                @endif

                @if ($submission)
                <div class="text-xs ml-1 mt-2">
                    @if ($submission->file_path)
                         <p class="block text-sm mb-2 font-medium text-gray-700">My Work : <a href="{{ route('student.submissions.download', $submission) }}" class="text-blue-600 hover:underline"> [{{ basename($submission->file_path) }}]</a>  </p>
                    @endif
                </div>
        @endif
            </div>

             {{-- Action Button --}}
            <div>
                @if($canSubmit)
                    <a href="{{ route('student.special-projects.submissions.create', $specialProject) }}"
                       class="px-4 py-2 bg-blue-600 text-white rounded text-sm font-medium hover:bg-blue-700">
                       Submit Now
                    </a>
                @elseif($canEdit)
                    <a href="{{ route('student.special-projects.submissions.edit', [$specialProject, $submission]) }}"
                       class="px-4 py-2 bg-yellow-500 text-white rounded text-sm font-medium hover:bg-yellow-600">
                       Edit Submission
                    </a>
                @elseif($canViewFeedback)
                    <a href="{{ route('student.special-projects.submissions.show', [$specialProject, $submission]) }}"
                       class="px-4 py-2 bg-green-600 text-white rounded text-sm font-medium hover:bg-green-700">
                       View Feedback
                    </a>
                @elseif($status === 'Closed')
                     <span class="px-4 py-2 bg-red-600 text-white rounded text-sm font-medium cursor-not-allowed">
                       Past Due
                    </span>
                @else
                     <span class="text-sm text-gray-500">No action available</span>
                @endif
            </div>
        </div>
  </section>
</x-layout>