<x-layout title="Student Dashboard">
@php
    $levels = [3,2,1];
    $tiles = [
        ['key'=>'lesson','label'=>'Lesson Materials','bg'=>'bg-sky-300'],
        ['key'=>'worksheet','label'=>'Worksheets','bg'=>'bg-cyan-300'],
        ['key'=>'self-study','label'=>'Self-study','bg'=>'bg-cyan-400'],
        ['key'=>'upload','label'=>'Upload Links','bg'=>'bg-cyan-300'],
    ];
@endphp

<div class="max-w-6xl mx-auto px-4 py-6 space-y-8">
    {{-- My Courses --}}
    <section class="space-y-4">
        <h2 class="text-lg font-semibold">My Courses</h2>

        <div class="bg-white rounded-xl shadow">
            <div class="rounded-t-xl px-6 py-4 bg-purple-900 text-white font-semibold text-lg">
                {{ strtoupper($course->title ?? 'FOUNDATION AUGUST 2025') }}
            </div>

            <div class="p-4 md:p-6">
                <div class="grid grid-cols-12 gap-4">
                    {{-- Left shortcuts --}}
                    <aside class="col-span-12 sm:col-span-2 space-y-4">
                        @foreach ([
                            ['Emergency Contact', '#'],
                            ['Maps', '#'],
                            ['Useful links', '#'],
                            ['Profile', '#'],
                        ] as [$label,$href])
                            <a href="{{ $href }}" class="block text-center text-xs bg-gray-100 hover:bg-gray-200 border rounded-lg p-3">
                                {{ $label }}
                            </a>
                        @endforeach
                        <div class="hidden sm:block h-full w-px bg-gray-300 mx-auto"></div>
                    </aside>

                    {{-- Level rows + tiles --}}
                    <div class="col-span-12 sm:col-span-10 space-y-6">
                        @foreach($levels as $lv)
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                @foreach($tiles as $t)
                                    @php
                                        $isUpload = $t['key'] === 'upload';
                                        $href = $isUpload
                                            ? route('student.assignments.index', $course) . '?level=' . $lv
                                            : route('student.materials.byTypeLevel', [$course, $t['key'], $lv]);
                                    @endphp

                                    <a href="{{ $href }}" class="rounded-xl shadow hover:shadow-md transition p-4 {{ $t['bg'] }}">
                                        <p class="text-xs font-semibold text-gray-800">LEVEL {{ $lv }}</p>
                                        <p class="mt-1 font-semibold text-gray-900">{{ $t['label'] }}</p>
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Announcements --}}
    <section class="space-y-4">
        <h2 class="text-lg font-semibold">Announcements</h2>
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-6 py-4 bg-purple-900 text-white font-semibold text-lg">
                {{ strtoupper($course->title ?? 'FOUNDATION AUGUST 2025') }}
            </div>
            <ul class="divide-y divide-gray-200">
                @forelse(($announcements ?? []) as $a)
                    <li class="px-6 py-4 flex items-center justify-between">
                        <a href="{{ route('student.courses.show', $course) }}#ann-{{ $a->id }}" class="text-blue-700 hover:underline">
                            {{ $a->title }}
                        </a>
                        <span class="text-sm text-gray-600">
                            {{ optional($a->published_at ?? $a->created_at)->format('jS F Y') }}
                        </span>
                    </li>
                @empty
                    <li class="px-6 py-4 flex items-center justify-between"><span>No announcements yet.</span></li>
                @endforelse
            </ul>
        </div>
    </section>
</div>
</x-layout>
