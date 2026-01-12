{{-- resources/views/lecturer/assignments/show.blade.php --}}
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
                    href="{{ route("lecturer.courses.assignments.index", $assignment->course) }}?level={{ $assignment->level }}"
                    class="hover:underline"
                >
                    Assignments
                </a>
                <span class="mx-2">/</span>
            </li>
            <li class="text-black font-semibold">
                {{ $assignment->title }}
            </li>
        </ol>
    </nav>

    <section class="max-w-8xl mx-auto p-3">
        @php
            $levelColors = [
                3 => "bg-[#9bd1f8]",
                2 => "bg-[#c7f7cf]",
                1 => "bg-[#f0c6bc]",
            ];
            // Use level filter for header, default to gray
            $headerColor = $levelColors[$assignment->level ?? null] ?? "bg-gray-100";
        @endphp

        {{-- Course header --}}
        <div
            class="max-w-4xl mx-auto p-6 rounded-lg shadow border border-gray-300"
        >
            {{-- Header with Edit/Delete buttons --}}
            <div
                class="flex items-center {{ $headerColor }} rounded-lg justify-between p-4"
            >
                <div>
                    <h1 class="text-2xl font-bold">
                        {{ $assignment->title }}
                    </h1>
                    <h1 class="text-xl text-gray-600 font-thin">
                        Level -
                        {{ $assignment->level }}
                    </h1>
                </div>
                <div class="flex gap-2">
                    <a
                        href="{{ route("lecturer.assignments.edit", $assignment) }}"
                        class="px-4 py-2 rounded bg-blue-600 text-white text-sm font-medium"
                    >
                        Edit
                    </a>
                    <form
                        method="POST"
                        action="{{ route("lecturer.assignments.destroy", $assignment) }}"
                        onsubmit="
                            return confirm(
                                'Are you sure you want to delete this assignment?',
                            );
                        "
                    >
                        @csrf
                        @method("DELETE")
                        <button
                            type="submit"
                            class="px-4 py-2 rounded bg-red-600 text-white text-sm font-medium"
                        >
                            Delete
                        </button>
                    </form>
                </div>
            </div>

            {{-- Details Grid --}}
            <div
                class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6 border border-gray-300 p-4 rounded-lg"
            >
                {{-- Column 1 --}}
                <div class="md:col-span-2 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">
                            Instruction
                        </label>
                        <div
                            class="mt-1 p-3 min-h-[100px] text-gray-800 bg-gray-50 rounded border"
                        >
                            {{ $assignment->instruction ?? "No instruction provided." }}
                        </div>
                    </div>
                    @if ($assignment->file_path)
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-500"
                            >
                                Attachment
                            </label>
                            <a
                                href="{{ route("lecturer.assignments.download", $assignment) }}"
                                class="mt-1 text-blue-600 hover:underline font-medium"
                            >
                                Download Attached File
                            </a>
                        </div>
                    @endif

                    @if ($assignment->url)
                        {{-- If you add URL later --}}
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-500"
                            >
                                External Link
                            </label>
                            <a
                                href="{{ $assignment->url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-1 text-blue-600 hover:underline font-medium"
                            >
                                {{ $assignment->url }}
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Column 2 (Sidebar) --}}
                <div class="space-y-4 bg-gray-50 p-4 rounded-lg">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">
                            Status
                        </label>
                        @if ($assignment->is_published)
                            <span
                                class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800"
                            >
                                Published (Active)
                            </span>
                        @else
                            <span
                                class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800"
                            >
                                Draft
                            </span>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">
                            Level
                        </label>
                        <p class="mt-1 text-gray-900">
                            {{ $assignment->level ?? "—" }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">
                            Week
                        </label>
                        <p class="mt-1 text-gray-900">
                            {{ $assignment->week ?? "—" }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">
                            Day
                        </label>
                        <p class="mt-1 text-gray-900">
                            {{ $assignment->day ?? "—" }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">
                            Due Date
                        </label>
                        <p class="mt-1 text-gray-900">
                            {{ optional($assignment->due_at)->format("M d, Y") ?? "—" }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Submissions table (anchor #submissions) --}}
            <h2 id="submissions" class="mt-10 text-xl font-semibold">
                Submissions
            </h2>
            @if ($assignment->submissions->isEmpty())
                <p class="text-sm text-gray-600 mt-2">No submissions yet.</p>
            @else
                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full text-sm border">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left">Student</th>
                                <th class="px-3 py-2 text-left">
                                    Submitted at
                                </th>
                                <th class="px-3 py-2 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assignment->submissions as $s)
                                <tr class="border-t">
                                    <td class="px-3 py-2">
                                        {{ $s->student->user->name }}
                                        <span class="text-gray-500">
                                            ({{ $s->student->user->email }})
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ optional($s->submitted_at)->format("M d, Y - H:i") }}
                                    </td>
                                    <td class="px-3 py-2">
                                        @if ($s->file_path)
                                            {{-- You will need to create this route --}}
                                            <a
                                                class="text-blue-600 underline"
                                                href="#"
                                            >
                                                Download Submission
                                            </a>
                                        @else
                                            <span class="text-gray-500">
                                                No file
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="mt-8 pt-4 border-t">
                <a
                    href="{{ route("lecturer.courses.assignments.index", $assignment->course) }}?level={{ $assignment->level }}"
                    class="px-4 py-2 rounded border text-sm hover:bg-gray-100"
                >
                    &larr; Back to All Assignments
                </a>
            </div>
        </div>
    </section>
</x-layout>
