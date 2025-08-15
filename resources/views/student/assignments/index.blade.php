<x-layout title="Assignments">
<div class="max-w-6xl mx-auto px-4 py-6 space-y-6">
    {{-- Breadcrumbs --}}
    <nav class="text-sm text-gray-500">
        <a href="{{ route('student.dashboard') }}" class="hover:underline">Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('student.courses.show', $course) }}" class="hover:underline">{{ $course->title }}</a>
        <span class="mx-2">/</span>
        <span class="text-gray-700 font-medium">Assignments</span>
    </nav>

    {{-- Optional filter by level via ?level= --}}
    @if(request('level'))
        <div class="text-sm text-gray-600">Filtering level: <span class="font-medium">Level {{ (int) request('level') }}</span></div>
    @endif

    {{-- List --}}
    <div class="bg-white rounded-xl shadow">
        <ul class="divide-y divide-gray-100">
            @forelse($assignments as $a)
                @php
                    $submission = $a->submissions->first(); // for this student
                    $status = $submission
                        ? ($submission->graded_at ? 'Graded' : 'Submitted')
                        : ( $a->due_at && $a->due_at->isPast() ? 'Closed' : 'Open');
                @endphp
                <li class="p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-medium truncate">{{ $a->title }}</p>
                            <p class="text-sm text-gray-500">
                                @if($a->level) Level {{ $a->level }} • @endif
                                @if($a->due_at) Due {{ $a->due_at->format('M d, Y H:i') }} @else No due date @endif
                            </p>
                            @if($a->instructions)
                                <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $a->instructions }}</p>
                            @endif
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs
                                @class([
                                    'bg-green-100 text-green-700' => $status === 'Graded',
                                    'bg-blue-100 text-blue-700'  => $status === 'Submitted',
                                    'bg-gray-100 text-gray-700'  => $status === 'Closed',
                                    'bg-yellow-100 text-yellow-700' => $status === 'Open',
                                ])">
                                {{ $status }}
                            </span>

                            <div class="mt-2">
                                @if(!$submission && (!$a->due_at || $a->due_at->isFuture()))
                                    <a href="{{ route('student.assignments.submissions.create', $a) }}" class="text-sm text-blue-600 hover:underline">Submit</a>
                                @elseif($submission && !$submission->graded_at)
                                    <a href="{{ route('student.assignments.submissions.edit', [$a, $submission]) }}" class="text-sm text-blue-600 hover:underline">Edit submission</a>
                                @elseif($submission && $submission->graded_at)
                                    <a href="{{ route('student.assignments.submissions.show', [$a, $submission]) }}" class="text-sm text-blue-600 hover:underline">View feedback</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </li>
            @empty
                <li class="p-8 text-center text-gray-500 text-sm">No assignments yet.</li>
            @endforelse
        </ul>

        <div class="px-4 py-3 border-t">
            {{ $assignments->links() }}
        </div>
    </div>
</div>
</x-layout>
