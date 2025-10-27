{{-- resources/views/student/dashboard.blade.php --}}
<x-layout title="Student Dashboard">
@php
    // Levels from high to low to match lecturer dashboard
    $levels = [3, 2, 1];

    // Map level → header/tile background color (mirror lecturer)
    $levelColors = [
        3 => 'bg-[#9bd1f8]',
        2 => 'bg-[#c7f7cf]',
        1 => 'bg-[#f0c6bc]',
    ];

    // Define tiles like lecturer (label + type key)
    $tile = fn($label, $type, $level, $color) => ['label' => $label, 'type' => $type, 'level' => $level, 'color' => $color];
    $tiles = [];
    foreach ($levels as $lv) {
        $tiles[] = $tile('Lesson Materials', 'lesson', $lv, $levelColors[$lv]);
        $tiles[] = $tile('Worksheets', 'worksheet', $lv, $levelColors[$lv]);
        $tiles[] = $tile('Self-study', 'self_study', $lv, $levelColors[$lv]);
        $tiles[] = $tile('Upload Links', 'upload', $lv, $levelColors[$lv]);
    }

    $hasCourse = isset($course) && $course;
@endphp

<div class="max-w-6xl mx-auto px-4 py-6 space-y-8">
    <section class="bg-white rounded-lg shadow-lg">
        <div class="flex items-center justify-between bg-purple-900 text-white rounded-lg px-6 py-4">
            <h2 class="text-lg font-semibold">
                {{ ($course->code ?? 'COURSE') . ' ' . ($course->name ?? ($course->title ?? '')) }}
                @if ($student_level)
                    <span class="ml-3 font-normal text-sm px-2 py-1 rounded-full bg-white text-purple-900">
                        Level {{ $student_level }}
                    </span>
                @endif
            </h2>
            {{-- Right side intentionally empty for students (no edit/add buttons) --}}
            <div class="flex gap-3"></div>
        </div>

        <div class="p-6 flex gap-6">
            {{-- Left shortcuts (mirror lecturer) --}}
            <aside class="w-40 space-y-4">
                <button class="w-full bg-gray-100 border border-gray-300 rounded p-4 text-sm hover:bg-gray-50" disabled> Emergency Contact</button>
                <button class="w-full bg-gray-100 border border-gray-300 rounded p-4 text-sm hover:bg-gray-50" onclick="location.href='https://rsuip.org/about-us/campus/'">Maps</button>
                <button class="w-full bg-gray-100 border border-gray-300 rounded p-4 text-sm hover:bg-gray-50" disabled>Useful Links</button>
                <button class="w-full bg-gray-100 border border-gray-300 rounded p-4 text-sm hover:bg-gray-50" disabled>Profile</button>

            
            </aside>

            {{-- Tiles grid (mirrors lecturer visual structure) --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 flex-1">
                @foreach ($tiles as $t)
                    @if($t['level'] <= $student_level && $student_level !== null) {{-- Show only tiles at or below student's enrolled level --}}
                        @php
                            // Build hrefs only if we have a course. For materials, student controller supports TYPE filter only.
                            if ($hasCourse) {
                                if ($t['type'] !== 'upload') {
                                    // Students see published materials filtered by type; include level in QS (harmless for controller)
                                    $href = route('student.materials.index', $course) . '?type=' . $t['type'] . '&level=' . $t['level'];
                                } else {
                                    // Upload links go to assignments list for that level
                                    $href = route('student.assignments.index', $course) . '?level=' . $t['level'];
                                }
                            } else {
                                $href = null;
                            }
                        @endphp

                        @if($href)
                            <a href="{{ $href }}" class="block rounded-lg p-5 border border-gray-300 hover:shadow {{ $t['color'] }}">
                                <div class="text-xs text-gray-600 mb-1">LEVEL {{ $t['level'] }}</div>
                                <div class="text-lg font-semibold">{{ $t['label'] }}</div>
                            </a>
                        @else
                            <div class="block rounded-lg p-5 border border-gray-300 opacity-60 cursor-not-allowed {{ $t['color'] }}" aria-disabled="true" title="Enroll in a course to access this">
                                <div class="text-xs text-gray-600 mb-1">LEVEL {{ $t['level'] }}</div>
                                <div class="text-lg font-semibold">{{ $t['label'] }}</div>
                            </div>
                        @endif
                    @endif
                @endforeach
            </div>
        </div>
    </section>
</div>
</x-layout>