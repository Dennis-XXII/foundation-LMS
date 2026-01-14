<x-layout>
    <nav>
        <ol
            class="list-reset flex text-sm text-gray-600 mb-4"
            aria-label="Breadcrumb"
        >
            <li>
                <a
                    href="{{ route("admin.dashboard") }}"
                    class="hover:underline"
                >
                    Dashboard
                </a>
                <span class="mx-2">/</span>
            </li>
            <li>
                <a href="{{ route("admin.courses.index") }}" 
                class="hover:underline">Courses</a>
                <span class="mx-2">/</span>
            </li>
            <li class="text-black font-semibold">New Course</li>
        </ol>
    </nav>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">New Course</h1>
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

    {{-- Create form --}}
    <form 
    method="POST""
        action="{{ route("admin.courses.store") }}"
        class="max-w-6xl bg-white border rounded-lg shadow p-6 space-y-4"
    >
        @csrf
        @method("POST")
        <div class="grid md:grid-cols-2 gap-4">
            <div> 
                <label class="block text-sm font-medium mb-1">Code</label>
                <input
                    name="code"
                    value="{{ old("code") }}"
                    class="w-full border rounded px-3 py-2"
                    placeholder="e.g., ENG101"
                    required
                />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input
                    name="name"
                    value="{{ old("name") }}"
                    class="w-full border rounded px-3 py-2"
                    placeholder="e.g., Introduction to English"
                    required
                />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1"> Year / Program </label>
                <input
                    name="level"
                    value="{{ old("level") }}"
                    class="w-full border rounded px-3 py-2"
                    placeholder="e.g. 2025 / Foundation"
                />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">&nbsp;</label>
                <textarea
                    name="description"
                    class="w-full border min-h-[160px] rounded px-3 py-2"
                    rows="3"
                    placeholder="Description (optional)"
                >{{ old("description") }}</textarea>
</div>
<div>
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded"
                >
                    Create Course
                </button>
            </div>
        </div>
    </form>
    </div>
</x-layout>
