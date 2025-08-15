{{-- resources/views/lecturer/materials/index.blade.php --}}
<x-layout>
  <div class="max-w-6xl mx-auto p-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold">Materials — {{ $course->code }} {{ $course->name }}</h1>
        <p class="text-sm text-gray-600">Filter by type/level; click a title to edit.</p>
      </div>
      <a href="{{ route('lecturer.courses.materials.create', $course) }}" class="px-3 py-2 rounded bg-black text-white">Add Material</a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mt-4 flex gap-3 items-end">
      <div>
        <label class="block text-xs text-gray-600">Type</label>
        <select name="type" class="border rounded px-3 py-2">
          <option value="">All</option>
          @foreach (['lesson' => 'Lesson', 'worksheet' => 'Worksheet', 'self_study' => 'Self‑study'] as $val => $label)
            <option value="{{ $val }}" @selected($type === $val)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-600">Level</label>
        <select name="level" class="border rounded px-3 py-2">
          <option value="">All</option>
          @foreach ([1,2,3] as $lv)
            <option value="{{ $lv }}" @selected($level == $lv)>{{ $lv }}</option>
          @endforeach
        </select>
      </div>
      <button class="px-3 py-2 rounded border">Apply</button>
    </form>

    {{-- Flash --}}
    @if(session('success'))
      <div class="mt-4 p-3 rounded bg-green-50 text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Table --}}
    <div class="mt-4 overflow-x-auto">
      <table class="min-w-full text-sm border">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2 text-left">Title</th>
            <th class="px-3 py-2 text-left">Type</th>
            <th class="px-3 py-2 text-left">Level</th>
            <th class="px-3 py-2 text-left">Uploaded</th>
            <th class="px-3 py-2 text-left">Published</th>
            <th class="px-3 py-2 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($materials as $m)
            <tr class="border-t">
              <td class="px-3 py-2">
                <a class="text-blue-600 underline" href="{{ route('lecturer.materials.edit', $m) }}">{{ $m->title }}</a>
              </td>
              <td class="px-3 py-2">{{ str($m->type)->replace('_',' ')->title() }}</td>
              <td class="px-3 py-2">{{ $m->level ?? '—' }}</td>
              <td class="px-3 py-2">{{ optional($m->uploaded_at)->format('Y-m-d') }}</td>
              <td class="px-3 py-2">
                <span class="inline-flex px-2 py-0.5 rounded text-xs {{ $m->is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                  {{ $m->is_published ? 'Yes' : 'No' }}
                </span>
              </td>
              <td class="px-3 py-2 space-x-3">
                @if($m->file_path)
                  <a class="text-blue-600 underline" href="{{ route('lecturer.materials.download', $m) }}">Download</a>
                @endif
                <form class="inline" method="POST" action="{{ route('lecturer.materials.destroy', $m) }}">
                  @csrf @method('DELETE')
                  <button class="text-red-600 underline" onclick="return confirm('Delete material?')">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td class="px-3 py-4 text-gray-500" colspan="6">No materials yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $materials->links() }}</div>
  </div>
</x-layout>
