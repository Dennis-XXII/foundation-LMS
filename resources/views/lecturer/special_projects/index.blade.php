{{-- resources/views/lecturer/special_projects/index.blade.php --}}
<x-layout>
    @php
        $level = (int) request("level");
        $levelLabel = $level ? "LEVEL " . $level : null;
        $week = (int) request("week");
        $day = request("day");

        $levelColors = [
            3 => "bg-[#9bd1f8]",
            2 => "bg-[#c7f7cf]",
            1 => "bg-[#f0c6bc]",
        ];
        $headerColor = $levelColors[$level ?? null] ?? "bg-gray-200";
    @endphp

    {{-- Breadcrumbs --}}
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
            <li class="text-black font-semibold">Special Projects</li>
        </ol>
    </nav>

    <div class="flex items-center justify-center gap-6 mb-8">
        <a
            href="{{ route("lecturer.courses.special_projects.index", $course) }}?level={{ $level }}"
            class="px-6 py-2.5 rounded-full shadow-sm {{ $headerColor }}"
        >
            Post Special Projects
        </a>
        <a
            href="{{ route("lecturer.courses.assessments.index", $course) }}?level={{ $level }}"
            class="px-6 py-2.5 rounded-full shadow-sm bg-gray-200 text-{{ $headerColor }} hover:bg-gray-300"
        >
            Assess Student Uploads
        </a>
    </div>

    {{-- If no level provided, guide the lecturer --}}
    @if (! $level)
        <div class="rounded border bg-yellow-50 text-yellow-800 px-4 py-3 mb-6">
            Please select a level from the dashboard to see special projects.
        </div>
    @endif

    {{-- Flash messages --}}
    @if (session("success"))
        <div
            class="mb-4 bg-green-50 text-green-800 border border-green-200 px-4 py-2 rounded"
        >
            {{ session("success") }}
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

    <section class="max-w-8xl mx-auto p-6 rounded-lg shadow border border-gray-300">
        <div
            class="flex items-center justify-between p-4 rounded-lg {{ $headerColor }}"
        >
            <div>
                <h1 class="text-2xl font-semibold">Special Projects</h1>
                <h1 class="text-xl font-thin">
                    {{ $level ? "Level $level" : "All Levels" }}
                </h1>
            </div>
            <a
                href="{{
                    route("lecturer.courses.special_projects.create", [
                        "course" => $course,
                        "level" => $level,
                    ])
                }}"
                class="px-4 py-2 rounded-full bg-gray-900 text-white hover:shadow-lg"
            >
                Create New Special Project
            </a>
        </div>

        {{-- Filters --}}
        <form method="GET" class="mt-4 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-sm text-gray-600">Level</label>
                <select
                    name="level"
                    class="block border rounded py-2.5 px-2 text-xs w-full text-center"
                >
                    <option value="">All Levels</option>
                    @foreach ([1, 2, 3] as $lv)
                        <option value="{{ $lv }}" @selected($level == $lv)>
                            Level {{ $lv }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button class="px-3 py-2 rounded bg-red-600 text-white">
                Apply Level
            </button>
        </form>

        {{-- Week/Day Navigation Grid --}}
        @php
            $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "REVIEW"];
        @endphp
        <div class="grid grid-cols-[auto_1fr] gap-4 mt-2">
            <aside class="w-72 mt-2">
                <table class="w-full text-xs border border-gray-200 mr-2 shadow-sm">
                    <tbody class="bg-white">
                        @for ($w = 1; $w <= 8; $w++)
                            <tr class="border-b border-gray-200">
                                <td class="flex flex-col-2 px-2 py-2 font-semibold">
                                    <div class="flex min-w-16 items-right border-r border-gray-200 mr-2">
                                        <a class="font-bold text-blue-700 whitespace-nowrap px-2 py-1 rounded">
                                            Week {{ $w }}:
                                        </a>
                                    </div>
                                    <div class="gap-x-1 gap-y-2 flex flex-wrap justify-start">
                                        @foreach ($days as $dayName)
                                            <a
                                                href="{{ request()->fullUrlWithQuery(["week" => $w, "day" => $dayName, "level" => $level]) }}"
                                                @class([
                                                    "font-normal",
                                                    "bg-gray-100 px-1 py-1 rounded",
                                                    "bg-gray-900 text-white" => $week == $w && $day == $dayName,
                                                    "hover:bg-gray-900 hover:text-white" => ! ($week == $w && $day == $dayName),
                                                    "hover:underline" => ! ($week == $w && $day == $dayName),
                                                    "text-purple-600 font-semibold" => $dayName === "REVIEW",
                                                    "text-black" => $dayName !== "REVIEW",
                                                ])
                                            >
                                                {{ $dayName }}
                                            </a>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </aside>

            {{-- Special Projects table (now filtered) --}}
            <main class="mt-2 rounded-lg flex-1">
                <div class="flex items-start justify-between mb-4">
                    <h2 class="text-xl font-semibold">
                        @if ($week && $day)
                            Special Projects for: Week {{ $week }}, {{ $day }}
                        @elseif ($level)
                            Special Projects for Level {{ $level }}
                        @else
                            All Special Projects
                        @endif
                    </h2>

                    @if ($week && $day && $level)
                        <div class="flex gap-2">
                            <a href="{{ route("lecturer.courses.special_projects.index", $course) }}?level={{ $level }}"
                                class="px-4 py-2 rounded bg-gray-200 border border-gray-300 text-sm font-medium hover:bg-gray-300 transition shadow-sm"
                            >
                                &larr; Back to all Special Projects
                            </a>

                            <a href="{{ route('lecturer.courses.special_projects.create', [
                                    'course' => $course,
                                    'level' => $level,
                                    'week' => $week,
                                    'day' => $day,
                                ]) }}"
                                class="px-4 py-2 rounded {{ $headerColor }} border border-gray-300 text-sm font-medium hover:bg-blue-300 transition shadow-sm"
                            >
                                Create for Week {{ $week }} - {{ $day }}
                            </a>
                        </div>
                    @endif
                </div>

                <div class="mt-4 overflow-x-auto rounded-xl shadow-md">
                    <table class="min-w-full text-sm bg-white shadow-sm">
                        <thead class="bg-gray-900 text-left">
                            <tr class="text-sm text-white">
                                <th class="px-4 py-3">Special Project Title</th>
                                <th class="px-4 py-3">Level</th>
                                <th class="px-4 py-3">Week / Day</th>
                                <th class="px-4 py-3">Due Date</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($specialProjects as $a)
                                <tr class="hover:bg-gray-50 border-b border-gray-200" onClick="window.location='{{ route("lecturer.special_projects.show", $a) }}'">
                                    <td class="px-4 py-3">
                                        <a
                                            href="{{ route("lecturer.special_projects.show", $a) }}"
                                            class="text-blue-600 hover:underline font-medium"
                                        >
                                            {{ $a->title }}
                                        </a>
                                    </td>
                                    
                                    <td class="px-4 py-3">{{ $a->level ?? "—" }}</td>
                                    @if ($a->week && $a->day)
                                        <td class="px-4 py-3">Week {{ $a->week ?? "—" }} &mdash; {{ $a->day ?? "—" }}</td>
                                    @else
                                        <td class="px-4 py-3">—</td>
                                    @endif
                                    <td class="px-4 py-3">
                                        @if ($a->due_at)
                                            {{ \Illuminate\Support\Carbon::parse($a->due_at)->timezone(config("app.timezone"))->format("M d, Y") }}
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $active = (bool) $a->is_published;
                                        @endphp

                                        <span
                                            class="px-2 py-1 text-xs rounded {{ $active ? "bg-green-50 text-green-700" : "bg-gray-100 text-gray-600" }}"
                                        >
                                            {{ $active ? "Active" : "Draft" }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3" onClick="event.stopPropagation();">
                                        <div class="flex items-start gap-3">
                                            @if (! empty($a->file_path))
                                                <a
                                                    class="text-blue-700 underline"
                                                    href="{{ route("lecturer.special_projects.download", $a) }}"
                                                >
                                                    Download
                                                </a>
                                            @endif

                                            <button
                                                class="text-blue-600 hover:underline"
                                                onclick="
                                                    location.href =
                                                        '{{ route("lecturer.special_projects.edit", $a) }}'
                                                "
                                            >
                                                Edit
                                            </button>
                                            <form
                                                method="POST"
                                                action="{{ route("lecturer.special_projects.destroy", $a) }}"
                                                onsubmit="
                                                    return confirm(
                                                        'Delete this special project?',
                                                    );
                                                "
                                            >
                                                @csrf
                                                @method("DELETE")
                                                <button
                                                    class="text-rose-600 hover:underline"
                                                >
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-6 py-6 text-gray-500" colspan="7">
                                        No special projects found matching these filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </section>

    {{-- Paginator --}}
    @if (method_exists($specialProjects, "links"))
        <div class="mt-4 px-6 py-3">
            {{ $specialProjects->withQueryString()->links() }}
        </div>
    @endif
</x-layout>
