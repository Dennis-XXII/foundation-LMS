<x-layout>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">FOUNDATION PROGRAM MOODLE</h1>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="px-4 py-2 bg-purple-900 text-white rounded">Log Out</button>
        </form>
    </div>

    {{-- Flashes / Errors --}}
    @if(session('success'))
        <div class="mb-4 bg-green-50 text-green-800 border border-green-200 px-4 py-2 rounded">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="mb-4 bg-blue-50 text-blue-800 border border-blue-200 px-4 py-2 rounded">{{ session('info') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 bg-rose-50 text-rose-800 border border-rose-200 px-4 py-2 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Course header card --}}
    <section class="bg-white rounded-lg shadow border">
        <div class="flex items-center justify-between bg-purple-900 text-white rounded-t-lg px-6 py-4">
            <h2 class="text-lg font-semibold">
                {{ $course->code ?? 'COURSE' }} {{ $course->name ?? '' }}
            </h2>
            <div class="flex gap-3">
                {{-- Students management --}}
                <a href="{{ route('lecturer.courses.students.index', $course) }}"
                   class="px-4 py-2 bg-green-600 hover:bg-green-700 rounded">
                    Add students to this course
                </a>
                {{-- Edit course --}}
                <a href="{{ route('lecturer.courses.edit', $course) }}"
                   class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded">
                    Edit
                </a>
            </div>
        </div>

        <div class="p-6 flex gap-6">
            <aside class="w-40 space-y-4">
                @foreach (['Emergency Contact','Maps','Useful links','Profile'] as $leftNav)
                    <button class="w-full bg-gray-100 border rounded p-4 text-sm hover:bg-gray-50">{{ $leftNav }}</button>
                @endforeach
            </aside>

            {{-- Tiles --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 flex-1">
                @php
                    $tile = fn($label,$type,$level,$color) => ['label'=>$label,'type'=>$type,'level'=>$level,'color'=>$color];
                    $tiles = [
                        // L3
                        $tile('Lesson Materials','lesson',3,'bg-cyan-200'),
                        $tile('Worksheets','worksheet',3,'bg-cyan-300'),
                        $tile('Self-study','self_study',3,'bg-cyan-200'),
                        $tile('Upload Links','upload',3,'bg-cyan-300'),
                        // L2
                        $tile('Lesson Materials','lesson',2,'bg-green-200'),
                        $tile('Worksheets','worksheet',2,'bg-green-200'),
                        $tile('Self-study','self_study',2,'bg-green-200'),
                        $tile('Upload Links','upload',2,'bg-green-200'),
                        // L1
                        $tile('Lesson Materials','lesson',1,'bg-rose-200'),
                        $tile('Worksheets','worksheet',1,'bg-rose-200'),
                        $tile('Self-study','self_study',1,'bg-rose-200'),
                        $tile('Upload Links','upload',1,'bg-rose-200'),
                    ];
                @endphp

                @foreach ($tiles as $t)
                    @php
                        // Materials: nested collection route (because of ->shallow())
                        $materialsHref = route('lecturer.courses.materials.index', $course)
                            .'?type='.$t['type'].'&level='.$t['level'];

                        // Assignments: nested collection route (because of ->shallow())
                        $assignIndex  = route('lecturer.courses.assignments.index', $course).'?level='.$t['level'];
                        // Assess entry: send to assignments list (no assessments index exists)
                        $assessEntry  = $assignIndex.'&tab=assess';
                    @endphp

                    @if($t['type'] !== 'upload')
                        <a href="{{ $materialsHref }}"
                           class="block rounded-lg p-5 border hover:shadow {{ $t['color'] }}">
                            <div class="text-xs text-gray-600 mb-1">LEVEL {{ $t['level'] }}</div>
                            <div class="text-lg font-semibold">{{ $t['label'] }}</div>
                        </a>
                @else
                    {{-- Single tile → go to Assignments index; lecturer chooses inside --}}
                    <a href="{{ route('lecturer.courses.assignments.index', $course) }}?level={{ $t['level'] }}"
                    class="block rounded-lg p-5 border hover:shadow {{ $t['color'] }}">
                        <div class="text-xs text-gray-600 mb-1">LEVEL {{ $t['level'] }}</div>
                        <div class="text-lg font-semibold">{{ $t['label'] }}</div>
                        <p class="mt-2 text-sm text-gray-600">Create links and assess submissions</p>
                    </a>
                @endif

                @endforeach
            </div>
        </div>
    </section>

    {{-- Announcements --}}
    <section class="mt-10 bg-white rounded-lg shadow border">
        <div class="bg-purple-900 text-white rounded-t-lg px-6 py-4 text-lg font-semibold">
            Make Announcement
        </div>

        <form class="p-6 space-y-4" method="POST"
              action="{{ route('lecturer.announcements.store') }}"
              enctype="multipart/form-data">
            @csrf
            <input type="text" name="title" placeholder="Title" class="w-full border rounded px-4 py-2" required>
            <textarea name="description" rows="3" placeholder="Write something..." class="w-full border rounded px-4 py-2"></textarea>
            <div class="flex items-center gap-3">
                <input type="file" name="file" class="border rounded px-4 py-2">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_global" value="1">
                    <span>Global</span>
                </label>
                <button class="ml-auto px-4 py-2 bg-blue-600 text-white rounded">Post</button>
            </div>
        </form>

        <div class="px-6 pb-6">
            <ul class="space-y-2">
                @forelse ($announcements ?? [] as $a)
                    <li class="flex items-center justify-between">
                        <span class="truncate">{{ $a->title }}</span>
                        <span class="flex gap-2">
                            <a href="{{ route('lecturer.announcements.edit', $a) }}"
                               class="text-blue-600 hover:underline text-sm">Edit</a>
                            <form method="POST" action="{{ route('lecturer.announcements.destroy', $a) }}"
                                  onsubmit="return confirm('Delete this announcement?')">
                                @csrf @method('DELETE')
                                <button class="text-rose-600 hover:underline text-sm">Delete</button>
                            </form>
                        </span>
                    </li>
                @empty
                    <li class="text-gray-500">No announcements yet.</li>
                @endforelse
            </ul>
        </div>
    </section>
</x-layout>
