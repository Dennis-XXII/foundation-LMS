{{-- resources/views/lecturer/materials/index.blade.php --}}
<x-layout>
  <!--breadcrumbs !important to add--> 
  <nav class="mb-6 text-sm text-gray-600" aria-label="Breadcrumb">
    <ol class="list-reset flex">
      <li>
        <a href="{{ route('lecturer.dashboard') }}" class="hover:underline">Dashboard</a>
        <span class="mx-2">/</span>
      </li>
      <li>
        <a href="{{ route('lecturer.courses.materials.index', $course) }}{{ request('type') || request('level') ? '?' . http_build_query(array_filter(['type'=>request('type'),'level'=>request('level')])) : '' }}" class="hover:underline font-semibold">
          {{ str(request('type') ?? $type)->replace('_', ' ')->title() }}
        </a>
      </li>
    </ol>
  </nav>
  <!--breadcrumbs end-->
  
  <div class="max-w-8xl mx-auto p-3">
    <!-- material upload header --> 
    @php
      $levelColors = [
      3 => 'bg-cyan-300',
      2 => 'bg-green-200',
      1 => 'bg-rose-200',
      ];
      $headerColor = $levelColors[$level ?? null] ?? 'bg-gray-100';
    @endphp
    <div class="flex items-center justify-between p-4 rounded-lg {{ $headerColor }}">
      <div>
        <h1 class="text-2xl font-semibold "> {{ ucfirst(str_replace('_', ' ', $type ?? 'All Materials')) }} </h1>
        <h1 class="text-xl font-thin">{{ $course->code }} {{ $course->name }} level {{ $level ?? '—' }}</h1>
      </div>
      <a
        href="{{ route('lecturer.courses.materials.create', [
            'course' => $course,
            // query params:
            'type'   => $type,
            'level'  => $level,
        ]) }}"
        class="px-3 py-2 rounded bg-black text-white"
      >
        Add Material
      </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mt-4 flex gap-3 items-end">
      <div>
        <label class="block text-sm text-gray-600">Type</label>
        <select name="type" class="block border rounded py-2.5 px-2 text-xs w-full text-center">
          @foreach (['lesson' => 'Lesson Materials', 'worksheet' => 'Worksheet', 'self_study' => 'Self‑study'] as $val => $label)
            <option value="{{ $val }}" @selected($type === $val)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-sm text-gray-600">Level</label>
        <select name="level" class="block border rounded py-2.5 px-2 text-xs w-full text-center">
          @foreach ([1,2,3] as $lv)
            <option value="{{ $lv }}" @selected($level == $lv)>{{ $lv }}</option>
          @endforeach
        </select>
      </div>
      <button class="px-3 py-2 rounded bg-red-600 text-white">Apply</button>
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
