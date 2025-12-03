<x-layout title="Submission Feedback">
    <div class="max-w-2xl mx-auto px-4 py-6 space-y-6">
        <h1 class="text-xl font-semibold">{{ $assignment->title }}</h1>

        <div class="bg-white rounded-xl shadow p-6 space-y-3">
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
                        Current:
                        <a
                            class="text-blue-600 hover:underline"
                            href="{{ route("student.submissions.download", $submission) }}"
                        >
                            download
                        </a>
                    </p>
                @endif
            </div>

            {{-- Score + comment (if assessed) --}}
            @if ($submission->assessment)
                <div class="mt-2 p-4 bg-green-50 rounded-lg">
                    <p class="font-medium">
                        Score: {{ $submission->assessment->score }}
                    </p>
                    @if ($submission->assessment->comment)
                        <p class="text-gray-700 mt-1">
                            {{ $submission->assessment->comment }}
                        </p>
                    @endif
                </div>
            @else
                <p class="text-gray-600">Awaiting grading.</p>
            @endif

            <div class="pt-2">
                @if (! $submission->graded_at)
                    <a
                        href="{{ route("student.assignments.submissions.edit", [$assignment, $submission]) }}"
                        class="text-blue-600 hover:underline"
                    >
                        Edit submission
                    </a>
                @endif

                <span class="mx-2 text-gray-400">•</span>
                <a
                    href="{{ route("student.assignments.index", $assignment->course) }}"
                    class="text-gray-600 hover:underline"
                >
                    Back to assignments
                </a>
            </div>
        </div>
    </div>
</x-layout>
