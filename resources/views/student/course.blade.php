<x-layout :title="$course->title">
<div class="max-w-6xl mx-auto px-4 py-6 space-y-6">
    {{-- Breadcrumbs --}}
    <nav class="text-sm text-gray-500">
        <a href="{{ route('student.dashboard') }}" class="hover:underline">Dashboard</a>
        <span class="mx-2">/</span>
        <span class="text-gray-700 font-medium">{{ $course->title }}</span>
    </nav>

    {{-- Header --}}
    <div class="bg-white shadow rounded-xl p-6">
        <h1 class="text-2xl font-semibold">{{ $course->title }}</h1>
        <p class="text-gray-600 mt-2">{{ $course->descriptions ?? 'Course overview' }}</p>
        <div class="mt-4 text-sm text-gray-600 flex flex-wrap gap-4">
            <span>Materials: {{ $course->materials_count }}</span>
            <span>Assignments: {{ $course->assignments_count }}</span>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('student.materials.index', $course) }}" class="bg-white rounded-xl shadow p-5 hover:shadow-md">
            <h3 class="font-semibold">Browse Materials</h3>
            <p class="text-sm text-gray-500 mt-1">Lesson • Worksheet • Self‑study</p>
        </a>
        <a href="{{ route('student.assignments.index', $course) }}" class="bg-white rounded-xl shadow p-5 hover:shadow-md">
            <h3 class="font-semibold">Assignments</h3>
            <p class="text-sm text-gray-500 mt-1">Upload & feedback</p>
        </a>
        {{-- Add announcements link if you expose a page --}}
    </div>

    {{-- Latest lists --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="font-semibold">Latest Materials</h3>
            <ul class="divide-y divide-gray-100 mt-4">
                @forelse($course->materials as $m)
                    <li class="py-3 flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="font-medium truncate">{{ $m->title }}</p>
                            <p class="text-sm text-gray-500">
                                {{ ucfirst($m->type) }} @if($m->level) • Level {{ $m->level }} @endif
                                @if($m->uploaded_at) • {{ $m->uploaded_at->diffForHumans() }} @endif
                            </p>
                        </div>
                        @if($m->url || $m->file_path)
                            <a href="{{ $m->url ?: route('file.download', $m) }}" class="text-sm text-blue-600 hover:underline">Open</a>
                        @endif
                    </li>
                @empty
                    <li class="py-6 text-gray-500 text-sm">No materials yet.</li>
                @endforelse
            </ul>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="font-semibold">Latest Assignments</h3>
            <ul class="divide-y divide-gray-100 mt-4">
                @forelse($course->assignments as $a)
                    <li class="py-3 flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="font-medium truncate">{{ $a->title }}</p>
                            <p class="text-sm text-gray-500">
                                @if($a->due_at) Due {{ $a->due_at->diffForHumans() }} @else No due date @endif
                            </p>
                        </div>
                        <a href="{{ route('student.assignments.index', $course) }}" class="text-sm text-blue-600 hover:underline">Submit</a>
                    </li>
                @empty
                    <li class="py-6 text-gray-500 text-sm">No assignments yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
</x-layout>
