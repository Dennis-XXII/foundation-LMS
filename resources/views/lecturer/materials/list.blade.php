{{-- resources/views/lecturer/materials/timetable.blade.php --}}
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
                {{-- Link back to timetable, preserving filters --}}
                <a
                    href="{{
                        route("lecturer.courses.materials.index", [
                            "course" => $course,
                            "type" => $type,
                            "level" => $level,
                        ])
                    }}"
                    class="hover:underline"
                >
                    {{ ucfirst(str_replace('_', '-', $type ?? "Material")) }} Timetable
                </a>
                <span class="mx-2">/</span>
            </li>
            <li class="font-semibold">Week {{ $week }} - {{ $day }}</li>
        </ol>
    </nav>

    <div class="max-w-8xl mx-auto p-3">
        @php
            $levelColors = [
                3 => "bg-[#9bd1f8]",
                2 => "bg-[#c7f7cf]",
                1 => "bg-[#f0c6bc]",
            ];
            $headerColor = $levelColors[$level ?? null] ?? "bg-gray-100";
        @endphp

        <div
            class="flex items-center justify-between p-4 rounded-lg {{ $headerColor }}"
        >
            <div>
                <h1 class="text-2xl font-semibold">
                    {{ ucfirst(str_replace('_', '-', $type ?? "Material")) }} - Week {{ $week }}:
                    {{ $day }}
                </h1>
                <h1 class="text-xl font-thin">
                    {{ $level ? "Level $level" : "All Levels" }}
                </h1>
            </div>
            {{-- Add Material button directs to create form, passing all filters (for pre-population) --}}
            <a
                href="{{
                    route("lecturer.courses.materials.create", [
                        "course" => $course,
                        "type" => $type,
                        "level" => $level,
                        "week" => $week,
                        "day" => $day,
                    ])
                }}"
                class="px-3 py-2 rounded bg-black text-white"
            >
                Add Material
            </a>
        </div>

        {{-- Materials List --}}
        <div class="mt-8">
            <h2 class="text-xl font-semibold">Filtered Materials</h2>

            @if (session("success"))
                <div class="mt-4 p-3 rounded bg-green-50 text-green-700">
                    {{ session("success") }}
                </div>
            @endif

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm border">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Title</th>
                            <th class="px-3 py-2 text-left">Type</th>
                            <th class="px-3 py-2 text-left">Level</th>
                            <th class="px-3 py-2 text-left">Uploaded</th>
                            <th class="px-3 py-2 text-left">Published</th>
                            <th class="px-3 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($materials as $m)
                            <tr class="border-t">
                                <td class="px-3 py-2">
                                    <a
                                        class="text-blue-600 underline"
                                        href="{{ route("lecturer.materials.show", $m) }}"
                                    >
                                        {{ $m->title }}
                                    </a>
                                </td>
                                <td class="px-3 py-2">
                                    {{ str($m->type)->replace("_", " ")->title() }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ $m->level ?? "—" }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ optional($m->uploaded_at)->format("Y-m-d") }}
                                </td>
                                <td class="px-3 py-2">
                                    <span
                                        class="inline-flex px-2 py-0.5 rounded text-xs {{ $m->is_published ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-600" }}"
                                    >
                                        {{ $m->is_published ? "Yes" : "No" }}
                                    </span>
                                </td>
                                <td
                                    class="px-3 py-2 space-x-3 whitespace-nowrap"
                                >
                                    @if ($m->file_path)
                                        <a
                                            class="text-blue-600 underline"
                                            href="{{ route("lecturer.materials.download", $m) }}"
                                        >
                                            Download
                                        </a>
                                    @endif

                                    <a
                                        class="text-gray-500 hover:underline"
                                        href="{{ route("lecturer.materials.edit", $m) }}"
                                    >
                                        Quick Edit
                                    </a>
                                    {{-- Delete form is modified to redirect back to this list view --}}
                                    <form
                                        method="POST"
                                        action="{{ route("lecturer.materials.destroy", $m) }}"
                                        class="inline"
                                        onsubmit="
                                            return confirm(
                                                'Delete this material?',
                                            );
                                        "
                                    >
                                        @csrf
                                        @method("DELETE")
                                        {{-- Preserve filters so redirect goes back to this list view --}}
                                        <input
                                            type="hidden"
                                            name="type"
                                            value="{{ $type }}"
                                        />
                                        <input
                                            type="hidden"
                                            name="level"
                                            value="{{ $level }}"
                                        />
                                        <input
                                            type="hidden"
                                            name="week"
                                            value="{{ $week }}"
                                        />
                                        <input
                                            type="hidden"
                                            name="day"
                                            value="{{ $day }}"
                                        />
                                        <button
                                            type="submit"
                                            class="text-rose-600 hover:underline"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-3 py-4 text-gray-500" colspan="8">
                                    No materials found matching these filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($materials->hasPages())
                <div class="mt-4">{{ $materials->links() }}</div>
            @endif

            <div class="mt-8 pt-4 border-t">
                <a
                    href="{{
                        route("lecturer.courses.materials.index", [
                            "course" => $course,
                            "type" => $type,
                            "level" => $level,
                        ])
                    }}"
                    class="px-4 py-2 rounded border text-sm hover:bg-gray-50"
                >
                    &larr; Back to Timetable
                </a>
            </div>
        </div>
    </div>
</x-layout>
