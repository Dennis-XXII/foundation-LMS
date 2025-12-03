<x-layout>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">
            Manage Enrollments — {{ $course->code }} {{ $course->name }}
        </h1>
        <div class="space-x-2">
            <a
                href="{{ route("admin.courses.edit", $course) }}"
                class="px-3 py-2 bg-gray-100 border rounded"
            >
                Edit Course
            </a>
            <a
                href="{{ route("admin.students.index") }}"
                class="px-3 py-2 bg-purple-900 text-white rounded"
            >
                Manage Students
            </a>
        </div>
    </div>

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
        action="{{ route("admin.courses.enrollments.store", $course) }}"
        class="bg-white border rounded p-4 mb-6"
    >
        @csrf
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">
                    Select student
                </label>
                <select
                    name="student_id"
                    class="w-full border rounded px-3 py-2"
                    required
                >
                    <option value="">— choose —</option>
                    @foreach ($allStudents as $s)
                        <option value="{{ $s->id }}">
                            {{ $s->student_id }} — {{ $s->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">
                    Level (optional)
                </label>
                <input
                    type="number"
                    name="level"
                    class="w-full border rounded px-3 py-2"
                    min="1"
                    value=""
                />
            </div>
            <div class="flex items-end">
                <button class="px-4 py-2 bg-blue-600 text-white rounded">
                    Enroll
                </button>
            </div>
        </div>
    </form>

    {{-- Enrolled list --}}
    <div class="bg-white border rounded overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Student ID</th>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Level</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($enrollments as $e)
                    <tr class="border-t">
                        <td class="px-4 py-2">
                            {{ $e->student->student_id }}
                        </td>
                        <td class="px-4 py-2">
                            {{ $e->student->user->name }}
                        </td>
                        <td class="px-4 py-2">{{ $e->level ?? "—" }}</td>
                        <td class="px-4 py-2">{{ ucfirst($e->status) }}</td>
                        <td class="px-4 py-2 text-right">
                            <form
                                method="POST"
                                action="{{ route("admin.enrollments.destroy", $e) }}"
                                onsubmit="
                                    return confirm('Remove this enrollment?');
                                "
                            >
                                @csrf
                                @method("DELETE")
                                <button
                                    class="px-3 py-1.5 bg-rose-600 text-white text-xs rounded"
                                >
                                    Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr class="border-t">
                        <td colspan="5" class="px-4 py-6 text-gray-500">
                            No enrollments yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layout>
