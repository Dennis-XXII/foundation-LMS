{{-- resources/views/student/dashboard.blade.php --}}
<x-layout title="Student Dashboard">
    @php
        $levels = [3, 2, 1];

        $levelColors = [
            3 => "bg-[#9bd1f8]",
            2 => "bg-[#c7f7cf]",
            1 => "bg-[#f0c6bc]",
        ];

        $levelColorsHalved = [
            3 => "bg-[#9bd1f8]/50",
            2 => "bg-[#c7f7cf]/50",
            1 => "bg-[#f0c6bc]/50",
        ];

        $headerColor = $levelColors[$student_level ?? null] ?? "bg-gray-100";

        $tile = fn ($label, $type, $level, $color) => ["label" => $label, "type" => $type, "level" => $level, "color" => $color];
        $tiles = [];
        foreach ($levels as $lv) {
            $tiles[] = $tile("Lesson Materials", "lesson", $lv, $levelColors[$lv]);
            $tiles[] = $tile("Worksheets", "worksheet", $lv, $levelColors[$lv]);
            $tiles[] = $tile("Self-study", "self_study", $lv, $levelColors[$lv]);
            $tiles[] = $tile("Assignments", "upload", $lv, $levelColors[$lv]);
        }

        $hasCourse = isset($course) && $course;
    @endphp

    <div class="mx-auto items-center">
        <div
            class="mx-auto w-full lg:max-w-4xl overflow-hidden bg-white rounded-md lg:shadow-sm border border-gray-100"
        >
            {{-- Header Section --}}
            <div
                class="flex flex-col md:flex-row md:items-center justify-between px-2 py-4 lg:px-6 lg:py-6 {{ $headerColor }}"
            >
                {{-- Left Side: Code, Badge, and Name --}}
                <div
                    class="flex flex-col sm:flex-row sm:items-center lg:gap-4 ml-1"
                >
                    <h2
                        class="flex items-center text-xl font-bold text-gray-800 tracking-tight"
                    >
                        {{ $course->code ?? "COURSE" }}

                        @if ($student_level)
                            <span
                                class="ml-4 inline-flex items-center rounded-full bg-white/95 px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-gray-700 shadow-sm border border-black/5"
                            >
                                Level {{ $student_level }}
                            </span>
                        @endif
                    </h2>

                    {{-- Separator visible only on desktop to clean up the layout --}}
                    <span class="hidden sm:flex font-light">|</span>

                    <p class="text-xs lg:text-md font-base">
                        {{ $course->name ?? ($course->title ?? "Untitled Course") }}
                    </p>
                </div>

                {{-- Right Side: Action Slot (Maintains layout balance) --}}
                <div class="flex items-center gap-3"></div>
            </div>

            <div class="flex flex-col lg:flex-row gap-4 lg:p-6 p-1">
                {{-- Navigation / Shortcuts --}}
                {{-- Mobile: Horizontal Grid | Desktop: Vertical Sidebar --}}
                <aside
                    id="sidebar"
                    class="w-full lg:w-1/6 grid grid-cols-4 lg:flex lg:flex-col gap-3"
                >
                    @php
                        $sideBtnClass =
                            "flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 p-3 text-center text-xs font-medium text-gray-700 transition-colors hover:bg-white hover:shadow-sm disabled:opacity-50 lg:p-4 lg:text-sm ";
                    @endphp

                    <a
                        class="{{ $sideBtnClass }}"
                        onClick="bg-gray-100"
                        disabled
                    >
                        Emergency
                    </a>
                    <a
                        class="{{ $sideBtnClass }}"
                        onClick="
                            bg-gray-100
                            location.href =
                                'https://rsuip.org/about-us/campus/';
                        "
                        disabled
                    >
                        Maps
                    </a>
                    <a
                        class="{{ $sideBtnClass }}"
                        onClick="bg-gray-100"
                        disabled
                    >
                        Links
                    </a>
                    <a
                        class="{{ $sideBtnClass }}"
                        href="{{ route("student.profile.show") }}"
                        style="text-decoration: none"
                    >
                        Profile
                    </a>
                </aside>

                {{-- Tiles Grid Grouped by Level --}}
                <div class="flex-1 space-y-8">
                    @php
                        $groupedTiles = collect($tiles)->groupBy("level");
                    @endphp

                    @foreach ($groupedTiles as $level => $levelTiles)
                        @if ($student_level !== null && $level <= $student_level)
                            <div class="level-group">
                                {{-- Level Header --}}
                                <h2
                                    class="mb-4 text-md font-bold uppercase text-gray-500"
                                >
                                    Level {{ $level }} Materials
                                </h2>

                                {{-- Horizontal Scroll Container --}}
                                <div
                                    class="flex overflow-x-auto no-scrollbar gap-4 pb-4 scroll-behavior-smooth border-b border-gray-200"
                                >
                                    @foreach ($levelTiles as $t)
                                        @php
                                            if ($hasCourse) {
                                                if ($t["type"] !== "upload") {
                                                    $href = route("student.materials.index", $course) . "?type=" . $t["type"] . "&level=" . $t["level"];
                                                } else {
                                                    $href = route("student.assignments.index", $course) . "?level=" . $t["level"];
                                                }
                                            } else {
                                                $href = null;
                                            }

                                            // Added flex-shrink-0 and fixed widths to maintain the shape during scroll
                                            $tileBaseClass = "relative flex flex-shrink-0 w-40 justify-center text-center items-center min-w-[2rem] flex-col justify-between rounded-md border border-gray-300 px-6 py-6 lg:px-8 lg:py-6 transition-all " . $t["color"];
                                        @endphp

                                        @if ($href)
                                            <a
                                                href="{{ $href }}"
                                                class="{{ $tileBaseClass }}"
                                            >
                                                <h3
                                                    class="text-sm lg:text-md font-base text-gray-800"
                                                >
                                                    {{ $t["label"] }}
                                                </h3>
                                            </a>
                                        @else
                                            <div
                                                class="{{ $tileBaseClass }} opacity-60 cursor-not-allowed"
                                                title="Enroll in a course to access"
                                            >
                                                <h3
                                                    class="text-sm lg:text-md font-base text-gray-800"
                                                >
                                                    {{ $t["label"] }}
                                                </h3>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <style>
                    /* Hides scrollbar while allowing functionality */
                    .no-scrollbar::-webkit-scrollbar {
                        display: none;
                    }
                    .no-scrollbar {
                        -ms-overflow-style: none;
                        scrollbar-width: none;
                    }
                </style>
            </div>
        </div>
    </div>
</x-layout>
