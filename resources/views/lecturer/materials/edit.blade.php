{{-- resources/views/lecturer/materials/edit.blade.php --}}
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
            <li>
                <a
                    href="{{
                        route("lecturer.courses.materials.index", [
                            "course" => $material->course,
                            "type" => $material->type,
                            "level" => $material->level,
                        ])
                    }}"
                    class="hover:underline"
                >
                    {{ ucfirst(str_replace("_", "-", $type ?? "Material")) }}
                    Timetable
                </a>
                <span class="mx-2">/</span>
            </li>
            <li>
                <a
                    href="{{
                        route("lecturer.materials.list", [
                            "course" => $material->course,
                            "type" => request("type") ?? $material->type,
                            "level" => request("level") ?? $material->level,
                            "week" => $material->week,
                            "day" => $material->day,
                        ])
                    }}"
                    class="hover:underline"
                >
                    Week {{ $material->week ?? "—" }} -
                    {{ $material->day ?? "—" }}
                </a>
                <span class="mx-2">/</span>
            </li>
            <li>
                {{-- Link back to the show page --}}
                <a
                    href="{{ route("lecturer.materials.show", $material) }}"
                    class="hover:underline"
                >
                    {{ $material->title }}
                </a>
                <span class="mx-2">/</span>
            </li>
            <li class="text-black font-semibold">Edit</li>
        </ol>
    </nav>

    <div class="max-w-8xl mx-auto p-3">
        @php
            $levelColors = [
                3 => "bg-[#9bd1f8]",
                2 => "bg-[#c7f7cf]",
                1 => "bg-[#f0c6bc]",
            ];
            // Use level filter for header, default to gray
            $headerColor = $levelColors[$material->level ?? null] ?? "bg-gray-100";
        @endphp

        {{-- Course header --}}

        <div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow border">
            <div class="{{ $headerColor }} rounded p-4 mb-6">
                <h1 class="text-2xl font-semibold">
                    Edit - {{ $material->title }}
                </h1>
                <p class="text-xl text-gray-600 font-thin pt-3">
                    Level - {{ $material->level ?? "—" }}
                </p>
            </div>

            {{-- Flash Messages --}}

            @if (session("success"))
                <div class="mt-4 p-3 rounded bg-green-50 text-green-700">
                    {{ session("success") }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-4 p-3 rounded bg-red-50 text-red-700">
                    <ul class="list-disc ml-5">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                class="mt-6 space-y-4"
                method="POST"
                action="{{ route("lecturer.materials.update", $material) }}"
                enctype="multipart/form-data"
            >
                @csrf
                @method("PUT")

                <div>
                    <label class="block text-sm font-medium">Title</label>
                    <input
                        name="title"
                        class="mt-1 w-full border border-gray-400 rounded px-3 py-2"
                        value="{{ old("title", $material->title) }}"
                        required
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium">
                        Descriptions
                    </label>
                    <textarea
                        name="descriptions"
                        rows="4"
                        class="mt-1 w-full border border-gray-400 rounded px-3 py-2"
                    >
{{ old("descriptions", $material->descriptions) }}</textarea
                    >
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium">Type</label>
                        <select
                            name="type"
                            class="mt-1 w-full border border-gray-400 rounded px-3 py-2"
                            required
                        >
                            @foreach (["lesson" => "Lesson", "worksheet" => "Worksheet", "self_study" => "Self‑study"] as $v => $label)
                                <option
                                    value="{{ $v }}"
                                    @selected(old("type", $material->type) === $v)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Level</label>
                        <select
                            name="level"
                            class="mt-1 w-full border border-gray-400 rounded px-3 py-2"
                        >
                            <option value="">Select level (optional)</option>
                            @foreach ([1, 2, 3] as $lv)
                                <option
                                    value="{{ $lv }}"
                                    @selected(old("level", $material->level) == $lv)
                                >
                                    Level {{ $lv }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Week</label>
                        <select
                            name="week"
                            class="mt-1 w-full border border-gray-400 rounded px-3 py-2"
                        >
                            <option value="">Select week (optional)</option>
                            @foreach (range(1, 8) as $w)
                                <option
                                    value="{{ $w }}"
                                    @selected(old("week", $material->week) == $w)
                                >
                                    Week {{ $w }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Day</label>
                        <select
                            name="day"
                            class="mt-1 w-full border border-gray-400 rounded px-3 py-2"
                        >
                            <option value="">Select day (optional)</option>
                            @foreach (["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "REVIEW"] as $day)
                                <option
                                    value="{{ $day }}"
                                    @selected(old("day", $material->day) == $day)
                                >
                                    {{ $day }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <input
                            id="is_published"
                            name="is_published"
                            type="checkbox"
                            value="1"
                            @checked(old("is_published", $material->is_published))
                        />
                        <label for="is_published" class="text-sm">
                            Published
                        </label>
                    </div>
                </div>

                <div class="border border-gray-400 rounded p-4 space-y-3">
                    <div class="text-sm font-medium">Attachment / URL</div>

                    @if ($material->file_path)
                        <div class="flex flex-wrap items-center gap-3">
                            <a
                                class="text-blue-600 underline"
                                href="{{ route("lecturer.materials.download", $material) }}"
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
                        <input type="file" name="file" class="mt-1 block" />
                        <p class="text-xs text-gray-500">
                            Allowed: pdf, doc, docx, ppt, pptx, zip. Max 20MB.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-600">
                            External URL (optional)
                        </label>
                        <input
                            name="url"
                            type="url"
                            class="mt-1 w-full border border-gray-400 rounded px-3 py-2"
                            value="{{ old("url", $material->url) }}"
                            placeholder="https://..."
                        />
                    </div>
                </div>

                <div class="flex gap-3">
                    <button class="px-4 py-2 rounded bg-black text-white">
                        Save
                    </button>
                    {{-- ### UPDATED "Back" LINK HERE ### --}}
                    <a
                        href="{{ route("lecturer.materials.show", $material) }}"
                        class="px-4 py-2 rounded border"
                    >
                        Back
                    </a>
                </div>
            </form>

            {{-- Delete button is now on the 'show' page, so it's removed from here --}}
        </div>
    </div>
</x-layout>
