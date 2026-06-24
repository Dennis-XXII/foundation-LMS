{{-- resources/views/student/materials/timetable.blade.php --}}
<x-layout>
    <nav
        class="mb-2 text-sm text-gray-600 p-3 lg:flex hidden"
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
            <li class="font-semibold">
                {{ ucfirst(str_replace("_", "-", $type ?? "Materials")) }}
                Timetable
            </li>
        </ol>
    </nav>
    <a
        href="{{ route("student.dashboard") }}"
        class="lg:hidden text-sm text-blue-600 hover:underline px-4 py-2 rounded border mb-4 inline-block"
    >
        &larr; Back to Dashboard
    </a>
    <section
        class="max-w-4xl mx-auto pb-2 lg:pb-4 rounded-xl lg:shadow border border-gray-200"
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

        {{-- Header (No "Add Material" button) --}}
        <div
            class="flex items-center justify-between p-4 rounded-t-lg {{ $headerColor }}"
        >
            <div>
                <h1 class="text-2xl font-semibold">
                    {{ ucfirst(str_replace("_", " ", $type ?? "Materials")) }}
                    Timetable
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
        <!-- <form
            method="GET"
            class="hidden mt-4 lg:flex flex-wrap gap-3 items-end justify-start place-self-left"
        >
            {{-- Type Filter --}}
            <div>
                <label class="block text-sm text-gray-600">Type</label>
                <select
                    name="type"
                    class="block border rounded py-2.5 px-2 text-xs w-full text-center"
                >
                    <option value="">All Types</option>
                    @foreach (["lesson" => "Lesson Materials", "homework" => "Homework", "self_study" => "Self‑study"] as $val => $label)
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

            {{--
                Clear Filters Link
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
                @endif
            --}}
        </form> -->

        {{-- Week/Day Navigation Grid (Timetable) --}}
        @php
            $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "REVIEW"];
        @endphp

        <div
            class="max-w-full mt-2 lg:mt-6 rounded-lg lg:p-2 place-self-center"
        >
            <table class="w-full text-md lg:text-base table-auto">
                <tbody class="bg-white">
                    @for ($w = 1; $w <= 8; $w++)
                        <tr class="border-b border-gray-100">
                            {{-- Changed from flex-wrap items-center to a layout that handles mobile better --}}
                            <td
                                class="px-2 py-2 lg:px-2 lg:py-1.5 flex flex-col sm:flex-row sm:items-center gap-y-3 gap-x-6"
                            >
                                {{-- Week Label: Full width on mobile, auto-width with border on desktop --}}
                                <span
                                    class="font-bold text-blue-700 sm:border-r border-gray-300 pr-2 w-fit"
                                >
                                    Week {{ $w }}:
                                </span>

                                {{-- Days Container: Wraps nicely on mobile --}}
                                <div
                                    class="flex flex-wrap items-center grid grid-cols-3 lg:grid-cols-6 gap-x-1 lg:gap-x-2 gap-y-2"
                                >
                                    @foreach ($days as $dayName)
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
                                                "px-2 py-2 justify-items-center text-center transition-colors whitespace-nowrap bg-blue-50 lg:bg-none",
                                                "hover:bg-blue-100 rounded-lg",
                                                "font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-lg" =>
                                                    $dayName === "REVIEW",
                                                "hover:text-blue-500" => $dayName !== "REVIEW",
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
        </div>
        <div class="pt-6 px-2 lg:px-6">
            <p class="text-xs text-gray-500">
                <strong>NOTE:</strong> The "Timetable" page provides a structured
                overview of the course schedule, allowing students to easily navigate
                to specific weeks and days. Each day may contain various types of
                materials, such as lessons, homework, and self-study resources, which
                are organized by week.
            </p>
        </div>
    </section>
</x-layout>
