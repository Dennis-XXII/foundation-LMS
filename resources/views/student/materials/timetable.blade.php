{{-- resources/views/student/materials/timetable.blade.php --}}
<x-layout>
  <nav class="mb-6 text-sm text-gray-600" aria-label="Breadcrumb">
    <ol class="list-reset flex">
      <li>
        <a href="{{ route('student.dashboard') }}" class="hover:underline">Dashboard</a>
        <span class="mx-2">/</span>
      </li>
      <li>
        <a href="{{ route('student.materials.index', $course) }}" class="font-semibold hover:underline">
          Materials Timetable
        </a>
      </li>
    </ol>
  </nav>
  <div class="max-w-8xl mx-auto p-3">
    @php
      $levelColors = [
        3 => 'bg-cyan-300',
        2 => 'bg-green-200',
        1 => 'bg-rose-200',
      ];
      // Use student's enrolled level for header, if available. Use filter level as fallback.
      $headerColor = $levelColors[$student_level ?? $level ?? null] ?? 'bg-gray-100'; 
    @endphp
    
    {{-- Header (No "Add Material" button) --}}
    <div class="flex items-center justify-between p-4 rounded-lg {{ $headerColor }}">
      <div>
        <h1 class="text-2xl font-semibold "> {{ ucfirst(str_replace('_', ' ', $type ?? 'Materials Timetable')) }} </h1>
        <h1 class="text-xl font-thin">{{ $course->code }} {{ $course->name }}</h1>
      </div>
    </div>

    {{-- Flash messages for redirection from list page --}}
    @if(session('error')) 
        <div class="mt-4 p-3 rounded bg-red-50 text-red-700">{{ session('error') }}</div> 
    @endif

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
      
      {{-- Submitting this form clears week/day in the query string --}}
      <button class="px-3 py-2 rounded bg-red-600 text-white">Apply Type/Level</button>
      
      {{-- Clear Filters Link --}}
        @if ($level || $type)
            <a href="{{ route('student.materials.index',[
                    'course' => $course,
                    ]) }}" class="text-sm text-blue-600 hover:underline">Clear Filters</a>
        @endif
    </form>

    {{-- Week/Day Navigation Grid (Timetable) --}}
    @php
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'REVIEW'];
    @endphp
    <div class="mt-6 overflow-x-auto">
      <h2 class="text-xl font-semibold mb-3">Select a Date to View Materials</h2>
      <table class="min-w-full text-lg">
        <tbody class="bg-white">
          @for ($w = 1; $w <= 8; $w++)
            <tr class="border-b border-gray-200">
              <td class="px-3 py-2">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                  
                  <span class="font-bold text-blue-700">Week {{ $w }}:</span>

                  @foreach ($days as $dayName)
                    {{-- THIS IS THE CRITICAL CHANGE: Link to the new list route --}}
                    {{-- Pass all existing filters (type, level) plus new time filters --}}
                    <a href="{{ route('student.materials.list', [
                        'course' => $course,
                        'week' => $w, 
                        'day' => $dayName, 
                        'type' => $type, 
                        'level' => $level
                    ]) }}"
                       @class([
                            'font-bold',
                            'hover:underline',
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
  </div>
</x-layout>