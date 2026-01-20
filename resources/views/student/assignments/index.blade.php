{{-- resources/views/student/assignments/index.blade.php --}}
<x-layout title="Assignments">
    @php
        // These variables ($level, $week, $day) are now passed from the controller
        $levelLabel = $level ? "LEVEL " . $level : null;
        $week = (int) request("week");
        $day = request("day");
    @endphp

    {{-- Breadcrumbs --}}
    <nav
        class="hidden lg:flex mb-2 text-sm text-gray-600 p-3"
        aria-label="Breadcrumb"
    >
        <ol class="list-reset flex">
            <li>
                <a
                    href="{{ route("student.dashboard") }}"
                    class="hover:underline"
                >
                    Dashboard
                </a>
                <span class="mx-2">/</span>
            </li>
            <li class="text-black font-semibold">Assignments</li>
        </ol>
    </nav>

    <a
        href="{{ route("student.dashboard") }}"
        class="lg:hidden text-sm text-blue-600 hover:underline px-4 py-2 rounded border mb-4 inline-block"
    >
        &larr; Back to Dashboard
    </a>

    {{--
        Level Guidance/Info
        
        @if ($student_level && $level)
        <div
        class="rounded border bg-blue-50 text-blue-800 px-4 py-3 mb-4 text-sm"
        >
        You are enrolled at Level {{ $student_level }}. You can see
        assignments for Level {{ $student_level }} and below.
        </div>
        @else
        <div
        class="rounded border bg-yellow-50 text-yellow-800 px-4 py-3 mb-4 text-sm"
        >
        You don't seem to be enrolled in this course with a specific level.
        Showing all available assignments.
        </div>
        @endif
    --}}

    {{-- Header --}}
    @php
        $levelColors = [
            3 => "bg-[#9bd1f8]",
            2 => "bg-[#c7f7cf]",
            1 => "bg-[#f0c6bc]",
        ];
        // Use the student's *enrolled* level for header color, default gray
        $headerColor = $levelColors[$level ?? null] ?? "bg-gray-100";
    @endphp

    <section
        class="max-w-6xl mx-auto lg:p-6 rounded-lg lg:shadow lg:border border-gray-300"
    >
        <div
            class="flex items-center justify-between p-4 rounded-lg {{ $headerColor }}"
        >
            <div>
                <h1 class="text-xl font-semibold">
                    @if ($week && $day)
                        Assignments for: Week {{ $week }}, {{ $day }}
                    @elseif ($week)
                        Assignments for: Week {{ $week }}
                    @elseif ($level)
                        All Assignments for: Level {{ $level }}
                    @else
                            All Assignments
                    @endif
                </h1>
                <h1 class="text-xl font-thin">
                    {{ $level ? "Level $level" : "All Levels" }}
                </h1>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" class="mt-4 flex flex-wrap gap-3 items-end hidden">
            {{-- Level Filter --}}
            <div>
                <label class="block text-sm text-gray-600">Level</label>
                <select
                    name="level"
                    class="block border rounded py-2.5 px-2 text-xs w-full text-center"
                >
                    {{-- Show levels up to the student's enrolled level --}}
                    <option value="">All My Levels</option>
                    @if ($student_level !== null)
                        {{-- Check if student_level is not null --}}
                        @foreach (range(1, $student_level) as $lv)
                            <option
                                value="{{ $lv }}"
                                @selected($level == $lv)
                            >
                                Level {{ $lv }}
                            </option>
                        @endforeach
                    @else
                        {{-- If no student level, show all levels as fallback --}}
                        @foreach ([1, 2, 3] as $lv)
                            <option
                                value="{{ $lv }}"
                                @selected($level == $lv)
                            >
                                Level {{ $lv }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            {{-- Apply Level button --}}
            <button class="px-3 py-2 rounded bg-red-600 text-white">
                Apply Level
            </button>
        </form>

        {{-- Week/Day Navigation Grid --}}
        @php
            $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "REVIEW"];
        @endphp

        <div class="grid grid-row gap-4 mt-2">
            <aside class="w-72 mt-2 hidden">
                <table
                    class="w-full text-xs border border-gray-200 mr-2 shadow-sm"
                >
                    <tbody class="bg-white">
                        @for ($w = 1; $w <= 8; $w++)
                            <tr class="border-b border-gray-200">
                                <td
                                    class="flex flex-col-2 px-2 py-2 font-semibold"
                                >
                                    <div
                                        class="flex min-w-16 items-right border-r border-gray-200 mr-2"
                                    >
                                        <a
                                            class="font-bold text-blue-700 whitespace-nowrap px-2 py-1 rounded"
                                        >
                                            Week {{ $w }}:
                                        </a>
                                    </div>
                                    <div
                                        class="gap-x-1 gap-y-2 flex flex-wrap justify-start"
                                    >
                                        @foreach ($days as $dayName)
                                            {{-- This link preserves the $level filter --}}
                                            <a
                                                href="{{ request()->fullUrlWithQuery(["week" => $w, "day" => $dayName, "level" => $level]) }}"
                                                @class([
                                                    "font-semibold",
                                                    "bg-gray-100 px-1 py-1 rounded",
                                                    "bg-gray-900 text-white" => $week == $w && $day == $dayName,
                                                    "hover:bg-gray-900 hover:text-white" => ! ($week == $w && $day == $dayName),
                                                    "hover:underline" => ! ($week == $w && $day == $dayName),
                                                    "text-purple-600" => $dayName === "REVIEW",
                                                    "text-black" => $dayName !== "REVIEW",
                                                ])
                                            >
                                                {{ $dayName }}
                                            </a>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </aside>
            {{-- Week and Day filter --}}
            <form
                method="GET"
                action="{{ url()->current() }}"
                class="grid grid-cols-2 gap-4 max-w-xs"
            >
                {{-- Preserve Level if exists --}}
                <input type="hidden" name="level" value="{{ $level }}" />

                <div class="block">
                    <label
                        class="block text-xs font-bold text-gray-600 uppercase mb-1"
                    >
                        Select Week
                    </label>
                    <select
                        name="week"
                        onchange="this.form.submit()"
                        class="w-full border border-gray-300 rounded-md py-2 px-2 text-sm"
                    >
                        <option value="">All Weeks</option>
                        @for ($i = 1; $i <= 8; $i++)
                            <option
                                value="{{ $i }}"
                                @selected(request("week") == $i)
                            >
                                Week {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="block">
                    <label
                        class="block text-xs font-bold text-gray-600 uppercase mb-1"
                    >
                        Select Day
                    </label>
                    <select
                        name="day"
                        onchange="this.form.submit()"
                        class="w-full border border-gray-300 rounded-md py-2 px-2 text-sm"
                    >
                        <option value="">All Days</option>
                        @foreach ($days as $d)
                            <option
                                value="{{ $d }}"
                                @selected(request("day") == $d)
                            >
                                {{ $d }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            {{-- Assignments table (now filtered) --}}
            <main class="rounded-lg flex-1">
                {{-- Title --}}
                <div class="flex items-center justify-between mb-4">
                    @if ($week && $day or $week)
                        <div class="flex gap-2">
                            {{-- Link 1: View filtered list --}}
                            <a
                                href="{{ route("student.assignments.index", $course) }}?level={{ $level }}"
                                class="text-sm px-4 py-2 rounded bg-white border border-gray-300 text-sm font-medium hover:bg-gray-100 transition shadow-sm"
                            >
                                &larr; Back to all Assignments
                            </a>
                        </div>
                    @endif
                </div>
                <div
                    class="w-full bg-white border border-gray-200 rounded-xl shadow-sm p-4"
                >
                    @php
                        $totalAssignments = $assignments->total();
                        $submittedCount = $assignments
                            ->filter(function ($a) {
                                return $a->submissions->isNotEmpty();
                            })
                            ->count();

                        $gradedCount = $assignments
                            ->filter(function ($a) {
                                return $a->submissions->first() && $a->submissions->first()->assessment;
                            })
                            ->count();

                        $overdueCount = $assignments
                            ->filter(function ($a) {
                                return ! $a->submissions->isNotEmpty() && $a->due_at && $a->due_at->isPast();
                            })
                            ->count();

                        $completionRate = $totalAssignments > 0 ? round(($gradedCount / $totalAssignments) * 100) : 0;
                    @endphp

                    {{-- Overview Header --}}
                    <div class="flex items-center justify-between mb-4">
                        <div class="max-w-xs">
                            <h2 class="text-sm font-semibold text-gray-900">
                                Assignment Overview
                            </h2>
                            <p class="text-xs text-gray-500">
                                Quick summary of Assignments
                            </p>
                        </div>
                        <span
                            class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700"
                        >
                            {{ $completionRate }}% completed
                        </span>
                    </div>

                    {{-- Progress bar --}}
                    <div class="mb-4">
                        <div
                            class="flex items-center justify-between text-xs text-gray-500 mb-1"
                        >
                            <span>Completion</span>
                            <span>
                                {{ $gradedCount }} /
                                {{ $totalAssignments }}
                            </span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100">
                            <div
                                class="h-full rounded-full bg-green-500 transition-all"
                                style="width: {{ $completionRate }}%"
                            ></div>
                        </div>
                    </div>

                    {{-- Stats row --}}
                    <div
                        class="grid grid-cols-3 sm:grid-cols-3 gap-3 text-xs max-w-base"
                    >
                        <div
                            class="flex items-center justify-between rounded-lg bg-gray-50 px-2 py-2"
                        >
                            <span class="text-gray-600">Total Assignments</span>
                            <span class="text-sm font-semibold text-gray-900">
                                {{ $totalAssignments }}
                            </span>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-lg bg-green-50 px-2 py-2"
                        >
                            <span class="text-gray-700">Completed</span>
                            <span class="text-sm font-semibold text-green-800">
                                {{ $gradedCount }}
                            </span>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-lg bg-rose-50 px-2 py-2"
                        >
                            <span class="text-gray-700">Overdue</span>
                            <span class="text-sm font-semibold text-rose-700">
                                {{ $overdueCount }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Assignments Table --}}
                <div class="mt-4 overflow-hidden rounded-lg shadow-md">
                    <table class="lg:min-w-full text-sm bg-white shadow-sm">
                        <thead class="bg-gray-900 text-left">
                            <tr class="text-xs text-white">
                                <th class="px-4 py-3 text-left">Title</th>

                                {{-- Changed from Lecturer's 'Published' status --}}
                                <th class="px-4 py-3 text-left">Week & Day</th>
                                <th class="px-4 py-3 text-left">Due Date</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-1 py-3 text-left"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($assignments as $a)
                                @php
                                    // Get the student's submission for this assignment (already eager loaded)
                                    $submission = $a->submissions->first();
                                    // Use the eager loaded 'has_assessment' attribute
                                    $hasAssessment = $a->has_assessment;

                                    // Determine student-centric status
                                    $status = "Open"; // Default
                                    if ($submission) {
                                        $status = $hasAssessment ? "Graded" : "Submitted";
                                    } elseif ($a->due_at && $a->due_at->isPast()) {
                                        $status = "Closed";
                                    }

                                    // Determine if student can submit/edit
                                    $canSubmit = ! $submission && (! $a->due_at || $a->due_at->isFuture());
                                    // Can edit if submitted, not graded, and not past due (or no due date)
                                    $canEdit = $submission && ! $hasAssessment && (! $a->due_at || $a->due_at->isFuture());
                                @endphp

                                <tr
                                    class="hover:bg-gray-50 border-b border-gray-200 text-xs"
                                    onclick="
                                        window.location =
                                            '{{ route("student.assignments.show", $a) }}'
                                    "
                                >
                                    <td class="px-4 py-3 text-left">
                                        {{-- Wrap the title in a link --}}
                                        <a
                                            href="{{ route("student.assignments.show", $a) }}"
                                            class="font-medium text-blue-600 hover:underline"
                                        >
                                            {{ $a->title }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-left">
                                        @if ($a->week && $a->day)
                                            Week {{ $a->week ?? "—" }} -
                                            {{ $a->day ?? "—" }}
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td
                                        class="px-2 py-3 text-left text-red-500 whitespace-wrap"
                                    >
                                        @if ($a->due_at)
                                            {{ $a->due_at->format("d M Y - H:i") }}
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-left">
                                        {{-- Student Status Span --}}
                                        <span
                                            @class([
                                                "px-2 py-1 rounded",
                                                "bg-green-100 text-green-700" => $status === "Graded",
                                                "bg-blue-100 text-blue-700" => $status === "Submitted",
                                                "bg-red-100 text-red-700" => $status === "Closed",
                                                "bg-yellow-100 text-yellow-700" => $status === "Open",
                                            ])
                                        >
                                            {{ $status }}
                                        </span>
                                    </td>
                                    <td class="px-1 py-3 text-xl text-left">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="18"
                                            height="18"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="#000000"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path d="M9 18l6-6-6-6" />
                                        </svg>
                                        {{--
                                            <div class="flex flex-col items-start gap-1">
                                            
                                            @if ($canSubmit)
                                            <a
                                            href="{{ route("student.assignments.submissions.create", $a) }}"
                                            class="text-blue-600 hover:underline text-xs whitespace-nowrap"
                                            >
                                            Submit Now
                                            </a>
                                            @elseif ($canEdit)
                                            <a
                                            href="{{ route("student.assignments.submissions.edit", [$a, $submission]) }}"
                                            class="text-blue-600 hover:underline text-xs whitespace-nowrap"
                                            >
                                            Edit Submission
                                            </a>
                                            @elseif ($status === "Graded")
                                            <a
                                            href="{{ route("student.assignments.submissions.show", [$a, $submission]) }}"
                                            class="text-green-600 hover:underline text-xs whitespace-nowrap"
                                            >
                                            View Feedback
                                            </a>
                                            @elseif ($status === "Submitted")
                                            <span
                                            class="text-gray-500 text-xs whitespace-nowrap"
                                            >
                                            Awaiting Grade
                                            </span>
                                            @elseif ($status === "Closed")
                                            <span
                                            class="text-red-500 text-xs whitespace-nowrap"
                                            >
                                            Past Due
                                            </span>
                                            @else
                                            <span class="text-gray-400 text-xs">
                                            —
                                            </span>
                                            @endif
                                            @if ($a->file_path)
                                            <a
                                            href="{{ route("student.assignments.download", $a) }}"
                                            class="text-gray-500 hover:underline text-xs whitespace-nowrap mt-1"
                                            >
                                            (Download Task File)
                                            </a>
                                            @endif
                                            </div>
                                        --}}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        class="px-6 py-6 text-gray-500 text-center"
                                        colspan="7"
                                    >
                                        No upload links found matching your
                                        filters and level.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </main>

            {{-- Paginator --}}
            @if ($assignments->hasPages())
                {{-- Check directly on paginator instance --}}
                <div class="mt-4 px-6 py-3">{{ $assignments->links() }}</div>
            @endif
        </div>
    </section>
</x-layout>
