{{-- resources/views/student/special_projects/index.blade.php --}}
<x-layout title="Special Projects">
    @php
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
            <li class="text-black font-semibold">Special Projects</li>
        </ol>
    </nav>

    <a
        href="{{ route("student.dashboard") }}"
        class="lg:hidden text-sm text-blue-600 hover:underline px-4 py-2 rounded border mb-4 inline-block"
    >
        &larr; Back to Dashboard
    </a>

    {{-- Header --}}
    @php
        $levelColors = [
            3 => "bg-[#9bd1f8]",
            2 => "bg-[#c7f7cf]",
            1 => "bg-[#f0c6bc]",
        ];
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
                        Projects for: Week {{ $week }}, {{ $day }}
                    @elseif ($week)
                        Projects for: Week {{ $week }}
                    @elseif ($level)
                        All Projects for: Level {{ $level }}
                    @else
                        All Projects
                    @endif
                </h1>
                <h1 class="text-xl font-thin">
                    {{ $level ? "Level $level" : "All Levels" }}
                </h1>
            </div>
        </div>

        {{-- Week/Day Navigation Grid --}}
        @php
            $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "REVIEW"];
        @endphp

        <div class="grid grid-row gap-4 mt-2">
            <form
                method="GET"
                action="{{ url()->current() }}"
                class="grid grid-cols-2 gap-4 max-w-xs"
            >
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

            {{-- Special Projects table (now filtered) --}}
            <main class="rounded-lg flex-1">
                <div class="flex items-center justify-between mb-4">
                    @if ($week && $day or $week)
                        <div class="flex gap-2">
                            <a
                                href="{{ route("student.special_projects.index", $course) }}?level={{ $level }}"
                                class="text-sm px-4 py-2 rounded bg-white border border-gray-300 text-sm font-medium hover:bg-gray-100 transition shadow-sm"
                            >
                                &larr; Back to all Special Projects
                            </a>
                        </div>
                    @endif
                </div>
                <div
                    class="w-full bg-white border border-gray-200 rounded-xl shadow-sm p-4"
                >
                    @php
                        $totalSpecialProjects = $specialProjects->total();
                        $submittedCount = $specialProjects
                            ->filter(function ($a) {
                                return $a->submissions->isNotEmpty();
                            })
                            ->count();

                        $gradedCount = $specialProjects
                            ->filter(function ($a) {
                                return $a->submissions->first() && $a->submissions->first()->assessment;
                            })
                            ->count();

                        $overdueCount = $specialProjects
                            ->filter(function ($a) {
                                return ! $a->submissions->isNotEmpty() && $a->due_at && $a->due_at->isPast();
                            })
                            ->count();

                        $completionRate = $totalSpecialProjects > 0 ? round(($gradedCount / $totalSpecialProjects) * 100) : 0;
                    @endphp

                    {{-- Overview Header --}}
                    <div class="flex items-center justify-between mb-4">
                        <div class="max-w-xs">
                            <h2 class="text-sm font-semibold text-gray-900">
                                Special Projects Overview
                            </h2>
                            <p class="text-xs text-gray-500">
                                Quick summary of Special Projects
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
                            <span>Completed Special Projects</span>
                            <span>
                                {{ $gradedCount }} /
                                {{ $totalSpecialProjects }}
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
                            <span class="text-gray-600">Total Special Projects</span>
                            <span class="text-sm font-semibold text-gray-900">
                                {{ $totalSpecialProjects }}
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

                {{-- Special Projects Table --}}
                <div class="mt-4 overflow-hidden rounded-lg shadow-md">
                    <table class="lg:min-w-full text-sm bg-white shadow-sm">
                        <thead class="bg-gray-900 text-left">
                            <tr class="text-xs text-white">
                                <th class="px-4 py-3 text-left">Title</th>
                                <th class="px-4 py-3 text-left">Week & Day</th>
                                <th class="px-4 py-3 text-left">Due Date</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-1 py-3 text-left"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($specialProjects as $a)
                                @php
                                    $submission = $a->submissions->first();
                                    $hasAssessment = $a->has_assessment;

                                    $status = "Open";
                                    if ($submission) {
                                        $status = $hasAssessment ? "Graded" : "Submitted";
                                    } elseif ($a->due_at && $a->due_at->isPast()) {
                                        $status = "Closed";
                                    }

                                    $canSubmit = ! $submission && (! $a->due_at || $a->due_at->isFuture());
                                    $canEdit = $submission && ! $hasAssessment && (! $a->due_at || $a->due_at->isFuture());
                                @endphp

                                <tr
                                    class="hover:bg-gray-50 border-b border-gray-200 text-xs"
                                    onclick="
                                        window.location =
                                            '{{ route("student.special_projects.show", $a) }}'
                                    "
                                >
                                    <td class="px-4 py-3 text-left">
                                        <a
                                            href="{{ route("student.special_projects.show", $a) }}"
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
                                    <td class="px-4 py-3 text-left" onClick="event.stopPropagation();">
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
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        class="px-6 py-6 text-gray-500 text-center"
                                        colspan="7"
                                    >
                                        No special projects found matching your filters and level.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </main>

            {{-- Paginator --}}
            @if ($specialProjects->hasPages())
                <div class="mt-4 px-6 py-3">{{ $specialProjects->links() }}</div>
            @endif
        </div>
    </section>
</x-layout>
