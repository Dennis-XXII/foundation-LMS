{{-- resources/views/lecturer/materials/show.blade.php --}}
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
                    {{ ucfirst(str_replace('_', '-', $type ?? "Material")) }} Timetable
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
            <li class="text-black font-semibold">
                {{ $material->title }}
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
            $headerColor = $levelColors[$material->level ?? null] ?? "bg-gray-100";
        @endphp

        {{-- Course header --}}
        <div class="max-w-4xl mx-auto p-6 rounded-lg shadow border border-gray-300">
            {{-- Header with Edit/Delete buttons --}}
            <div
                class="flex items-center {{ $headerColor }} rounded-lg justify-between p-4"
            >
                <div>
                    <h1 class="text-2xl font-semibold">
                        {{ $material->title }}
                    </h1>
                    <h1 class="text-xl text-gray-600 font-thin">
                        {{ $material->level ? "Level $material->level" : "All Levels" }}
                    </h1>
                </div>
                <div class="flex gap-2">
                    <a
                        href="{{ route("lecturer.materials.edit", $material) }}"
                        class="px-4 py-2 rounded bg-blue-600 text-white text-sm font-medium"
                    >
                        Edit
                    </a>
                    <form
                        method="POST"
                        action="{{ route("lecturer.materials.destroy", $material) }}"
                        onsubmit="
                            return confirm(
                                'Are you sure you want to delete this material?',
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
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Column 1 --}}
                <div class="md:col-span-2 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">
                            Description
                        </label>
                        <div
                            class="mt-1 p-3 min-h-[100px] text-gray-800 bg-gray-50 rounded border border-gray-300"
                        >
                            {!! nl2br(e($material->descriptions)) !!}
                        </div>
                    </div>

                    @if ($material->file_path)
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-500"
                            >
                                File
                            </label>
                            <div class="border border-gray-300 p-3 rounded mt-1 bg-gray-50">
                            <a
                                href="{{ route("lecturer.materials.download", $material) }}"
                                class="mt-1 text-blue-600 hover:underline text-sm font-medium"
                            >
                                {{ basename($material->file_path) }}
                            </a>
                        </div>
                    @endif
        </div>

                    @if ($material->url)
                    <div>
                    <label
                                class="block text-sm font-medium text-gray-500"
                            >
                                External Link
                            </label>
                        <div class="border border-gray-300 p-3 rounded mt-1 bg-gray-50">
                            
                            <a
                                href="{{ $material->url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-sm text-blue-600 hover:underline font-medium break-all"
                            >
                                {{ $material->url }}
                            </a>
                        </div>
                    @endif
                    </div>
                </div>

                {{-- Column 2 (Sidebar) --}}
                <div>
                <label class="block text-sm font-medium text-gray-500">Details</label>
                <div class="space-y-4 bg-gray-50 p-4 rounded-lg border border-gray-100 mt-1">
                    <div>
                        <label class="block text-md font-medium text-gray-500">
                            Status:
                        @if ($material->is_published)
                            <p
                                class="ml-1 inline-flex items-left px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800"
                            >
                                Published
                            </p>
                        @else
                            <p
                                class="ml-1 inline-flex items-left px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800"
                            >
                                Draft
                            </p>
                        @endif
                        </label>
                        
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">
                            Type of Material
                        </label>
                        <p class="mt-1 text-gray-900">
                            {{ str($material->type)->replace("_", " ")->title() }} Material
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">
                            Level
                        </label>
                        <p class="mt-1 text-gray-900">
                            {{ $material->level ?? "All Levels" }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">
                            Week / Day
                        </label>
                        <p class="mt-1 text-gray-900">
                            {{ $material->week ?? "—" }} &mdash; {{ $material->day ?? "—" }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">
                            Uploaded on
                        </label>
                        <p class="mt-1 text-gray-900">
                            {{ optional($material->uploaded_at)->format("M d, Y") ?? "—" }}
                        </p>
                    </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-4 border-t">
                <a
                    href="{{ route("lecturer.courses.materials.index", $material->course) }}"
                    class="px-4 py-2 rounded border text-sm"
                >
                    &larr; Back to All Materials
                </a>
            </div>
        </div>
    </section>
</x-layout>
