<x-layout title="Submit Assignment">
    <nav
        class="hidden lg:flex mb-2 text-sm text-gray-600 p-3"
        aria-label="Breadcrumb"
    >
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
            <li class="text-black font-semibold">Submit Assignment</li>
        </ol>
    </nav>

    <section
        class="max-w-4xl mx-auto lg:p-6 rounded-lg lg:shadow lg:border border-gray-300"
    >
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
            class="flex items-center {{ $headerColor }} rounded-lg justify-between p-4 mb-6"
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
            action="{{ route("student.assignments.submissions.store", $assignment) }}"
            enctype="multipart/form-data"
            class="bg-white border border-gray-300 rounded-xl shadow p-6 space-y-4"
        >
            @csrf
            {{-- File (optional) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">
                    Upload File
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
            </div>

            <div class="flex items-center gap-3">
                <button
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                    Submit
                    @if (session("success"))
                        <span class="ml-3 text-green-600 text-sm">
                            {{ session("success") }}
                        </span>
                    @endif
                </button>
                <a
                    href="{{ route("student.assignments.show", $assignment) }}"
                    class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-100"
                >
                    Cancel
                </a>
            </div>
        </form>
    </section>
</x-layout>
