<x-layout title="Edit Submission">
    <nav class="mb-2 text-sm text-gray-600 p-3" aria-label="Breadcrumb">
        <ol class="list-reset flex">
            <li>
                <a
                    href="{{ route("student.dashboard") }}"
                    class="hover:underline"
                >
                    Dashboard
                </a>
                <span class="mx-2">/</span>
            </li>
            <li>
                <a
                    href="{{ route("student.assignments.index", $assignment->course) }}?level={{ $assignment->level }}"
                    class="hover:underline"
                >
                    Assignments
                </a>
                <span class="mx-2">/</span>
            </li>
            <li>
                <a
                    href="{{ route("student.assignments.show", $assignment) }}"
                    class="hover:underline"
                >
                    {{ $assignment->title }}
                </a>
            </li>
            <li>
                <span class="mx-2">/</span>
            </li>
            <li class="text-black font-semibold">Edit Submission</li>
        </ol>
    </nav>
    <section
        class="max-w-2xl mx-auto p-6 space-y-6 shadow border border-gray-300 rounded-lg"
    >
        {{-- Flashes / Errors --}}
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

        @php
            $levelColors = [
                3 => "bg-[#9bd1f8]",
                2 => "bg-[#c7f7cf]",
                1 => "bg-[#f0c6bc]",
            ];
            // Use level filter for header, default to gray
            $headerColor = $levelColors[$assignment->level ?? null] ?? "bg-gray-100";
        @endphp

        <div
            class="flex items-center {{ $headerColor }} rounded-lg justify-between p-4"
        >
            <div>
                <h1 class="text-2xl font-bold">{{ $assignment->title }}</h1>
                <h1 class="text-lg text-gray-600 font-thin">
                    {{ $assignment->level ? "Level " . $assignment->level : "All Levels" }}
                </h1>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route("student.assignments.submissions.update", [$assignment, $submission]) }}"
            enctype="multipart/form-data"
            class="bg-white border border-gray-300 rounded-xl shadow p-6 space-y-4"
        >
            @csrf
            @method("PUT")

            <div>
                <label class="block text-sm font-medium text-gray-700">
                    Replace File
                </label>
                <input
                    type="file"
                    name="file"
                    id="file"
                    class="mt-1 w-full block border border-gray-300 rounded-lg text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                />
                <label for="file" class="sr-only">Choose file</label>
                @error("file")
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror

                @if ($submission->file_path)
                    <p class="text-sm text-gray-500 mt-4">
                        Submitted File:
                        <a
                            class="text-blue-600 hover:underline"
                            href="{{ route("student.submissions.download", $submission) }}"
                        >
                            {{ basename($submission->file_path) }}
                        </a>
                    </p>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <button
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                    Update
                </button>
                <a
                    href="{{ route("student.assignments.show", $assignment) }}"
                    class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-100"
                >
                    Back
                </a>
            </div>
        </form>
    </section>
</x-layout>
