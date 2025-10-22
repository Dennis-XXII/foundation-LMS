{{-- resources/views/lecturer/assignments/index.blade.php --}}
<x-layout>
    @php
        $level = (int) request('level');
        // $tab and $isAssess REMOVED
        $levelLabel = $level ? ('LEVEL '.$level) : null;
        $week = (int) request('week');
        $day = request('day');
    @endphp

    <nav class="mb-6 text-sm text-gray-600" aria-label="Breadcrumb">
      <ol class="list-reset flex">
        <li>
          <a href="{{ route('lecturer.dashboard') }}" class="hover:underline">Dashboard</a>
          <span class="mx-2">/</span>
        </li>
        <li class="text-black font-semibold">
          Upload Links
        </li>
      </ol>
    </nav>
    {{-- Top options: Post / Assess --}}
    <div class="flex items-center justify-center gap-6 mb-8">
        {{-- Current Page (Active style) --}}
        <a href="{{ route('lecturer.courses.assignments.index', $course) }}?level={{ $level }}"
           class="px-6 py-2.5 rounded-lg shadow bg-rose-500 text-white"> {{-- Always active style for this page --}}
            Post Upload Links
        </a>
        {{-- Link to NEW Assessment Page --}}
        <a href="{{ route('lecturer.courses.assessments.index', $course) }}?level={{ $level }}" {{-- UPDATED Route --}}
           class="px-6 py-2.5 rounded-lg shadow bg-blue-200 text-blue-800 hover:bg-blue-300"> {{-- Always inactive style for this page --}}
            Assess Student Uploads
        </a>
    </div>

    {{-- If no level provided, guide the lecturer --}}
    @if(!$level) {{-- Removed !$isAssess check --}}
        <div class="rounded border bg-yellow-50 text-yellow-800 px-4 py-3 mb-6">
            Please select a level from the dashboard to see assignments.
        </div>
    @endif

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 bg-green-50 text-green-800 border border-green-200 px-4 py-2 rounded">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 bg-rose-50 text-rose-800 border border-rose-200 px-4 py-2 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- ===================== POST UPLOAD LINKS (NEW DESIGN) ===================== --}}
    {{-- @if(!$isAssess) REMOVED --}}

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
            <h1 class="text-2xl font-semibold ">Upload Links</h1>
            <h1 class="text-xl font-thin">{{ $course->code }} {{ $course->name }}</h1>
          </div>
          {{-- This is the main "Add" button --}}
          <a
            href="{{ route('lecturer.courses.assignments.create', [
                'course' => $course,
                'level'  => $level, // Pass current level filter
            ]) }}"
            class="px-3 py-2 rounded bg-black text-white"
          >
            Add Upload Link
          </a>
        </div>

        {{-- Filters --}}
        <form method="GET" class="mt-4 flex flex-wrap gap-3 items-end">
          {{-- Level Filter --}}
          <div>
            <label class="block text-sm text-gray-600">Level</label>
            <select name="level" class="block border rounded py-2.5 px-2 text-xs w-full text-center">
              <option value="">All Levels</option>
              @foreach ([1,2,3] as $lv)
                <option value="{{ $lv }}" @selected($level == $lv)>Level {{ $lv }}</option>
              @endforeach
            </select>
          </div>

          {{-- Submit button clears week/day --}}
          <button class="px-3 py-2 rounded bg-red-600 text-white">Apply Level</button>

          {{-- Link to view all for the current level (clears week/day) --}}
          @if ($level || $week || $day)
            <a href="{{ route('lecturer.courses.assignments.index',[
                    'course' => $course,
                    'level' => $level,
                    ]) }}" class="text-sm text-blue-600 hover:underline">View All Assignments for Level {{ $level }}</a>
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
                        {{-- This link preserves the $level filter --}}
                        <a href="{{ request()->fullUrlWithQuery(['week' => $w, 'day' => $dayName, 'level' => $level]) }}"
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

        {{-- Uploaded Links table (now filtered) --}}
        <div class="mt-8">
            {{-- Flex container for title and contextual button --}}
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-semibold">
                  @if ($week && $day)
                      Assignments for: Week {{ $week }}, {{ $day }}
                  @elseif ($level)
                      Assignments for Level {{ $level }}
                  @else
                      All Assignments
                  @endif
              </h2>

              {{-- Contextual "Add" button --}}
              @if ($week && $day && $level)
                <a href="{{ route('lecturer.courses.assignments.create', [
                        'course' => $course,
                        'level'  => $level, // Pass the level filter
                        'week'   => $week,  // Pass the selected week
                        'day'    => $day,   // Pass the selected day
                    ]) }}"
                   class="px-3 py-1.5 rounded bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                    + Add to Week {{ $week }}, {{ $day }}
                </a>
              @endif
            </div>

            <div class="mt-4 overflow-x-auto">
              <table class="min-w-full text-sm border">
                <thead class="bg-gray-50">
                    <tr class="text-sm text-gray-600">
                        <th class="px-6 py-3">Assignment title</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Level</th>
                        <th class="px-6 py-3">Week</th>
                        <th class="px-6 py-3">Day</th>
                        <th class="px-6 py-3">Due Date</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                @forelse($assignments as $a)
                    <tr>
                        <td class="px-6 py-3">
                            <a href="{{ route('lecturer.assignments.show', $a) }}" class="text-blue-600 hover:underline font-medium">
                                {{ $a->title }}
                            </a>
                        </td>
                        <td class="px-6 py-3">
                            @php $active = (bool) $a->is_published; @endphp
                            <span class="px-2 py-1 text-xs rounded {{ $active ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $active ? 'Active' : 'Draft' }}
                            </span>
                        </td>
                        <td class="px-6 py-3">{{ $a->level ?? '—' }}</td>
                        <td class="px-6 py-3">{{ $a->week ?? '—' }}</td>
                        <td class="px-6 py-3">{{ $a->day ?? '—' }}</td>
                        <td class="px-6 py-3">
                            @if($a->due_at)
                                {{ \Illuminate\Support\Carbon::parse($a->due_at)->timezone(config('app.timezone'))->format('d M Y') }}
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                @if(!empty($a->file_path))
                                    <a class="text-blue-700 underline"
                                       href="{{ route('lecturer.assignments.download', $a) }}">Download</a>
                                @endif
                                <button class="text-blue-600 hover:underline"
                                        onclick="location.href='{{ route('lecturer.assignments.edit', $a) }}'">Edit</button>
                                <form method="POST" action="{{ route('lecturer.assignments.destroy', $a) }}"
                                      onsubmit="return confirm('Delete this upload link?');">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-600 hover:underline">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-6 py-6 text-gray-500" colspan="7">
                            No upload links found matching these filters.
                        </td>
                    </tr>
                @endforelse
                </tbody>
              </table>
            </div>

            {{-- Paginator --}}
            @if(method_exists($assignments, 'links'))
                <div class="mt-4 px-6 py-3">{{ $assignments->withQueryString()->links() }}</div>
            @endif
        </div>

    {{-- @else block REMOVED --}}

</x-layout>



