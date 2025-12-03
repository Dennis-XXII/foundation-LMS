{{-- resources/views/lecturer/students/progress.blade.php --}}
<x-layout>
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
            <li class="text-black font-semibold">
                Student Progress: {{ $course->code }}
            </li>
        </ol>
    </nav>

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">
            Student Progress & Analysis — {{ $course->code }}
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
                    <tr class="border-t">
                        <td class="px-4 py-3 align-top">
                            <p class="font-medium">
                                {{ $e->student->user->name ?? "N/A" }}
                            </p>
                            <p class="text-xs text-gray-500">
                                ID: {{ $e->student->student_id ?? "—" }}
                            </p>
                            <p class="text-xs text-blue-600">
                                Level: {{ $e->level ?? "—" }}
                            </p>
                        </td>
                        <td class="px-4 py-3 align-top">
                            <div
                                class="font-bold text-lg text-{{ $e->assignment_stats["completion_percentage"] < 50 ? "red" : "green" }}-600"
                            >
                                {{ $e->assignment_stats["completion_percentage"] }}%
                            </div>
                            <div
                                class="w-full bg-gray-200 rounded-full h-2 mt-1 max-w-40"
                            >
                                <div
                                    class="h-2 rounded-full bg-{{ $e->assignment_stats["completion_percentage"] < 50 ? "red" : "green" }}-600"
                                    style="
                                        width: {{ $e->assignment_stats["completion_percentage"] }}%;
                                    "
                                ></div>
                            </div>
                        </td>
                        <td class="px-4 py-3 align-top text-xs">
                            Assignments Visible for Level
                            {{ $e->level ?? "—" }}:
                            <strong class="text-gray-700">
                                {{ $e->assignment_stats["visible_count"] }}
                            </strong>
                        </td>
                        <td class="px-4 py-3 align-top text-xs">
                            <p class="text-gray-700">
                                Submitted:
                                <strong class="text-blue-600">
                                    {{ $e->assignment_stats["submitted_count"] }}
                                </strong>
                            </p>
                            <p class="text-gray-700 mt-1">
                                Graded:
                                <strong class="text-green-600">
                                    {{ $e->assignment_stats["graded_count"] }}
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
