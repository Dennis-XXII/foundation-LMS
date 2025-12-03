{{-- resources/views/lecturer/assessments/index.blade.php --}}
<x-layout>
    @php
        $level = (int) request("level");
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
            class="px-6 py-2.5 rounded-lg shadow bg-rose-200 text-rose-800 hover:bg-rose-300"
        >
            Post Upload Links
        </a>
        <a
            href="{{ route("lecturer.courses.assessments.index", $course) }}?level={{ $level }}"
            class="px-6 py-2.5 rounded-lg shadow bg-blue-600 text-white"
        >
            Assess Student Uploads
        </a>
    </div>

    <!-- Level Filter -->
    <form
        method="GET"
        class="mt-4 mb-6 flex flex-wrap gap-3 items-end justify-center"
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
    <div class="bg-white rounded-lg shadow border overflow-hidden">
        <div class="bg-blue-100 px-6 py-3 font-semibold text-blue-800">
            Select an Assignment to Assess Submissions
        </div>
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50">
                <tr class="text-gray-600">
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
