{{-- resources/views/student/materials/index.blade.php --}}
<x-layout>
  {{-- Breadcrumbs --}}
  <nav class="mb-6 text-sm text-gray-600" aria-label="Breadcrumb">
    <ol class="list-reset flex">
      <li>
        <a href="{{ route('student.dashboard') }}" class="hover:underline">Dashboard</a>
        <span class="mx-2">/</span>
      </li>
      <li>
        <a href="{{ route('student.materials.index', $course) }}" class="font-semibold hover:underline">
          Materials for {{ $course->code }}
        </a>
      </li>
    </ol>
  </nav>

  <div class="max-w-8xl mx-auto p-3">
    {{-- Header --}}
    @php
      $levelColors = [
          3 => 'bg-cyan-100 text-cyan-800',
          2 => 'bg-green-100 text-green-800',
          1 => 'bg-rose-100 text-rose-800',
      ];
    @endphp
    <div class="flex items-center justify-between p-4 rounded-lg bg-gray-100">
      <div>
        <h1 class="text-2xl font-semibold">Course Materials</h1>
        <h2 class="text-xl font-thin">{{ $course->code }} {{ $course->name }}</h2>
        {{-- Show the student's enrolled level for this course --}}
        @if ($student_level)
            <p class="mt-2 text-sm">Your enrolled level: 
                <span class="font-bold px-2 py-1 text-xs rounded-full {{ $levelColors[$student_level] ?? 'bg-gray-200' }}">
                    Level {{ $student_level }}
                </span>
            </p>
        @endif
      </div>
      {{-- "Add Material" button is removed for students --}}
    </div>

    {{-- Flash Messages (optional, but good practice to keep) --}}
    @if (session('success'))
      <div class="mt-4 p-3 rounded bg-green-50 text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Materials Table --}}
    <div class="mt-4 overflow-x-auto">
      <table class="min-w-full text-sm border">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left font-medium text-gray-600">Title</th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">Type</th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">Level</th>
            <th class="px-4 py-3 text-left font-medium text-gray-600">Uploaded Date</th>
          </tr>
        </thead>
        <tbody>
          @forelse($materials as $material)
            {{-- Highlight the row if material level matches the student's enrolled level --}}
            <tr class="border-t hover:bg-gray-50 {{ $material->level == $student_level ? 'bg-blue-50' : '' }}">
              <td class="px-4 py-3">
                {{-- The title is now the download link --}}
                @if ($material->file_path)
                  <a class="text-blue-600 hover:underline font-semibold" href="{{ route('student.materials.download', $material) }}">
                    {{ $material->title }}
                  </a>
                @else
                  <span class="text-gray-600">{{ $material->title }}</span> 
                  <span class="text-gray-400 text-xs">(No file)</span>
                @endif
              </td>
              <td class="px-4 py-3 text-gray-700">{{ str($material->type)->replace('_', ' ')->title() }}</td>
              <td class="px-4 py-3 text-gray-700">{{ $material->level ?? '—' }}</td>
              <td class="px-4 py-3 text-gray-700">{{ optional($material->uploaded_at)->format('M d, Y') }}</td>
            </tr>
          @empty
            {{-- Updated colspan to 4 since columns were removed --}}
            <tr>
              <td class="px-4 py-4 text-center text-gray-500" colspan="4">No materials have been uploaded for this course yet.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if ($materials->hasPages())
        <div class="mt-4">
            {{ $materials->links() }}
        </div>
    @endif
  </div>
</x-layout>