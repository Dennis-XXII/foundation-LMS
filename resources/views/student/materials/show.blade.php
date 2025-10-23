{{-- resources/views/student/materials/show.blade.php --}}
<x-layout>
  <nav class="mb-6 text-sm text-gray-600" aria-label="Breadcrumb">
    <ol class="list-reset flex">
      <li>
        <a href="{{ route('student.dashboard') }}" class="hover:underline">Dashboard</a>
        <span class="mx-2">/</span>
      </li>
      <li>
        <a href="{{ route('student.materials.index', $material->course) }}" class="hover:underline">
          Materials
        </a>
        <span class="mx-2">/</span>
      </li>
      <li class="text-black font-semibold">
        {{ $material->title }}
      </li>
    </ol>
  </nav>
  <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow border">
    
    {{-- Header (No Edit/Delete buttons) --}}
    <div class="flex items-center justify-between pb-4 border-b">
      <div>
        <h1 class="text-3xl font-bold">{{ $material->title }}</h1>
        <p class="text-lg text-gray-600">{{ $material->course->code }} — {{ $material->course->name }}</p>
      </div>
    </div>

    {{-- Details Grid --}}
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
      
      {{-- Column 1 --}}
      <div class="md:col-span-2 space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-500">Description</label>
          <div class="mt-1 p-3 min-h-[100px] text-gray-800 bg-gray-50 rounded border">
            {!! nl2br(e($material->descriptions)) !!}
          </div>
        </div>

        @if ($material->file_path)
          <div>
            <label class="block text-sm font-medium text-gray-500">File</label>
            <a href="{{ route('student.materials.download', $material) }}" class="mt-1 text-blue-600 hover:underline font-medium">
              Download Attached File
            </a>
          </div>
        @endif
        
        @if ($material->url)
          <div>
            <label class="block text-sm font-medium text-gray-500">External Link</label>
            <a href="{{ $material->url }}" target="_blank" rel="noopener noreferrer" class="mt-1 text-blue-600 hover:underline font-medium break-all">
              {{ $material->url }}
            </a>
          </div>
        @endif
      </div>

      {{-- Column 2 (Sidebar) --}}
      <div class="space-y-4">
        {{-- "Status" block removed --}}
        <div>
          <label class="block text-sm font-medium text-gray-500">Type</label>
          <p class="mt-1 text-gray-900">{{ str($material->type)->replace('_', ' ')->title() }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-500">Level</label>
          <p class="mt-1 text-gray-900">{{ $material->level ?? 'All Levels' }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-500">Week</label>
          <p class="mt-1 text-gray-900">{{ $material->week ?? '—' }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-500">Day</label>
          <p class="mt-1 text-gray-900">{{ $material->day ?? '—' }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-500">Uploaded</label>
          <p class="mt-1 text-gray-900">{{ optional($material->uploaded_at)->format('M d, Y') ?? '—' }}</p>
        </div>
      </div>
    </div>
    
    <div class="mt-8 pt-4 border-t">
        <a href="{{ route('student.materials.index', [
                    'course' => $course,
                    'level' => $level,
                    'type' => $type,
                    ]) }}" class="px-4 py-2 rounded border text-sm">
            &larr; Back to All Materials
        </a>
    </div>

  </div>
</x-layout>