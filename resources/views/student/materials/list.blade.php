{{-- resources/views/student/materials/list.blade.php --}}
<x-layout>
    <nav class="mb-6 text-sm text-gray-600" aria-label="Breadcrumb">
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
                    href="{{ route("student.materials.index", $course) }}?type={{ $type }}&level={{ $level }}"
                    class="hover:underline"
                >
                    {{ ucfirst(str_replace("_", "-", $type ?? "Materials")) }} Timetable
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
            // Use student's enrolled level for header, if available. Use filter level as fallback.
            $headerColor = $levelColors[$student_level ?? ($level ?? null)] ?? "bg-gray-100";
        @endphp

        {{-- Header --}}
        <div
            class="flex items-center justify-between p-4 rounded-lg {{ $headerColor }}"
        >
            <div>
                <h1 class="text-2xl font-semibold">
                    {{ ucfirst($type ?? "Material") }} - Week {{ $week }}:
                    {{ $day }}
                </h1>
                <h1 class="text-xl font-thin">
                    {{ $level ? "Level $level" : "All Levels" }}
                </h1>
            </div>
        </div>

        {{-- Materials List --}}
        <div class="mt-2">
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm border border-gray-400 rounded">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Title</th>
                            <th class="px-3 py-2 text-left">Type</th>
                            <th class="px-3 py-2 text-left">Level</th>
                            <th class="px-3 py-2 text-left">Uploaded</th>
                            <th class="px-3 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($materials as $m)
                            <tr class="border-t border-gray-400">
                                <td class="px-3 py-2">
                                    <a
                                        class="text-blue-600 underline"
                                        href="{{ route("student.materials.show", $m) }}"
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
                                <td
                                    class="px-3 py-2 space-x-3 whitespace-nowrap"
                                >
                                    {{-- Student-specific actions --}}
                                    @if ($m->url)
                                        <a
                                            href="{{ $m->url }}"
                                            target="_blank"
                                            class="text-blue-600 hover:underline"
                                        >
                                            Open Link
                                        </a>
                                    @endif

                                    @if ($m->file_path)
                                        <a
                                            class="text-blue-600 underline"
                                            href="{{ route("student.materials.download", $m) }}"
                                        >
                                            Download
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-3 py-4 text-gray-500" colspan="5">
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
        </div>

        {{-- Back link to timetable --}}
        <div class="mt-8 pt-4 border-t">
            <a
                href="{{
                    route("student.materials.index", [
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
</x-layout>
