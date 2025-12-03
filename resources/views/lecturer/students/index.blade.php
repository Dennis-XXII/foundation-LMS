{{-- resources/views/lecturer/students/index.blade.php --}}
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
                Manage Students for {{ $course->code }}
            </li>
        </ol>
    </nav>

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">
            Manage Students — {{ $course->code }} {{ $course->name }}
        </h1>
    </div>

    {{-- Flash Messages --}}
    @if (session("success"))
        <div
            class="mb-4 bg-green-50 text-green-800 border border-green-200 px-4 py-2 rounded"
        >
            {{ session("success") }}
        </div>
    @endif

    @if (session("info"))
        <div
            class="mb-4 bg-blue-50 text-blue-800 border border-blue-200 px-4 py-2 rounded"
        >
            {{ session("info") }}
        </div>
    @endif

    @if ($errors->any())
        <div
            class="mb-4 bg-rose-50 text-rose-800 border border-rose-200 px-4 py-2 rounded"
        >
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Enroll form --}}
    <form
        method="POST"
        action="{{ route("lecturer.courses.students.store", $course) }}"
        class="bg-white border rounded p-4 mb-6 shadow-sm"
    >
        @csrf
        <h2 class="text-lg font-semibold mb-3">Add Student to Course</h2>
        <div class="grid md:grid-cols-4 gap-4 items-end">
            <div class="md:col-span-2">
                <label for="student_id" class="block text-sm font-medium mb-1">
                    Student ID (e.g., 66xxxxxx)
                </label>
                <input
                    type="text"
                    id="student_id"
                    name="student_id"
                    class="w-full border rounded px-3 py-2"
                    placeholder="Enter Student ID"
                    required
                />
            </div>
            <div>
                <label for="level" class="block text-sm font-medium mb-1">
                    Level (optional)
                </label>
                <select
                    id="level"
                    name="level"
                    class="w-full border rounded px-3 py-2"
                >
                    <option value="">— Select Level —</option>
                    @foreach ([1, 2, 3] as $lv)
                        <option value="{{ $lv }}">Level {{ $lv }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button
                    class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                    Enroll Student
                </button>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-2">
            The student must already have an account in the system.
        </p>
    </form>

    {{-- Enrolled list --}}
    <div class="bg-white border rounded overflow-x-auto shadow-md">
        <div class="bg-gray-50 px-6 py-3 font-semibold text-gray-700 border-b">
            Enrolled Students ({{ $enrollments->count() }} total)
        </div>
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">Student Name</th>
                    <th class="px-4 py-3 text-left">Student ID</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Level</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($enrollments as $e)
                    <tr class="border-t">
                        <td class="px-4 py-3">
                            {{ $e->student->user->name ?? "N/A" }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $e->student->student_id ?? "—" }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $e->student->user->email ?? "N/A" }}
                        </td>
                        <td class="px-4 py-3">{{ $e->level ?? "—" }}</td>
                        <td class="px-4 py-3">{{ ucfirst($e->status) }}</td>
                        <td class="px-4 py-3 text-right">
                            <form
                                method="POST"
                                action="{{ route("lecturer.courses.students.destroy", [$course, $e]) }}"
                                onsubmit="
                                    return confirm(
                                        'Remove {{ $e->student->user->name ?? "this student" }} from {{ $course->code }}?',
                                    );
                                "
                            >
                                @csrf
                                @method("DELETE")
                                <button
                                    class="px-3 py-1.5 bg-rose-600 text-white text-xs rounded hover:bg-rose-700"
                                >
                                    Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr class="border-t">
                        <td
                            colspan="6"
                            class="px-4 py-6 text-gray-500 text-center"
                        >
                            No students are currently enrolled in this course.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layout>
