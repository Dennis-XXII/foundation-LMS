{{-- resources/views/lecturer/assignments/create.blade.php --}}
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
            <li>
                <a
                    href="{{ route("lecturer.courses.assignments.index", $course) }}{{ request("level") ? "?level=" . request("level") : "" }}"
                    class="hover:underline"
                >
                    Upload Links
                </a>
                <span class="mx-2">/</span>
            </li>
            <li class="text-black font-semibold">Create Upload Link</li>
        </ol>
    </nav>
    {{-- Course header --}}
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-2xl font-semibold">
            Create Upload Link — {{ $course->code }} {{ $course->name }}
        </h1>

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

        <form
            class="mt-6 space-y-4"
            method="POST"
            action="{{ route("lecturer.courses.assignments.store", $course) }}"
            enctype="multipart/form-data"
        >
            @csrf

            {{-- Pass back any filters --}}
            @if (request("level"))
                <input
                    type="hidden"
                    name="level"
                    value="{{ request("level") }}"
                />
            @endif

            @if (request("week"))
                <input
                    type="hidden"
                    name="week"
                    value="{{ request("week") }}"
                />
            @endif

            @if (request("day"))
                <input type="hidden" name="day" value="{{ request("day") }}" />
            @endif

            <div>
                <label class="block text-sm font-medium">Title</label>
                <input
                    name="title"
                    class="mt-1 w-full border rounded px-3 py-2"
                    placeholder="Assignment 1"
                    value="{{ old("title") }}"
                    required
                />
            </div>

            <div>
                <label class="block text-sm font-medium">Instruction</label>
                <textarea
                    name="instruction"
                    rows="4"
                    placeholder="Give instructions here..."
                    class="mt-1 w-full border rounded px-3 py-2 min-h-[100px]"
                >
{{ old("instruction") }}</textarea
                >
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- LEVEL: lock if ?level= is present --}}
                <div>
                    @if (request()->filled("level"))
                        <input
                            type="hidden"
                            name="level"
                            value="{{ request("level") }}"
                        />
                        <label class="block text-sm font-medium">Level</label>
                        <div class="mt-1 px-3 py-2 border rounded bg-gray-50">
                            Level {{ request("level") }}
                            <span class="ml-2 text-xs text-gray-500">
                                (locked)
                            </span>
                        </div>
                    @else
                        <label class="block text-sm font-medium">Level</label>
                        <select
                            name="level"
                            class="mt-1 w-full border rounded px-3 py-2"
                        >
                            <option value="">Select level (optional)</option>
                            @foreach ([1, 2, 3] as $lv)
                                <option
                                    value="{{ $lv }}"
                                    @selected(old("level", request("level")) == $lv)
                                >
                                    Level {{ $lv }}
                                </option>
                            @endforeach
                        </select>
                    @endif
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
                                @selected(old("week", request("week")) == $w)
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
                                @selected(old("day", request("day")) == $dayName)
                            >
                                {{ $dayName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Due Date --}}
                <div>
                    <label class="block text-sm font-medium">Due Date</label>
                    <input
                        type="date"
                        name="due_at"
                        value="{{ old("due_at") }}"
                        class="mt-1 w-full border rounded px-3 py-2"
                    />
                </div>

                <div class="flex items-center gap-2 md:col-start-1">
                    <input
                        id="is_published"
                        name="is_published"
                        type="checkbox"
                        value="1"
                        @checked(old("is_published", 1))
                    />
                    <label for="is_published" class="text-sm">
                        Published (Active)
                    </label>
                </div>
            </div>

            {{-- File --}}
            <div class="border rounded p-4 space-y-3">
                <div>
                    <label class="block text-xs text-gray-600">
                        Attachment (optional)
                    </label>
                    <input type="file" name="file" class="mt-1 block" />
                    <p class="text-xs text-gray-500">
                        Allowed: pdf, doc, docx, ppt, pptx, zip. Max 20MB.
                    </p>
                </div>
            </div>

            <div class="flex gap-3">
                <button class="px-4 py-2 rounded bg-black text-white">
                    Create Link
                </button>
                @php
                    $backParams = http_build_query(
                        array_filter([
                            "level" => request("level"),
                            "week" => request("week"),
                            "day" => request("day"),
                        ]),
                    );
                @endphp

                <a
                    href="{{ route("lecturer.courses.assignments.index", $course) . ($backParams ? "?" . $backParams : "") }}"
                    class="px-4 py-2 rounded border"
                >
                    Back
                </a>
            </div>
        </form>
    </div>
</x-layout>
