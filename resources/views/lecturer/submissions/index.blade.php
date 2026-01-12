{{-- resources/views/lecturer/submissions/index.blade.php --}}
<x-layout>
    @php
        // $assignment is passed to this view by LecturerSubmissionsController@index
        $course = $assignment->course;
    @endphp

    <nav class="mb-2 text-sm text-gray-600 p-3" aria-label="Breadcrumb">
        <ol class="list-reset flex">
            <li>
                <a
                    href="{{ route("lecturer.dashboard") }}"
                    class="hover:underline"
                >
                    Dashboard
                </a>
                <span class="mx-2">/</span>
            </li>
            {{-- Link back to the list of assignments for assessment --}}
            <li>
                <a
                    href="{{ route("lecturer.courses.assessments.index", $course) }}?level={{ $assignment->level }}"
                    class="hover:underline"
                >
                    Assess Student Uploads
                </a>
                <span class="mx-2">/</span>
            </li>
            <li class="text-black font-semibold">
                {{ $assignment->title }} - Submissions
            </li>
        </ol>
    </nav>

    <section
        class="max-w-8xl mx-auto p-6 rounded-lg shadow border border-gray-300"
    >
        @php
            $levelColors = [
                3 => "bg-[#9bd1f8]",
                2 => "bg-[#c7f7cf]",
                1 => "bg-[#f0c6bc]",
            ];
            // Use level filter for header, default to gray
            $headerColor = $levelColors[$assignment->level ?? null] ?? "bg-gray-100";
        @endphp

        <div class="mb-6 p-4 rounded-lg {{ $headerColor }}">
            <h1 class="text-2xl font-semibold text-gray-900">
                {{ $assignment->title }}
            </h1>
            <p class="text-gray-700">
                Level {{ $assignment->level ?? "N/A" }}
                @if ($assignment->week && $assignment->day)
                        | Week {{ $assignment->week }}, {{ $assignment->day }}
                @endif

                @if ($assignment->due_at)
                        | Due: {{ $assignment->due_at->format("d M Y") }}
                @endif
            </p>
            <a
                href="{{ route("lecturer.assignments.show", $assignment) }}"
                class="text-xs text-blue-600 hover:underline mt-1 inline-block"
            >
                View Assignment Details
            </a>
        </div>

        <div class="mt-6">
            {{-- Card Header --}}
            <h2 class="text-xl font-semibold mb-4 text-gray-800">
                Student Submissions Overview
            </h2>

            @php
                $totalStudents = $assignment->course
                    ->enrollments()
                    ->where("level", $assignment->level)
                    ->count();

                $submittedCount = $assignment
                    ->submissions()
                    ->whereNotNull("submitted_at")
                    ->count();

                $notSubmittedCount = max(0, $totalStudents - $submittedCount);

                $gradedCount = $assignment
                    ->submissions()
                    ->whereHas("assessment")
                    ->count();

                $submissionRate = $totalStudents > 0 ? round(($submittedCount / $totalStudents) * 100) : 0;
            @endphp

            <div
                class="mb-6 bg-white border border-gray-200 rounded-xl shadow-sm p-5"
            >
                {{-- Header Row --}}
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">
                            Level {{ $assignment->level }} – Submission Summary
                        </h3>
                        <p class="text-xs text-gray-500">
                            Overview of how many students submitted this
                            assignment.
                        </p>
                    </div>

                    {{-- Status Pill --}}
                    <span
                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $submissionRate == 100 ? "bg-green-50 text-green-700" : "bg-yellow-50 text-yellow-700" }}"
                    >
                        {{ $submissionRate }}% Submitted
                    </span>
                </div>

                {{-- Progress Bar --}}
                <div class="mb-5">
                    <div
                        class="flex items-center justify-between text-xs text-gray-500 mb-1"
                    >
                        <span>Submission Rate</span>
                        <span>
                            {{ $submittedCount }} / {{ $totalStudents }}
                        </span>
                    </div>
                    <div
                        class="h-2 w-full rounded-full bg-gray-100 overflow-hidden"
                    >
                        <div
                            class="h-full rounded-full bg-green-600 transition-all duration-300"
                            style="width: {{ $submissionRate }}%"
                        ></div>
                    </div>
                </div>

                {{-- Stats Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div
                        class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2"
                    >
                        <span class="text-gray-600">Total Students</span>
                        <span class="text-sm font-semibold text-gray-900">
                            {{ $totalStudents }}
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between rounded-lg bg-green-50 px-3 py-2"
                    >
                        <span class="text-gray-700">Submitted</span>
                        <span class="text-sm font-semibold text-green-800">
                            {{ $submittedCount }}
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between rounded-lg bg-rose-50 px-3 py-2"
                    >
                        <span class="text-gray-700">Not Submitted</span>
                        <span class="text-sm font-semibold text-rose-700">
                            {{ $notSubmittedCount }}
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between rounded-lg bg-blue-50 px-3 py-2"
                    >
                        <span class="text-gray-700">Graded</span>
                        <span class="text-sm font-semibold text-blue-700">
                            {{ $gradedCount }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        @if (session("success"))
            <div
                class="mb-4 bg-green-50 text-green-700 border border-green-200 px-4 py-2 rounded"
            >
                {{ session("success") }}
            </div>
        @endif

        {{-- Errors usually appear on the edit/grading page, not the list --}}

        {{-- ===================== SUBMISSIONS LIST ===================== --}}
        <div class="font-semibold text-gray-700 mb-4 text-lg">
            Student Submissions
        </div>

        <div class="mt-4 overflow-x-auto rounded-lg shadow-md">
            <table
                class="min-w-full text-sm bg-white shadow-sm text-center border border-gray-200"
            >
                <thead class="bg-gray-900 text-white">
                    <tr class="text-sm text-white">
                        <th class="px-3 py-3">Student Name</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3">Turn in Date</th>
                        <th class="px-3 py-3">Score</th>
                        {{-- Show score if already graded --}}
                        <th class="px-3 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    {{-- Use the submissions loaded onto the $assignment object --}}
                    @php
                        $subs = $assignment->submissions;
                    @endphp

                    @forelse ($subs as $s)
                        @php
                            $done = ! empty($s->submitted_at);
                            $assessment = $s->assessment; // Eager loaded
                        @endphp

                        <tr
                            onClick="window.location='{{ $done ? route("lecturer.submissions.assessments.edit", ["submission" => $s, "assessment" => $assessment]) : route("lecturer.submissions.assessments.create", ["submission" => $s]) }}'"
                            class="hover:bg-gray-50 cursor-pointer"
                        >
                            <td class="px-3 py-3 whitespace-nowrap">
                                {{ $s->student->user->name ?? "—" }}
                            </td>
                            <td class="px-3 py-3">
                                <span
                                    @class([
                                        "px-2 py-1 text-xs rounded",
                                        "bg-green-100 text-green-700" => $done,
                                        "bg-rose-100 text-rose-700" => ! $done,
                                    ])
                                >
                                    {{ $done ? "Submitted" : "Not Submitted" }}
                                </span>
                            </td>
                            <td class="px-3 py-3 whitespace-nowrap">
                                {{ optional($s->submitted_at)->format("d M Y, H:i") ?? "N/A" }}
                            </td>
                            <td class="px-3 py-3">
                                {{-- Display score if assessed --}}

                                @if ($assessment && isset($assessment->score))
                                    {{ $assessment->score }} / 10
                                @elseif ($done)
                                    <span class="text-gray-400">
                                        Not Graded
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex gap-3">
                                    @if ($done)
                                        @if ($assessment)
                                            {{-- If grade exists, go to the EDIT route --}}
                                            <a
                                                href="{{ route("lecturer.submissions.assessments.edit", ["submission" => $s, "assessment" => $assessment]) }}"
                                                class="inline-block px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700"
                                            >
                                                Edit Grade
                                            </a>
                                        @else
                                            {{-- If no grade, go to the CREATE route --}}
                                            <a
                                                href="{{ route("lecturer.submissions.assessments.create", ["submission" => $s]) }}"
                                                class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700"
                                            >
                                                Grade
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="#000000"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M9 18l6-6-6-6" />
                                    </svg>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                class="px-6 py-6 text-gray-500 text-center"
                                colspan="5"
                            >
                                No submissions found for this assignment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{-- Link back to the list of assignments for assessment --}}
            <a
                href="{{ route("lecturer.courses.assessments.index", $course) }}?level={{ $assignment->level }}"
                class="px-4 py-2 rounded border text-sm hover:bg-gray-100"
            >
                &larr; Back to Assessment List
            </a>
        </div>
    </section>
</x-layout>
E
