<x-layout title="Materials">
<div class="max-w-6xl mx-auto px-4 py-6 space-y-6">
    {{-- Breadcrumbs --}}
  <!--breadcrumbs !important to add--> 
  <nav class="mb-6 text-sm text-gray-600" aria-label="Breadcrumb">
    <ol class="list-reset flex">
      <li>
        <a href="{{ route('student.dashboard') }}" class="hover:underline">Dashboard</a>
        <span class="mx-2">/</span>
      </li>
      <li>
        <a href="{{ route('lecturer.courses.show', $course) }}{{ request('type') || request('level') ? '?' . http_build_query(array_filter(['type'=>request('type'),'level'=>request('level')])) : '' }}" class="hover:underline font-semibold">
          {{ str(request('type') ?? $type)->replace('_', ' ')->title() }}
        </a>
      </li>
    </ol>
  </nav>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl shadow p-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
        <div>
            <label class="block text-sm text-gray-700 mb-1">Type</label>
            <select name="type" class="w-full rounded border-gray-300">
                <option value="">All</option>
                @foreach (['lesson'=>'Lesson','worksheet'=>'Worksheet','self-study'=>'Self‑study'] as $val => $label)
                    <option value="{{ $val }}" @selected(($filters['type'] ?? '') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-700 mb-1">Level</label>
            <input type="number" name="level" min="1" class="w-full rounded border-gray-300"
                   value="{{ $filters['level'] ?? '' }}" placeholder="e.g., 1">
        </div>
        <div class="sm:col-span-2 flex items-end gap-3">
            <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Apply</button>
            <a href="{{ route('student.materials.index', $course) }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">Reset</a>
        </div>
    </form>

    {{-- List --}}
    <div class="bg-white rounded-xl shadow">
        <ul class="divide-y divide-gray-100">
            @forelse ($materials as $m)
                <li class="p-4 flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="font-medium truncate">{{ $m->title }}</p>
                        <p class="text-sm text-gray-500">
                            {{ ucfirst($m->type) }} @if($m->level) • Level {{ $m->level }} @endif
                            @if($m->uploaded_at) • {{ $m->uploaded_at->format('M d, Y') }} @endif
                        </p>
                        @if($m->descriptions)
                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $m->descriptions }}</p>
                        @endif
                    </div>
                    <div class="shrink-0 flex items-center gap-3">
                        @if($m->url)
                            <a href="{{ $m->url }}" target="_blank" class="text-blue-600 hover:underline text-sm">Open Link</a>
                        @elseif($m->file_path)
                            <a href="{{ route('student.materials.download', $m) }}" class="text-blue-600 hover:underline text-sm">Download</a>
                        @endif
                    </div>
                </li>
            @empty
                <li class="p-8 text-center text-gray-500 text-sm">No materials found.</li>
            @endforelse
        </ul>
        <div class="px-4 py-3 border-t">
            {{ $materials->links() }}
        </div>
    </div>
</div>
</x-layout>
