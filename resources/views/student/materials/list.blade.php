{{-- resources/views/student/materials/list.blade.php --}}
<x-layout>
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
                    href="{{ route("student.materials.index", $course) }}?type={{ $type }}&level={{ $level }}"
                    class="hover:underline"
                >
                    {{ ucfirst(str_replace("_", "-", $type ?? "Materials")) }}
                    Timetable
                </a>
                <span class="mx-2">/</span>
            </li>
            <li class="font-semibold">Week {{ $week }} - {{ $day }}</li>
        </ol>
    </nav>
    <section
        class="max-w-6xl mx-auto p-6 rounded-lg shadow border border-gray-300"
    >
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
            <div
                class="mt-4 overflow-x-auto rounded-lg shadow-sm border border-gray-300"
            >
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-900 text-white">
                        <tr>
                            <th class="px-3 py-3 text-left">Title</th>
                            <th class="px-3 py-3 text-left">Type</th>
                            <th class="px-1 py-3 text-left">Uploaded</th>
                            <th class="px-1 py-3 text-left"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($materials as $m)
                            <tr
                                class="hover:bg-gray-50 border-b border-gray-200"
                                onclick="
                                    window.location =
                                        '{{ route("student.materials.show", $m) }}'
                                "
                            >
                                <td class="px-3 py-3">
                                    <a
                                        class="text-blue-600 underline"
                                        href="{{ route("student.materials.show", $m) }}"
                                    >
                                        {{ $m->title }}
                                    </a>
                                </td>
                                <td class="px-3 py-3">
                                    {{ str($m->type)->replace("_", " ")->title() }}
                                </td>
                                <td class="px-1 py-3">
                                    {{ optional($m->uploaded_at)->format("M d, Y") }}
                                </td>
                                <td
                                    class="px-1 py-3 space-x-3 whitespace-nowrap"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="#000000"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M9 18l6-6-6-6" />
                                    </svg>
                                    {{--
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
                                        @endif Student-specific actions
                                    --}}
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
    </section>
</x-layout>
