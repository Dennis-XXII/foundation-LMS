{{-- resources/views/student/materials/show.blade.php --}}
<x-layout>
    {{-- Breadcrumbs --}}
    <nav class="mb-2 text-sm text-gray-600 p-3 lg:flex hidden" aria-label="Breadcrumb">
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
                    href="{{
                        route("student.materials.index", [
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
                        route("student.materials.list", [
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
    <a
                    href="{{
                        route("student.materials.list", [
                            "course" => $material->course,
                            "type" => request("type") ?? $material->type,
                            "level" => request("level") ?? $material->level,
                            "week" => $material->week,
                            "day" => $material->day,
                        ])
                    }}"
                    class="lg:hidden text-sm text-blue-600 hover:underline px-4 py-2 rounded border mb-4 inline-block"
                >
                    &larr; Week {{ $material->week ?? "—" }} -
                    {{ $material->day ?? "—" }} Materials
                </a>
    <section class="max-w-6xl mx-auto lg:p-6 rounded-lg lg:shadow lg:border border-gray-300">
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
    <div class="max-w-4xl mx-auto lg:p-6 rounded-lg lg:shadow lg:border border-gray-300">
        {{-- Header --}}
        <div class="flex items-center {{ $headerColor }} rounded-lg justify-between p-4">
            <div>
                <h1 class="text-2xl font-semibold">{{ $material->title }}</h1>
                <h1 class="text-lg text-gray-600 font-thin">
                    {{ $material->level ? "Level $material->level" : "All Levels" }}
                </h1>
            </div>
        </div>

        {{-- Details Grid --}}
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6 border border-gray-300 p-4 rounded-lg">
            {{-- Column 1: Description, Files, Link --}}
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
                <div>
                {{-- File Download --}}
                @if ($material->file_path)
                        <label class="block text-sm font-medium text-gray-500">
                            File
                        </label>
                    <div class="border border-gray-300 p-3 rounded-lg bg-gray-50">
                        <a
                            href="{{ route("student.materials.download", $material) }}"
                            class="mt-1 text-blue-600 hover:underline text-sm font-medium"
                        >
                            {{ basename($material->file_path) }}
                        </a>
                    </div>
                @endif
            </div>

                {{-- External Link --}}
            <div>

                @if ($material->url)
                <label class="block text-sm font-medium text-gray-500">
                            External Link
                        </label>
                    <div  class="border border-gray-300 p-3 rounded-lg bg-gray-50">
                        
                        <a
                            href="{{ $material->url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-sm mt-1 text-blue-600 hover:underline font-medium break-all"
                        >
                            {{ $material->url }}
                        </a>
                    </div>
                @endif
            </div>

                {{-- UPDATED: Related Assignment Link --}}
                @if ($relatedAssignment)
                    <div
                        class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg"
                    >
                        <h3 class="font-semibold text-yellow-800">
                            Related Assignment
                        </h3>
                        <p class="text-sm text-yellow-700 mt-1">
                            This material is related to the assignment:
                            <strong class="font-medium">
                                {{ $relatedAssignment->title }}
                            </strong>
                            .
                        </p>
                        {{-- Link directly to the assignment's show page --}}
                        <a
                            href="{{ route("student.assignments.show", $relatedAssignment) }}"
                            class="inline-block mt-2 px-3 py-1 bg-yellow-500 text-white text-xs font-medium rounded hover:bg-yellow-600"
                        >
                            View Assignment Details
                        </a>
                    </div>
                @endif

                {{-- END UPDATED --}}
            </div>

            {{-- Column 2 (Sidebar): Material Meta --}}
            <div>
                <label class="block text-sm font-medium text-gray-500">Details</label>
            <div class="space-y-4 bg-gray-50 p-4 rounded-lg border border-gray-100 mt-1">
                <div class="pb-2 border-b border-gray-200">
                    <label class="block text-sm font-medium text-gray-500">
                        Type
                    </label>
                    <p class="mt-1 text-gray-900">
                        {{ str($material->type)->replace("_", " ")->title() }} Material
                    </p>
                </div>
                <div class="pb-2 border-b border-gray-200">
                    <label class="block text-sm font-medium text-gray-500">
                        Level
                    </label>
                    <p class="mt-1 text-gray-900">
                        {{ $material->level ?? "All Levels" }}
                    </p>
                </div>
                <div class="pb-2 border-b border-gray-200">
                    <label class="block text-sm font-medium text-gray-500">
                        Week & Day
                    </label>
                    <p class="mt-1 text-gray-900">
                        Week {{ $material->week ?? "—" }} &mdash; {{ $material->day ?? "—" }}
                    </p>
                </div>
                <div class="pb-2 border-b border-gray-200">
                    <label class="block text-sm font-medium text-gray-500">
                        Uploaded
                    </label>
                    <p class="mt-1">
                        {{ optional($material->uploaded_at)->format("M d, Y") ?? "—" }}
                    </p>
                </div>
                </div>
            </div>
        </div>

        {{-- Back Link --}}
        <div class="mt-8 pt-4 border-t">
            <a
                    href="{{
                        route("student.materials.list", [
                            "course" => $material->course,
                            "type" => request("type") ?? $material->type,
                            "level" => request("level") ?? $material->level,
                            "week" => $material->week,
                            "day" => $material->day,
                        ])
                    }}"
                    class="lg:hidden text-sm text-blue-600 hover:underline px-4 py-2 rounded border mb-4 inline-block"
                >
                    &larr; Week {{ $material->week ?? "—" }} -
                    {{ $material->day ?? "—" }} Materials
                </a>
        </div>
    </section>
</x-layout>
