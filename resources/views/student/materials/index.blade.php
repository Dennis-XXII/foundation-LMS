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
      // Use level filter for header color, default to gray-100
      $headerColor = $levelColors[$level ?? null] ?? 'bg-gray-100';
    @endphp
    <div class="flex items-center justify-between p-4 rounded-lg {{ $headerColor }}">
      <div>
        <h1 class="text-2xl font-semibold">
            {{ ucfirst(str_replace('_', ' ', $type ?? 'All Materials')) }}
        </h1>
        <h2 class="text-xl font-thin">{{ $course->code }} {{ $course->name }}</h2>
        {{-- Show the student's enrolled level --}}
        @if ($student_level)
            <p class="mt-2 text-sm">Your enrolled level: 
                <span class="font-bold px-2 py-1 text-xs rounded-full {{ $levelColors[$student_level] ?? 'bg-gray-200' }}">
                    Level {{ $student_level }}
                </span>
            </p>
        @endif
      </div>
      {{-- No "Add Material" button for students --}}
    </div>

    {{-- Filters (Students can use these to override dashboard links) --}}
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
            {{-- Students should only see their level or lower --}}
            @if ($lv <= $student_level)
                <option value="{{ $lv }}" @selected($level == $lv)>Level {{ $lv }}</option>
            @endif
          @endforeach
        </select>
      </div>
      
      {{-- These inputs ensure week/day filters persist when changing type/level --}}
      @if($week) <input type="hidden" name="week" value="{{ $week }}"> @endif
      @if($day) <input type="hidden" name="day" value="{{ $day }}"> @endif
      
      <button class="px-3 py-2 rounded bg-red-600 text-white">Apply Type/Level</button>
      
      {{-- Clear Filters Link --}}
      @if ($type || $level || $week || $day)
        <a href="{{ route('student.materials.index', $course) }}" class="text-sm text-blue-600 hover:underline">Clear All Filters</a>
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
        
        @if(session('success'))
          <div class="mt-4 p-3 rounded bg-green-50 text-green-700">{{ session('success') }}</div>
        @endif
        
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
                <th class="px-3 py-2 text-left">Link</th>
              </tr>
            </thead>
            <tbody>
              @forelse($materials as $m)
                <tr class="border-t">
                  <td class="px-3 py-2">{{ $m->title }}</td>
                  <td class="px-3 py-2">{{ str($m->type)->replace('_',' ')->title() }}</td>
                  <td class="px-3 py-2">{{ $m->level ?? '—' }}</td>
                  <td class="px-3 py-2">{{ $m->week ?? '—' }}</td>
                  <td class="px-3 py-2">{{ $m->day ?? '—' }}</td>
                  <td class="px-3 py-2">{{ optional($m->uploaded_at)->format('M d, Y') }}</td>
                  <td class="px-3 py-2 whitespace-nowrap">
                    @if($m->file_path)
                      <a class="text-blue-600 underline" href="{{ route('student.materials.download', $m) }}">Download</a>
                    @elseif($m->url)
                      <a class="text-blue-600 underline" href="{{ $m->url }}" target="_blank" rel="noopener">View Link</a>
                    @else
                      <span class="text-gray-400">N/A</span>
                    @endif
                  </td>
                </tr>
              @empty
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