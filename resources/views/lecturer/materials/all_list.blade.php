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
                <span class="font-semibold text-gray-800">
                    Materials for {{ $course->code }}
                </span>
            </li>
        </ol>
    </nav>

    <div class="max-w-7xl mx-auto p-2">
        @php
            $levelColors = [
                3 => "bg-[#9bd1f8]",
                2 => "bg-[#c7f7cf]",
                1 => "bg-[#f0c6bc]",
            ];
            // Use level filter for header, default to gray
            $headerColor = $levelColors[$level ?? null] ?? "bg-gray-100";
        @endphp

        {{-- Header --}}
        <div
            class="flex items-center justify-between p-6 rounded-xl shadow-sm {{ $headerColor }} mb-8"
        >
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ $type ? ucfirst(str_replace("_", " ", $type)) : "All Materials" }}
                </h1>
                <p class="text-lg text-gray-700 mt-1 opacity-80">
                    {{ $course->code }} — {{ $course->name }}
                </p>
            </div>
        </div>

        {{-- FILTERS SECTION --}}
        <div class="mb-8 space-y-4">
            {{-- 1. Type Buttons (Pills) --}}
            <div class="flex flex-wrap items-center gap-2">
                <span
                    class="text-sm font-semibold text-gray-500 mr-2 uppercase tracking-wide"
                >
                    Category:
                </span>

                @php
                    $types = [
                        "" => "All",
                        "lesson" => "Lesson Materials",
                        "worksheet" => "Worksheets",
                        "self_study" => "Self-study",
                    ];
                    $currentType = request("type");
                @endphp

                @foreach ($types as $key => $label)
                    <a
                        href="{{ request()->fullUrlWithQuery(["type" => $key]) }}"
                        class="px-5 py-2 rounded-full text-sm font-medium transition-all shadow-sm {{
                            $currentType == $key ? "bg-black text-white shadow-md transform scale-105" : "bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 hover:border-gray-300"
                        }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- 2. Level & Time Filters Form --}}
            <form
                method="GET"
                class="flex flex-wrap items-end gap-4 p-4 bg-gray-50 rounded-lg border border-gray-100"
            >
                {{-- Maintain Type in hidden input so applying level doesn't reset type --}}
                @if (request("type"))
                    <input
                        type="hidden"
                        name="type"
                        value="{{ request("type") }}"
                    />
                @endif

                {{-- Level --}}
                <div>
                    <label
                        class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1"
                    >
                        Level
                    </label>
                    <select
                        name="level"
                        class="bg-white border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-40 p-2.5"
                    >
                        <option value="">All Levels</option>
                        @foreach ([1, 2, 3] as $lv)
                            <option
                                value="{{ $lv }}"
                                @selected($level == $lv)
                            >
                                Level {{ $lv }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Week (Optional Filter) --}}
                <div>
                    <label
                        class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1"
                    >
                        Week
                    </label>
                    <select
                        name="week"
                        class="bg-white border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-32 p-2.5"
                    >
                        <option value="">Any Week</option>
                        @foreach (range(1, 8) as $w)
                            <option value="{{ $w }}" @selected($week == $w)>
                                Week {{ $w }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button
                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm shadow-sm transition-colors"
                >
                    Filter
                </button>

                @if ($level || $week || $day || $type)
                    <a
                        href="{{ route("lecturer.courses.materials.index", $course) }}"
                        class="text-sm text-red-600 hover:text-red-800 font-medium ml-auto"
                    >
                        ✕ Clear All Filters
                    </a>
                @endif
            </form>
        </div>

        {{-- Materials Grid/List --}}
        <div class="mt-6">
            <div
                class="overflow-hidden bg-white border border-gray-200 rounded-xl shadow-sm"
            >
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th
                                class="px-6 py-4 text-left font-semibold text-gray-600"
                            >
                                Title
                            </th>
                            <th
                                class="px-6 py-4 text-left font-semibold text-gray-600"
                            >
                                Type
                            </th>
                            <th
                                class="px-6 py-4 text-left font-semibold text-gray-600"
                            >
                                Level
                            </th>
                            <th
                                class="px-6 py-4 text-left font-semibold text-gray-600"
                            >
                                Week / Day
                            </th>
                            <th
                                class="px-6 py-4 text-left font-semibold text-gray-600"
                            >
                                Uploaded
                            </th>
                            <th
                                class="px-6 py-4 text-right font-semibold text-gray-600"
                            >
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($materials as $m)
                            <tr
                                class="hover:bg-gray-50 transition-colors cursor-pointer"
                                onclick="
                                    window.location =
                                        '{{ route("lecturer.materials.show", $m) }}'
                                "
                            >
                                <td class="px-6 py-4">
                                    <a
                                        class="font-medium text-blue-600 hover:underline text-base"
                                        href="{{ route("lecturer.materials.show", $m) }}"
                                    >
                                        {{ $m->title }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                                    >
                                        {{ str($m->type)->replace("_", " ")->title() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($m->level)
                                        <span
                                            class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold text-white {{ $m->level == 1 ? "bg-rose-400" : ($m->level == 2 ? "bg-green-400" : "bg-cyan-400") }}"
                                        >
                                            {{ $m->level }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    @if ($m->week)
                                            Week {{ $m->week }}
                                    @endif

                                    @if ($m->day)
                                        <span class="text-gray-400 mx-1">
                                            •
                                        </span>
                                        {{ $m->day }}
                                    @endif

                                    @if (! $m->week && ! $m->day)
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-500">
                                    {{ optional($m->uploaded_at)->format("M d, Y") }}
                                </td>
                                <td
                                    class="px-6 py-4 text-right"
                                    onclick="event.stopPropagation()"
                                >
                                    <div class="flex justify-end gap-3">
                                        @if ($m->url)
                                            <a
                                                href="{{ $m->url }}"
                                                target="_blank"
                                                class="text-gray-500 hover:text-blue-600 font-medium flex items-center gap-1"
                                            >
                                                <svg
                                                    class="w-4 h-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                                    ></path>
                                                </svg>
                                                Open
                                            </a>
                                        @endif

                                        @if ($m->file_path)
                                            <a
                                                class="text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1"
                                                href="{{ route("lecturer.materials.download", $m) }}"
                                            >
                                                <svg
                                                    class="w-4 h-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                                    ></path>
                                                </svg>
                                                Download
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    class="px-6 py-12 text-center text-gray-500 bg-gray-50"
                                    colspan="6"
                                >
                                    <div
                                        class="flex flex-col items-center justify-center"
                                    >
                                        <svg
                                            class="w-12 h-12 text-gray-300 mb-3"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            ></path>
                                        </svg>
                                        <p class="text-lg font-medium">
                                            No materials found.
                                        </p>
                                        <p class="text-sm">
                                            Try adjusting your filters.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($materials->hasPages())
                <div class="mt-6">
                    {{ $materials->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layout>
