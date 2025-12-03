{{-- resources/views/student/dashboard.blade.php --}}
<x-layout title="Student Dashboard">
@php
    // Levels rendered from high to low
    $levels = [3,2,1];

    // Use route-friendly keys (self_study instead of self-study) to avoid normalization bugs
    $tiles = [
        ['key'=>'lesson',      'label'=>'Lesson Materials', 'bg'=>'bg-sky-300'],
        ['key'=>'worksheet',   'label'=>'Worksheets',       'bg'=>'bg-cyan-300'],
        ['key'=>'self_study',  'label'=>'Self-study',       'bg'=>'bg-cyan-400'],
        ['key'=>'upload',      'label'=>'Upload Links',     'bg'=>'bg-cyan-300'],
    ];

    // Is there a selected/active course?
    $hasCourse = isset($course) && $course;
@endphp

<div class="max-w-6xl mx-auto px-4 py-6 space-y-8">
    {{-- My Courses --}}
    <section class="space-y-4">
        <h2 class="text-lg font-semibold">My Courses</h2>

        <div class="bg-white rounded-xl shadow">
            <div class="rounded-t-xl px-6 py-4 bg-purple-900 text-white font-semibold text-lg">
            {{ strtoupper(
                $hasCourse
                    ? ($course->title ?? $course->code ?? 'Course')
                    : 'FOUNDATION AUGUST 2025'
            ) }}
            </div>
            <div class="p-4 md:p-6">
                <div class="grid grid-cols-12 gap-4">
                    {{-- Left shortcuts --}}
                    <aside class="col-span-12 sm:col-span-2 space-y-4">
                        @foreach ([
                            ['Emergency Contact', null],
                            ['Maps', null],
                            ['Useful links', null],
                            ['Profile', null],
                        ] as [$label,$href])
                            @php
                                // Disable shortcuts that you haven't wired yet
                                $isDisabled = is_null($href);
                            @endphp
                            @if($isDisabled)
                                <span class="block text-center text-xs bg-gray-100 border rounded-lg p-3 text-gray-400 cursor-not-allowed" aria-disabled="true" title="Coming soon">{{ $label }}</span>
                            @else
                                <a href="{{ $href }}" class="block text-center text-xs bg-gray-100 hover:bg-gray-200 border rounded-lg p-3">{{ $label }}</a>
                            @endif
                        @endforeach
                        <div class="hidden sm:block h-full w-px bg-gray-300 mx-auto"></div>
                    </aside>

                    {{-- Level rows + tiles --}}
                    <div class="col-span-12 sm:col-span-10 space-y-6">
                        {{-- Empty state when no course --}}
                        @unless($hasCourse)
                            <div class="rounded border p-6 bg-gray-50">
                                <h3 class="font-semibold">No course yet</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    You’re not enrolled in any course. Please contact your lecturer or admin to be enrolled.
                                </p>
                            </div>
                        @endunless

                        @foreach($levels as $lv)
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                @foreach($tiles as $t)
                                    @php
                                        $isUpload = $t['key'] === 'upload';

                                        // Build hrefs ONLY if we have a course; otherwise keep disabled
                                        if ($hasCourse) {
                                            if ($isUpload) {
                                                // student.assignments.index likely needs {course}
                                                $href = route('student.assignments.index', $course) . '?level=' . $lv;
                                            } else {
                                                // student.materials.byTypeLevel expects {course}, {type}, {level?}
                                                $href = route('student.materials.byTypeLevel', ['course' => $course, 'type' => $t['key'], 'level' => $lv]);
                                            }
                                        } else {
                                            $href = null; // disabled tile
                                        }
                                    @endphp

                                    @if($href)
                                        <a href="{{ $href }}" class="rounded-xl shadow hover:shadow-md transition p-4 {{ $t['bg'] }}">
                                            <p class="text-xs font-semibold text-gray-800">LEVEL {{ $lv }}</p>
                                            <p class="mt-1 font-semibold text-gray-900">{{ $t['label'] }}</p>
                                        </a>
                                    @else
                                        <div class="rounded-xl shadow p-4 {{ $t['bg'] }} opacity-60 cursor-not-allowed" aria-disabled="true" title="Enroll in a course to access this">
                                            <p class="text-xs font-semibold text-gray-800">LEVEL {{ $lv }}</p>
                                            <p class="mt-1 font-semibold text-gray-900">{{ $t['label'] }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
</x-layout>