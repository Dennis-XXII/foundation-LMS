<x-layout>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Edit Course</h1>
        <a
            href="{{ route("admin.courses.index") }}"
            class="text-blue-600 hover:underline"
        >
            Back
        </a>
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

    {{-- Update basic info --}}
    <form
        method="POST"
        action="{{ route("admin.courses.update", $course) }}"
        class="bg-white border rounded-lg shadow p-6 space-y-4"
    >
        @csrf
        @method("PUT")
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Code</label>
                <input
                    name="code"
                    value="{{ old("code", $course->code) }}"
                    class="w-full border rounded px-3 py-2"
                    required
                />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input
                    name="name"
                    value="{{ old("name", $course->name) }}"
                    class="w-full border rounded px-3 py-2"
                    required
                />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">
                    Year / Program
                </label>
                <input
                    name="level"
                    value="{{ old("level", $course->level) }}"
                    class="w-full border rounded px-3 py-2"
                    placeholder="e.g. 2025 / Foundation"
                />
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea
                name="description"
                rows="3"
                class="w-full border rounded px-3 py-2"
            >
{{ old("description", $course->description) }}</textarea
            >
        </div>
        <div class="flex items-center gap-3">
            <button class="px-4 py-2 bg-blue-600 text-white rounded">
                Save Changes
            </button>
            <a
                class="px-4 py-2 bg-gray-100 border rounded"
                href="{{ route("admin.students.index", $course) }}"
            >
                Manage Students
            </a>
        </div>
    </form>

    {{-- Dangerous tools per your UI note (remove all coursework for reupload) --}}
    <div class="mt-6 bg-white border rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-3">Danger Zone</h2>
        <p class="text-sm text-gray-600 mb-4">
            Delete all materials and assignments for this course.
        </p>
        <form
            method="POST"
            action="{{ route("admin.courses.destroy", $course) }}"
            onsubmit="
                return confirm(
                    'Delete this course and all related coursework? This cannot be undone.',
                );
            "
        >
            @csrf
            @method("DELETE")
            <button class="px-4 py-2 bg-rose-600 text-white rounded">
                Delete Course & Coursework
            </button>
        </form>
    </div>
</x-layout>
