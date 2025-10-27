{{-- resources/views/student/materials/index.blade.php --}}
<x-layout>
  <nav class="mb-6 text-sm text-gray-600" aria-label="Breadcrumb">
    <ol class="list-reset flex">
      <li>
        <a href="{{ route('student.dashboard') }}" class="hover:underline">Dashboard</a>
        <span class="mx-2">/</span>
      </li>
      <li>
        <a href="{{ route('lecturer.courses.materials.index', $course) }}{{ request('type') || request('level') ? '?' . http_build_query(array_filter(['type'=>request('type'),'level'=>request('level')])) : '' }}" class="hover:underline font-semibold">
          {{ str(request('type') ?? $type)->replace('_', ' ')->title() }}
        </a>
      </li>
    </ol>
  </nav>
  <div class="max-w-8xl mx-auto p-3">
    @php
      $levelColors = [
        3 => 'bg-[#9bd1f8]',
        2 => 'bg-[#c7f7cf]',
        1 => 'bg-[#f0c6bc]',
      ];
      // Use level filter for header, default to gray
      $headerColor = $levelColors[$level ?? null] ?? 'bg-gray-100';
    @endphp
    
    {{-- Header (No "Add Material" button) --}}
    <div class="flex items-center justify-between p-4 rounded-lg {{ $headerColor }}">
      <div>
        <h1 class="text-2xl font-semibold "> {{ ucfirst(str_replace('_', ' ', $type ?? 'All Materials')) }} </h1>
        <h1 class="text-xl font-thin">{{ $course->code }} {{ $course->name }}</h1>
      </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mt-4 flex flex-wrap gap-3 items-end">
      {{-- Type Filter --}}
      <div>
        <label class="block text-sm text-gray-600">Type</label>
        <select name="type" class="block border rounded py-2.5 px-2 text-xs w-full text-center">
           <option value="">All Types</option>
          @foreach (['lesson' => 'Lesson Materials', 'worksheet' => 'Worksheet', 'self_study' => 'Self‑study'] as $val => $label)
            <option value="{{ $val }}" @selected($type === $val)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      {{-- Level Filter --}}
      <div>
        <label class="block text-sm text-gray-600">Level</label>
        <select name="level" class="block border rounded py-2.5 px-2 text-xs w-full text-center">
          <option value="">All Levels</option>
          @foreach ([1,2,3] as $lv)
            <option value="{{ $lv }}" @selected($level == $lv)>{{ $lv }}</option>
          @endforeach
        </select>
      </div>
      
      {{-- Submitting this form clears week/day --}}
      <button class="px-3 py-2 rounded bg-red-600 text-white">Apply Type/Level</button>
      
      {{-- Clear Filters Link --}}
        @if ($level || $week || $day)
            <a href="{{ route('student.materials.index',[
                    'course' => $course,
                    'level' => $level,
                    'type' => $type,
                    ]) }}" class="text-sm text-blue-600 hover:underline">View All Materials for Level {{ $level }}, Type {{ $type }}</a>
        @endif
    </form>

    {{-- Week/Day Navigation Grid --}}
    @php
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'REVIEW'];
    @endphp
    <div class="mt-6 overflow-x-auto">
      <table class="min-w-full text-lg">
        <tbody class="bg-white">
          @for ($w = 1; $w <= 8; $w++)
            <tr class="border-b border-gray-200">
              <td class="px-3 py-2">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                  
                  <span class="font-bold text-blue-700">Week {{ $w }}:</span>

                  @foreach ($days as $dayName)
                    {{-- This URL logic is route-agnostic and works perfectly --}}
                    <a href="{{ request()->fullUrlWithQuery(['week' => $w, 'day' => $dayName]) }}"
                       @class([
                            'font-bold',
                            'underline' => ($week == $w && $day == $dayName), 
                            'hover:underline' => !($week == $w && $day == $dayName),
                            'text-purple-700' => $dayName === 'REVIEW',
                            'text-red-800' => $dayName !== 'REVIEW',
                       ])>
                      {{ $dayName }}
                    </a>
                  @endforeach

                </div>
              </td>
            </tr>
          @endfor
        </tbody>
      </table>
    </div>

    {{-- Materials List --}}
    <div class="mt-8">
        <h2 class="text-xl font-semibold">
            @if ($week && $day)
                Materials for: Week {{ $week }}, {{ $day }}
            @elseif ($type || $level)
                Filtered Materials
            @else
                All Materials
            @endif
        </h2>
        
        <div class="mt-4 overflow-x-auto">
          <table class="min-w-full text-sm border">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-3 py-2 text-left">Title</th>
                <th class="px-3 py-2 text-left">Type</th>
                <th class="px-3 py-2 text-left">Level</th>
                <th class="px-3 py-2 text-left">Week</th>
                <th class="px-3 py-2 text-left">Day</th>
                <th class="px-3 py-2 text-left">Uploaded</th>
                {{-- "Published" column removed --}}
                <th class="px-3 py-2 text-left">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($materials as $m)
                <tr class="border-t">
                  <td class="px-3 py-2">
                    {{-- THIS IS THE UPDATED LINE --}}
                    <a class="text-blue-600 underline" href="{{ route('student.materials.show', $m) }}">{{ $m->title }}</a>
                  </td>
                  <td class="px-3 py-2">{{ str($m->type)->replace('_',' ')->title() }}</td>
                  <td class="px-3 py-2">{{ $m->level ?? '—' }}</td>
                  <td class="px-3 py-2">{{ $m->week ?? '—' }}</td>
                  <td class="px-3 py-2">{{ $m->day ?? '—' }}</td>
                  <td class="px-3 py-2">{{ optional($m->uploaded_at)->format('Y-m-d') }}</td>
                  {{-- "Published" td removed --}}
                  <td class="px-3 py-2 space-x-3 whitespace-nowrap">
                    {{-- Student-specific actions --}}
                    @if($m->url)
                      <a href="{{ $m->url }}" target="_blank" class="text-blue-600 hover:underline">Open Link</a>
                    @endif
                    @if($m->file_path)
                      <a class="text-blue-600 underline" href="{{ route('student.materials.download', $m) }}">Download</a>
                    @endif
                  </td>
                </tr>
              @empty
                {{-- Colspan reduced from 8 to 7 --}}
                <tr><td class="px-3 py-4 text-gray-500" colspan="7">No materials found matching these filters.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
    
        @if ($materials->hasPages())
          <div class="mt-4">{{ $materials->links() }}</div>
        @endif
    </div>
    
  </div>
</x-layout>