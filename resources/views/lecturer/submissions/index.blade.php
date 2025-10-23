{{-- resources/views/lecturer/submissions/index.blade.php --}}
<x-layout>
    @php
        // $assignment is passed to this view by LecturerSubmissionsController@index
        $course = $assignment->course;
    @endphp

    <nav class="mb-6 text-sm text-gray-600" aria-label="Breadcrumb">
        <ol class="list-reset flex">
            <li><a href="{{ route('lecturer.dashboard') }}" class="hover:underline">Dashboard</a><span class="mx-2">/</span></li>
            {{-- Link back to the list of assignments for assessment --}}
            <li><a href="{{ route('lecturer.courses.assessments.index', $course) }}?level={{$assignment->level}}" class="hover:underline">Assess Student Uploads</a><span class="mx-2">/</span></li>
            <li class="text-black font-semibold">{{ $assignment->title }} - Submissions</li>
        </ol>
    </nav>

    <div class="mb-6 p-4 rounded-lg bg-blue-100 border border-blue-200">
        <h1 class="text-2xl font-semibold text-blue-800">{{ $assignment->title }}</h1>
        <p class="text-blue-700">Level {{ $assignment->level ?? 'N/A' }}
            @if($assignment->week && $assignment->day) | Week {{ $assignment->week }}, {{ $assignment->day }} @endif
            @if($assignment->due_at) | Due: {{ $assignment->due_at->format('d M Y') }} @endif
        </p>
        <a href="{{ route('lecturer.assignments.show', $assignment) }}" class="text-xs text-blue-600 hover:underline mt-1 inline-block">View Assignment Details</a>
    </div>

    @if(session('success')) <div class="mb-4 bg-green-50 text-green-700 border border-green-200 px-4 py-2 rounded">{{ session('success') }}</div> @endif
    {{-- Errors usually appear on the edit/grading page, not the list --}}

    {{-- ===================== SUBMISSIONS LIST ===================== --}}
    <div class="bg-white rounded-lg shadow border overflow-hidden">
        <div class="bg-gray-50 px-6 py-3 font-semibold text-gray-700 border-b">
            Student Submissions
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-100 border-b">
                    <tr class="text-sm text-gray-600">
                        <th class="px-6 py-3">Student Name</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Turn in Date</th>
                        <th class="px-6 py-3">Score</th> {{-- Show score if already graded --}}
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    {{-- Use the submissions loaded onto the $assignment object --}}
                    @php $subs = $assignment->submissions; @endphp
                    @forelse($subs as $s)
                        @php
                            $done = !empty($s->submitted_at);
                            $assessment = $s->assessment; // Eager loaded
                        @endphp
                        <tr>
                            <td class="px-6 py-4 align-top whitespace-nowrap">{{ $s->student->user->name ?? '—' }}</td>
                            <td class="px-6 py-4 align-top">
                                <span @class([
                                    'px-2 py-1 text-xs rounded',
                                    'bg-green-100 text-green-700' => $done,
                                    'bg-rose-100 text-rose-700' => !$done,
                                ])>{{ $done ? 'Submitted' : 'Not Submitted' }}</span>
                            </td>
                            <td class="px-6 py-4 align-top whitespace-nowrap">{{ optional($s->submitted_at)->format('d M Y, H:i') ?? 'N/A' }}</td>
                            <td class="px-6 py-4 align-top">
                                {{-- Display score if assessed --}}
                                @if($assessment && isset($assessment->score))
                                    {{ $assessment->score }} / 10
                                @elseif($done)
                                    <span class="text-gray-400">Not Graded</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 align-top">
                                <div class="flex items-center gap-3 flex-wrap">
                                    @if($done)
                                        {{-- Link to the Assessment Edit page --}}
                                        {{-- Pass assessment ID = 0 if creating, else actual ID --}}
                                            @if($assessment)
                                                {{-- If grade exists, go to the EDIT route --}}
                                                <a href="{{ route('lecturer.submissions.assessments.edit', ['submission' => $s, 'assessment' => $assessment]) }}"
                                                class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 whitespace-nowrap">
                                                    View/Edit Grade
                                                </a>
                                            @else
                                                {{-- If no grade, go to the CREATE route --}}
                                                <a href="{{ route('lecturer.submissions.assessments.create', ['submission' => $s]) }}"
                                                class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 whitespace-nowrap">
                                                    Grade Now
                                                </a>
                                            @endif
                                        @if($s->file_path)
                                            <a href="{{ route('lecturer.submissions.download', $s) }}" {{-- Use correct route name --}}
                                               class="text-blue-700 underline text-xs whitespace-nowrap">
                                                Download Submission
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-6 py-6 text-gray-500 text-center" colspan="5">No submissions found for this assignment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-6">
        {{-- Link back to the list of assignments for assessment --}}
        <a href="{{ route('lecturer.courses.assessments.index', $course) }}?level={{$assignment->level}}" class="text-blue-600 hover:underline">&larr; Back to Assessment List</a>
    </div>
</x-layout>E