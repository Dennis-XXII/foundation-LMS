<x-layout title="Edit Useful Link">
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
            <li>
                <a
                    href="{{ route("lecturer.courses.useful_links.index", $course) }}"
                    class="hover:underline"
                >
                    Useful Links
                </a>
                <span class="mx-2">/</span>
            </li>
            <li class="text-black font-semibold">Edit Link</li>
        </ol>
    </nav>

    <div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow border">
        <div class="bg-gray-100 rounded p-4 mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">
                Edit Useful Link — {{ $course->code }}
            </h1>
            <p class="text-sm text-gray-600">
                Update the link resource details.
            </p>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="p-4 mb-6 rounded bg-red-50 text-red-700 border border-red-200">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route("lecturer.useful_links.update", $usefulLink) }}"
            class="space-y-6"
        >
            @csrf
            @method('PUT')

            {{-- Title --}}
            <div>
                <label for="title" class="block text-sm font-semibold text-gray-700 mb-1">
                    Title
                </label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old("title", $usefulLink->title) }}"
                    class="w-full border border-gray-400 rounded px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                    placeholder="Enter link title"
                    required
                />
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-1">
                    Description (Optional)
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    class="w-full border border-gray-400 rounded px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none min-h-[100px]"
                    placeholder="Provide a brief description of what this link is for..."
                >{{ old("description", $usefulLink->description) }}</textarea>
            </div>

            {{-- Link URL --}}
            <div>
                <label for="link" class="block text-sm font-semibold text-gray-700 mb-1">
                    Link URL
                </label>
                <input
                    type="url"
                    id="link"
                    name="link"
                    value="{{ old("link", $usefulLink->link) }}"
                    class="w-full border border-gray-400 rounded px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                    placeholder="https://example.com"
                    required
                />
            </div>

            {{-- Buttons --}}
            <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                <button
                    type="submit"
                    class="px-5 py-2.5 bg-blue-600 text-white font-medium rounded hover:bg-blue-700 shadow-sm transition"
                >
                    Update Link
                </button>
                <a
                    href="{{ route("lecturer.courses.useful_links.index", $course) }}"
                    class="px-5 py-2.5 border border-gray-300 rounded font-medium text-gray-700 hover:bg-gray-50 transition"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layout>
