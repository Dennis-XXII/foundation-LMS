{{-- resources/views/student/materials/timetable.blade.php --}}
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
            <li class="font-semibold">
                    {{ ucfirst(str_replace("_", "-", $type ?? "Materials")) }} Timetable
            </li>
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

        {{-- Header (No "Add Material" button) --}}
        <div
            class="flex items-center justify-between p-4 rounded-lg {{ $headerColor }}"
        >
            <div>
                <h1 class="text-2xl font-semibold">
                    {{ ucfirst(str_replace("_", " ", $type ?? "Materials")) }} Timetable
                </h1>
                <h1 class="text-xl font-thin">
                    {{ $level ? "Level $level" : "All Levels" }}
                </h1>
            </div>
        </div>

        {{-- Flash messages for redirection from list page --}}
        @if (session("error"))
            <div class="mt-4 p-3 rounded bg-red-50 text-red-700">
                {{ session("error") }}
            </div>
        @endif

        {{-- Filters --}}
        <form method="GET" class="mt-4 flex flex-wrap gap-3 items-end">
            {{-- Type Filter --}}
            <div>
                <label class="block text-sm text-gray-600">Type</label>
                <select
                    name="type"
                    class="block border rounded py-2.5 px-2 text-xs w-full text-center"
                >
                    <option value="">All Types</option>
                    @foreach (["lesson" => "Lesson Materials", "worksheet" => "Worksheet", "self_study" => "Self‑study"] as $val => $label)
                        <option value="{{ $val }}" @selected($type === $val)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            {{-- Level Filter --}}
            <div>
                <label class="block text-sm text-gray-600">Level</label>
                <select
                    name="level"
                    class="block border rounded py-2.5 px-2 text-xs w-full text-center"
                >
                    <option value="">All Levels</option>
                    @foreach ([1, 2, 3] as $lv)
                        <option value="{{ $lv }}" @selected($level == $lv)>
                            {{ $lv }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Submitting this form clears week/day in the query string --}}
            <button class="px-3 py-2 rounded bg-red-600 text-white">
                Apply Type/Level
            </button>

            {{-- Clear Filters Link
            @if ($level || $type)
                <a
                    href="{{
                        route("student.materials.index", [
                            "course" => $course,
                        ])
                    }}"
                    class="text-sm text-blue-600 hover:underline"
                >
                    Clear Filters
                </a>
            @endif  --}}
        </form>

        {{-- Week/Day Navigation Grid (Timetable) --}}
        @php
            $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "REVIEW"];
        @endphp

        <div class="max-w-1/2 mt-6 overflow-x-auto rounded-lg shadow-lg p-2 place-self-left">
            <h2 class="text-xl font-semibold mb-3 p-2 rounded">
                Select a Date to View {{ ucfirst($type ?? "Material") }} Materials
            </h2>
            <table class="w-full text-base table-auto">
                <tbody class="bg-white">
                    @for ($w = 1; $w <= 8; $w++)
                        <tr class="border-b border-gray-200">
                            <td class="px-3 py-2 px-3 py-2 flex flex-wrap items-center gap-x-4 gap-y-1">
                                    <span class="font-bold text-blue-700">
                                        Week {{ $w }}:
                                    </span>

                                    @foreach ($days as $dayName)
                                        {{-- THIS IS THE CRITICAL CHANGE: Link to the new list route --}}
                                        {{-- Pass all existing filters (type, level) plus new time filters --}}
                                        <a
                                            href="{{
                                                route("student.materials.list", [
                                                    "course" => $course,
                                                    "week" => $w,
                                                    "day" => $dayName,
                                                    "type" => $type,
                                                    "level" => $level,
                                                ])
                                            }}"
                                            @class([
                                                "px-2 py-1",
                                                "hover:bg-blue-100 rounded-lg",
                                                "font-bold text-purple-700 hover:bg-purple-100 rounded-lg" => $dayName === "REVIEW",
                                                "hover:text-blue-500" => $dayName !== "REVIEW",
                                            ])
                                        >
                                            {{ $dayName }}
                                        </a>
                                    @endforeach
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
        <div class="mt-8 pt-4 border-t">
            <a
                href="{{
                    route("student.dashboard", [])
                }}"
                class="px-4 py-2 rounded border text-sm hover:bg-gray-50"
            >
                &larr; Back to Dashbord
            </a>
        </div>
    </div>
</x-layout>
