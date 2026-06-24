{{-- resources/views/lecturer/special_projects/edit.blade.php --}}
<x-layout>
    {{-- Breadcrumbs --}}
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
            <li class="text-black">
                <a
                    href="{{ route("lecturer.special_projects.show", $specialProject) }}"
                    class="hover:underline"
                >
                    {{ $specialProject->title }}
                </a>
                <span class="mx-2">/</span>
            </li>
            <li class="text-black font-semibold">Edit</li>
        </ol>
    </nav>

    {{-- Course header --}}
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-2xl font-semibold">Edit Special Project</h1>
        <p class="text-sm text-gray-600 mt-1">
            Course: {{ $specialProject->course->code }} —
            {{ $specialProject->course->name }}
        </p>

        {{-- Flash messages --}}
        @if (session("success"))
            <div class="mt-4 p-3 rounded bg-green-50 text-green-700">
                {{ session("success") }}
            </div>
        @endif

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="mt-4 p-3 rounded bg-red-50 text-red-700">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Update form --}}
        <form
            class="mt-6 space-y-4"
            method="POST"
            action="{{ route("lecturer.special_projects.update", $specialProject) }}"
            enctype="multipart/form-data"
        >
            @csrf
            @method("PUT")

            <div>
                <label class="block text-sm font-medium">Title</label>
                <input
                    name="title"
                    type="text"
                    class="mt-1 w-full border rounded px-3 py-2"
                    value="{{ old("title", $specialProject->title) }}"
                    required
                />
            </div>

            <div>
                <label class="block text-sm font-medium">Instruction</label>
                <textarea
                    name="instruction"
                    rows="5"
                    class="mt-1 w-full border rounded px-3 py-2"
                >
{{ old("instruction", $specialProject->instruction) }}</textarea
                >
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium">Level</label>
                    <input
                        name="level"
                        type="number"
                        min="1"
                        max="3"
                        class="mt-1 w-full border rounded px-3 py-2"
                        value="{{ old("level", $specialProject->level) }}"
                        required
                    />
                </div>

                {{-- Week --}}
                <div>
                    <label class="block text-sm font-medium">Week</label>
                    <select
                        name="week"
                        class="mt-1 w-full border rounded px-3 py-2"
                    >
                        <option value="">Select week (optional)</option>
                        @foreach (range(1, 8) as $w)
                            <option
                                value="{{ $w }}"
                                @selected(old("week", $specialProject->week) == $w)
                            >
                                Week {{ $w }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Day --}}
                <div>
                    <label class="block text-sm font-medium">Day</label>
                    <select
                        name="day"
                        class="mt-1 w-full border rounded px-3 py-2"
                    >
                        <option value="">Select day (optional)</option>
                        @foreach (["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "REVIEW"] as $dayName)
                            <option
                                value="{{ $dayName }}"
                                @selected(old("day", $specialProject->day) == $dayName)
                            >
                                {{ $dayName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium">Due at</label>
                    <input
                        name="due_at"
                        type="date"
                        class="mt-1 w-full border rounded px-3 py-2"
                        value="{{ old("due_at", optional($specialProject->due_at)->format("Y-m-d")) }}"
                    />
                </div>
                <div class="flex items-end gap-2">
                    <label class="block text-sm font-medium sr-only">
                        Published
                    </label>
                    <input
                        id="is_published"
                        name="is_published"
                        type="checkbox"
                        value="1"
                        @checked(old("is_published", $specialProject->is_published))
                    />
                    <label for="is_published" class="text-sm">Published</label>
                </div>
            </div>

            {{-- Attachment / URL section --}}
            <div class="border rounded p-4 space-y-3">
                <div class="text-sm font-medium">Attachment / URL</div>

                @if ($specialProject->file_path)
                    <div class="flex items-center gap-3">
                        <a
                            class="text-blue-600 underline"
                            href="{{ route("lecturer.special_projects.download", $specialProject) }}"
                        >
                            Download current file
                        </a>

                        <label
                            class="inline-flex items-center gap-2 text-sm text-red-700"
                        >
                            <input
                                type="checkbox"
                                name="remove_file"
                                value="1"
                            />
                            Remove current file on save
                        </label>
                    </div>
                @endif

                <div>
                    <label class="block text-xs text-gray-600">
                        Upload new file (replaces current)
                    </label>
                    <input name="file" type="file" class="mt-1 block" />
                    <p class="text-xs text-gray-500 mt-1">
                        Allowed: pdf, doc, docx, zip. Max 20MB.
                    </p>
                </div>

                <div>
                    <label class="block text-xs text-gray-600">
                        OR external URL
                    </label>
                    <input
                        name="url"
                        type="url"
                        class="mt-1 w-full border rounded px-3 py-2"
                        value="{{ old("url", $specialProject->url) }}"
                        placeholder="https://..."
                    />
                    <p class="text-xs text-gray-500 mt-1">
                        Choose either a URL or a file, not both.
                    </p>
                </div>
            </div>

            <div class="flex gap-3">
                <button
                    type="submit"
                    class="px-4 py-2 rounded bg-black text-white"
                >
                    Save changes
                </button>
                <a
                    href="{{ route("lecturer.special_projects.show", $specialProject) }}"
                    class="px-4 py-2 rounded border"
                >
                    Back
                </a>
            </div>
        </form>
    </div>
</x-layout>
