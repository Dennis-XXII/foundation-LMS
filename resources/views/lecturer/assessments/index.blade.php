{{-- resources/views/lecturer/assessments/index.blade.php --}}
<x-layout>
    @php
        $level = (int) request("level");

        $levelColors = [
                3 => "bg-[#9bd1f8]",
                2 => "bg-[#c7f7cf]",
                1 => "bg-[#f0c6bc]",
            ];
            // Use level filter for header, default to gray
            $headerColor = $levelColors[$level ?? null] ?? "bg-gray-200";
    @endphp

    <!-- Breadcrumbs -->
    <nav class="mb-6 text-sm text-gray-600" aria-label="Breadcrumb">
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
            <li class="text-black font-semibold">Assess Student Uploads</li>
        </ol>
    </nav>

    <!-- Top Tabs -->
    <div class="flex items-center justify-center gap-6 mb-8">
        <a
            href="{{ route("lecturer.courses.assignments.index", $course) }}?level={{ $level }}"
            class="px-6 py-2.5 rounded-full shadow-sm bg-gray-200 hover:bg-gray-300"
        >
            Post Assignments
        </a>
        <a
            href="{{ route("lecturer.courses.assessments.index", $course) }}?level={{ $level }}"
            class="px-6 py-2.5 rounded-full shadow-sm shadow {{ $headerColor }}"
        >
            Assess Student Uploads
        </a>
    </div>

    <!-- Level Filter -->
    <form
        method="GET"
        class="mt-4 mb-6 flex flex-wrap gap-3 items-end justify-start"
    >
        <div>
            <label for="level_filter" class="block text-sm text-gray-600">
                Filter by Level
            </label>
            <select
                id="level_filter"
                name="level"
                class="block border rounded py-2.5 px-2 text-xs w-full text-center"
            >
                <option value="">All Levels</option>
                @foreach ([1, 2, 3] as $lv)
                    <option value="{{ $lv }}" @selected($level == $lv)>
                        Level {{ $lv }}
                    </option>
                @endforeach
            </select>
        </div>
        <button
            class="px-3 py-2 rounded bg-red-600 text-white hover:bg-red-700"
        >
            Apply Level Filter
        </button>
        @if ($level)
            <a
                href="{{ route("lecturer.courses.assessments.index", $course) }}"
                class="text-sm text-blue-600 hover:underline"
            >
                Clear Level Filter
            </a>
        @endif
    </form>

    <!-- Flash Messages -->
    @if (session("success"))
        <div
            class="mb-4 bg-green-50 text-green-800 border border-green-200 px-4 py-2 rounded"
        >
            {{ session("success") }}
        </div>
    @endif

    @if ($errors->any())
        <div
            class="mb-4 bg-rose-50 text-rose-800 border border-rose-200 px-4 py-2 rounded"
        >
            ... Errors ...
        </div>
    @endif

    {{-- ===================== ASSIGNMENTS LIST FOR ASSESSMENT ===================== --}}
    <div class="mb-4 text-lg font-semibold">
            Select an Assignment to Assess Submissions
        </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-900 text-white">
                <tr class="border-b border-gray-700">
                    <th class="px-6 py-3">Assignment Title</th>
                    <th class="px-6 py-3">Level</th>
                    <th class="px-6 py-3">Week</th>
                    <th class="px-6 py-3">Day</th>
                    <th class="px-6 py-3">Due Date</th>
                    <th class="px-6 py-3">Submissions</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($assignments as $assignment)
                    <tr>
                        <td class="px-6 py-3">
                            {{-- Link to the submissions page for this assignment --}}
                            <a
                                href="{{ route("lecturer.assignments.submissions.index", $assignment) }}"
                                class="font-medium text-blue-600 hover:underline"
                            >
                                {{ $assignment->title }}
                            </a>
                        </td>
                        <td class="px-6 py-3">
                            {{ $assignment->level ?? "N/A" }}
                        </td>
                        <td class="px-6 py-3">
                            {{ $assignment->week ?? "—" }}
                        </td>
                        <td class="px-6 py-3">
                            {{ $assignment->day ?? "—" }}
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            {{ optional($assignment->due_at)->format("d M Y") ?? "N/A" }}
                        </td>
                        <td class="px-6 py-3">
                            {{ $assignment->submissions_count }}
                        </td>
                        {{-- Display submission count --}}
                        <td class="px-6 py-3">
                            {{-- Link to the submissions page for this assignment --}}
                            <a
                                href="{{ route("lecturer.assignments.submissions.index", $assignment) }}"
                                class="text-blue-600 hover:underline"
                            >
                                View Submissions
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td
                            class="px-6 py-6 text-gray-500 text-center"
                            colspan="7"
                        >
                            No assignments
                            found{{ $level ? " for Level " . $level : "" }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{-- Pagination for Assignments List --}}
        @if ($assignments instanceof \Illuminate\Pagination\LengthAwarePaginator && $assignments->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $assignments->links() }}
            </div>
        @endif
    </div>
</x-layout>
