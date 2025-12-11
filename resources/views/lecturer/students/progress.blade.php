{{-- resources/views/lecturer/students/progress.blade.php --}}
<x-layout>
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
            <li class="text-black font-semibold">
                Student Progress: {{ $course->code }}
            </li>
        </ol>
    </nav>

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">
            Student Progress Table — {{ $course->code }}
            {{ $course->name }}
        </h1>
        <div class="flex gap-2">
            {{-- Link back to the operations page --}}
            <a
                href="{{ route("lecturer.courses.students.index", $course) }}"
                class="px-4 py-2 bg-purple-900 text-white rounded text-sm hover:bg-purple-800"
            >
                Manage Enrollments
            </a>
            <a
                href="{{ route("lecturer.dashboard") }}"
                class="px-4 py-2 border rounded text-sm hover:bg-gray-50"
            >
                &larr; Back to Dashboard
            </a>
        </div>
    </div>

    {{-- Analysis Table --}}
    <div class="bg-white border rounded overflow-x-auto shadow-md">
        <div class="bg-gray-50 px-6 py-3 font-semibold text-gray-700 border-b">
            Assignment Completion Status ({{ $enrollments->count() }} Students)
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">Student Profile & Level</th>
                    <th class="px-4 py-3 text-left">Completion Percentage</th>
                    <th class="px-4 py-3 text-left">Assignments (Visible)</th>
                    <th class="px-4 py-3 text-left">Submissions Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($enrollments as $e)
                    @php
                        // Safely extract stats
                        $stats = $e->assignment_stats ?? [];
                        $pct = $stats["completion_percentage"] ?? 0;

                        // Determine colors (fixed to ensure Tailwind compiles them)
                        $textColor = $pct < 50 ? "text-red-600" : "text-green-600";
                        $barColor = $pct < 50 ? "bg-red-600" : "bg-green-600";
                    @endphp

                    <tr class="border-t">
                        <td class="px-4 py-3 align-top">
                            <p class="font-medium">
                                {{-- LINK TO STUDENT SHOW VIEW --}}
                                <a
                                    href="{{ route("lecturer.courses.students.show", [$course, $e->student->user_id]) }}"
                                    class="text-base text-purple-900 hover:text-purple-700 hover:underline"
                                >
                                    {{ $e->student->user->name ?? "N/A" }}
                                </a>
                                <span class="text-xs text-gray-500">
                                    {{ $e->student->student_id ?? "—" }}
                                </span>
                            </p>
                            <p class="text-xs text-blue-600">
                                Level: {{ $e->level ?? "—" }}
                            </p>
                        </td>
                        <td class="px-4 py-3 align-top">
                            <div class="font-bold text-md {{ $textColor }}">
                                {{ $pct }}%
                            </div>
                            <div
                                class="w-full bg-gray-200 rounded-full h-1 mt-1 max-w-30"
                            >
                                <div
                                    class="h-1 rounded-full {{ $barColor }}"
                                    style="width: {{ $pct }}%"
                                ></div>
                            </div>
                        </td>
                        <td class="px-4 py-3 align-top text-xs">
                            Assignments Visible for Level
                            {{ $e->level ?? "—" }}:
                            <strong class="text-gray-700">
                                {{ $stats["visible_count"] ?? 0 }}
                            </strong>
                        </td>
                        <td class="px-4 py-3 align-top text-xs">
                            <p class="text-gray-700">
                                Submitted:
                                <strong class="text-blue-600">
                                    {{ $stats["submitted_count"] ?? 0 }}
                                </strong>
                            </p>
                            <p class="text-gray-700 mt-1">
                                Graded:
                                <strong class="text-green-600">
                                    {{ $stats["graded_count"] ?? 0 }}
                                </strong>
                            </p>
                        </td>
                    </tr>
                @empty
                    <tr class="border-t">
                        <td
                            colspan="4"
                            class="px-4 py-6 text-gray-500 text-center"
                        >
                            No students are currently enrolled in this course to
                            analyze.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layout>
