<x-layout title="Submission Feedback">
    <nav class="mb-2 text-sm text-gray-600 p-3" aria-label="Breadcrumb">
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
      <li>
        <a href ="{{ route("student.assignments.show", $assignment) }}" class="hover:underline">
        {{ $assignment->title }}
        </a>
        <span class="mx-2">/</span>
      </li>
        <li class="text-black font-semibold">
            Submission Feedback
        </li>
    </ol>
  </nav>
    <section class="max-w-2xl mx-auto px-4 py-4 space-y-6 border border-gray-300 rounded-xl shadow-sm mt-6">
        <h1 class="text-2xl p-2 font-semibold">{{ $assignment->title }}</h1>

        <div class="bg-white rounded-xl border border-gray-300 p-6 space-y-3">
            <div class="text-sm text-gray-600">
                <p>
                    Submitted:
                    {{ $submission->created_at->format("M d, Y H:i") }}
                </p>
                @if ($submission->graded_at)
                    <p>
                        Graded:
                        {{ $submission->graded_at->format("M d, Y H:i") }}
                    </p>
                @endif
            </div>

            <div class="text-sm">
                @if ($submission->url)
                    <p>
                        URL:
                        <a
                            href="{{ $submission->url }}"
                            class="text-blue-600 hover:underline"
                        >
                            {{ $submission->url }}
                        </a>
                    </p>
                @endif

                @if ($submission->file_path)
                    <p class="text-sm text-gray-500 mt-1">
                        My Work:
                        <a
                            class="text-blue-600 hover:underline"
                            href="{{ route("student.submissions.download", $submission) }}"
                        >
                            {{ basename($submission->file_path) }}
                        </a>
                    </p>
                @endif
            </div>

            {{-- Score + comment (if assessed) --}}
            @if ($submission->assessment)
                <div class="mt-2 rounded-lg">
                    <p class="font-medium">
                        Score: <span class="bg-green-100 text-green-800 inline-block px-3 py-1 rounded">{{ $submission->assessment->score }} / 10 </span>
                    </p>
                    @if ($submission->assessment->comment)
                        <p class="text-gray-700 mt-2 border border-gray-200 rounded-lg p-4 bg-gray-50 min-h-[100px]">
                            {{ $submission->assessment->comment }}
                        </p>
                    @endif
                </div>
            @else
                <p class="text-gray-600">Awaiting grading.</p>
            @endif
            <div class="mt-2 border-b border-gray-200 pb-4">
                @if (! $submission->assessment && $canEdit)
                    <a
                        href="{{ route("student.assignments.submissions.edit", [$assignment, $submission]) }}"
                        class="px-4 py-2 bg-yellow-500 text-white rounded border border-yellow-600 text-sm hover:bg-yellow-600"
                    >
                        Edit submission
                    </a>
                @endif
            </div>

            <div class="mt-2">
                <a  class="px-4 py-2 rounded border border-gray-300 text-sm hover:bg-gray-100"
                    href="{{ route("student.assignments.index", $assignment->course) }}?level={{ $assignment->level }}"
                    
                >
                    &larr; Back
                </a>
            </div>
            </div>
        </div>
    </section>
</x-layout>
