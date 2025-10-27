{{-- resources/views/lecturer/materials/show.blade.php --}}
<x-layout>
  <nav class="mb-6 text-sm text-gray-600" aria-label="Breadcrumb">
    <ol class="list-reset flex">
      <li>
        <a href="{{ route('lecturer.dashboard') }}" class="hover:underline">Dashboard</a>
        <span class="mx-2">/</span>
      </li>
      <li>
        <a href="{{ route('lecturer.courses.materials.index', $material->course) }}{{ request('type') || request('level') ? '?' . http_build_query(array_filter(['type'=>request('type'),'level'=>request('level')])) : '' }}" class="hover:underline font-semibold">
          {{ str(request('type') ?? $material->type)->replace('_', ' ')->title() }} {{ str(request('level') ? ' - Level ' . request('level') : ($material->level ? ' - Level ' . $material->level : ''))}}
        </a>
        <span class="mx-2">/</span>
      </li>
      <li class="text-black font-semibold">
        {{ $material->title }}
      </li>
    </ol>
  </nav>
  <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow border">
    
    {{-- Header with Edit/Delete buttons --}}
    <div class="flex items-center justify-between pb-4 border-b">
      <div>
        <h1 class="text-3xl font-bold">{{ $material->title }}</h1>
        <p class="text-lg text-gray-600">{{ $material->course->code }} — {{ $material->course->name }}</p>
      </div>
      <div class="flex gap-2">
        <a href="{{ route('lecturer.materials.edit', $material) }}" class="px-4 py-2 rounded bg-blue-600 text-white text-sm font-medium">
          Edit
        </a>
        <form method="POST" action="{{ route('lecturer.materials.destroy', $material) }}" onsubmit="return confirm('Are you sure you want to delete this material?');">
          @csrf
          @method('DELETE')
          <button type="submit" class="px-4 py-2 rounded bg-red-600 text-white text-sm font-medium">
            Delete
          </button>
        </form>
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
            <a href="{{ route('lecturer.materials.download', $material) }}" class="mt-1 text-blue-600 hover:underline font-medium">
              Download Attached File
            </a>
          </div>
        @endif
        
        @if ($material->url)
          <div>
            <label class="block text-sm font-medium text-gray-500">External Link</label>
            <a href="{{ $material->url }}" target="_blank" rel="noopener noreferrer" class="mt-1 text-blue-600 hover:underline font-medium">
              {{ $material->url }}
            </a>
          </div>
        @endif
      </div>

      {{-- Column 2 (Sidebar) --}}
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-500">Status</label>
          @if ($material->is_published)
            <span class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
              Published
            </span>
          @else
            <span class="mt-1 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
              Draft
            </span>
          @endif
        </div>
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
        <a href="{{ route('lecturer.courses.materials.index', $material->course) }}" class="px-4 py-2 rounded border text-sm">
            &larr; Back to All Materials
        </a>
    </div>

  </div>
</x-layout>