{{-- resources/views/student/assignments/index.blade.php --}}
<x-layout title="Upload Links">
    @php
        // These variables ($level, $week, $day) are now passed from the controller
        $levelLabel = $level ? ('LEVEL '.$level) : null;
    @endphp

    {{-- Breadcrumbs --}}
    <nav class="mb-6 text-sm text-gray-600" aria-label="Breadcrumb">
      <ol class="list-reset flex">
        <li>
          <a href="{{ route('student.dashboard') }}" class="hover:underline">Dashboard</a>
          <span class="mx-2">/</span>
        </li>
        {{-- Maybe link back to course show page if you have one --}}
        {{-- <li>
          <a href="{{ route('student.courses.show', $course) }}" class="hover:underline">{{ $course->code }}</a>
          <span class="mx-2">/</span>
        </li> --}}
        <li class="text-black font-semibold">
          Upload Links
        </li>
      </ol>
    </nav>

    {{-- Level Guidance/Info --}}
    @if($student_level)
        <div class="rounded border bg-blue-50 text-blue-800 px-4 py-3 mb-6 text-sm">
            You are enrolled at Level {{ $student_level }}. You can see assignments for Level {{ $student_level }} and below.
        </div>
    @else
        <div class="rounded border bg-yellow-50 text-yellow-800 px-4 py-3 mb-6 text-sm">
           You don't seem to be enrolled in this course with a specific level. Showing all available assignments.
        </div>
    @endif


    {{-- Header --}}
    @php
      $levelColors = [
        3 => 'bg-cyan-300',
        2 => 'bg-green-200',
        1 => 'bg-rose-200',
      ];
      // Use the student's *enrolled* level for header color, default gray
      $headerColor = $levelColors[$student_level ?? null] ?? 'bg-gray-100';
    @endphp
    <div class="flex items-center justify-between p-4 rounded-lg {{ $headerColor }}">
      <div>
        <h1 class="text-2xl font-semibold ">Upload Links</h1>
        <h1 class="text-xl font-thin">{{ $course->code }} {{ $course->name }}</h1>
      </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mt-4 flex flex-wrap gap-3 items-end">
      {{-- Level Filter --}}
      <div>
        <label class="block text-sm text-gray-600">Level</label>
        <select name="level" class="block border rounded py-2.5 px-2 text-xs w-full text-center">
          {{-- Show levels up to the student's enrolled level --}}
          <option value="">All My Levels</option>
          @if($student_level !== null) {{-- Check if student_level is not null --}}
              @foreach (range(1, $student_level) as $lv)
                <option value="{{ $lv }}" @selected($level == $lv)>Level {{ $lv }}</option>
              @endforeach
          @else
              {{-- If no student level, show all levels as fallback --}}
              @foreach ([1,2,3] as $lv)
                  <option value="{{ $lv }}" @selected($level == $lv)>Level {{ $lv }}</option>
              @endforeach
          @endif
        </select>
      </div>

      {{-- Apply Level button --}}
      <button class="px-3 py-2 rounded bg-red-600 text-white">Apply Level</button>

      {{-- Link to view all for the current level (clears week/day) --}}
      @if ($level || $week || $day)
        <a href="{{ route('student.assignments.index', ['course' => $course, 'level' => $level]) }}"
           class="text-sm text-blue-600 hover:underline">
           View All Assignments for Level {{ $level ?: 'My Levels' }}
        </a>
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

    {{-- Assignments table (now filtered) --}}
    <div class="mt-8">
        {{-- Title --}}
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold">
              @if ($week && $day)
                  Assignments for: Week {{ $week }}, {{ $day }}
              @elseif ($level)
                  Assignments for Level {{ $level }}
              @else
                  All My Assignments
              @endif
          </h2>
        </div>

        {{-- Assignments Table --}}
        <div class="mt-4 overflow-x-auto">
          <table class="min-w-full text-sm border">
            <thead class="bg-gray-50">
                <tr class="text-sm text-gray-600">
                    <th class="px-6 py-3 text-left">Assignment title</th>
                    <th class="px-6 py-3 text-left">Status</th> {{-- Changed from Lecturer's 'Published' status --}}
                    <th class="px-6 py-3 text-left">Level</th>
                    <th class="px-6 py-3 text-left">Week</th>
                    <th class="px-6 py-3 text-left">Day</th>
                    <th class="px-6 py-3 text-left">Due Date</th>
                    <th class="px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
            @forelse($assignments as $a)
                @php
                    // Get the student's submission for this assignment (already eager loaded)
                    $submission = $a->submissions->first();
                    // Use the eager loaded 'has_assessment' attribute
                    $hasAssessment = $a->has_assessment;

                    // Determine student-centric status
                    $status = 'Open'; // Default
                    if ($submission) {
                        $status = $hasAssessment ? 'Graded' : 'Submitted';
                    } elseif ($a->due_at && $a->due_at->isPast()) {
                        $status = 'Closed';
                    }

                    // Determine if student can submit/edit
                    $canSubmit = !$submission && (!$a->due_at || $a->due_at->isFuture());
                     // Can edit if submitted, not graded, and not past due (or no due date)
                    $canEdit = $submission && !$hasAssessment && (!$a->due_at || $a->due_at->isFuture());
                @endphp
                <tr>
                    <td class="px-6 py-3 align-top">
                        <span class="font-medium">{{ $a->title }}</span>
                         @if($a->instruction)
                            <p class="text-xs text-gray-500 mt-1 whitespace-pre-wrap">{{ $a->instruction }}</p>
                         @endif
                    </td>
                    <td class="px-6 py-3 align-top">
                        {{-- Student Status Span --}}
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs
                            @class([
                                'bg-green-100 text-green-700' => $status === 'Graded',
                                'bg-blue-100 text-blue-700'  => $status === 'Submitted',
                                'bg-gray-100 text-gray-700'  => $status === 'Closed',
                                'bg-yellow-100 text-yellow-700' => $status === 'Open',
                            ])">
                            {{ $status }}
                        </span>
                    </td>
                    <td class="px-6 py-3 align-top">{{ $a->level ?? '—' }}</td>
                    <td class="px-6 py-3 align-top">{{ $a->week ?? '—' }}</td>
                    <td class="px-6 py-3 align-top">{{ $a->day ?? '—' }}</td>
                    <td class="px-6 py-3 align-top whitespace-nowrap">
                        @if($a->due_at)
                            {{ $a->due_at->format('d M Y, H:i') }}
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 align-top">
                        {{-- Student Actions --}}
                        <div class="flex flex-col items-start gap-1"> {{-- Changed to flex-col for better wrapping --}}
                            @if($canSubmit)
                                <a href="{{ route('student.assignments.submissions.create', $a) }}" class="text-blue-600 hover:underline text-xs whitespace-nowrap">Submit Now</a>
                            @elseif($canEdit)
                                <a href="{{ route('student.assignments.submissions.edit', [$a, $submission]) }}" class="text-blue-600 hover:underline text-xs whitespace-nowrap">Edit Submission</a>
                            @elseif($status === 'Graded')
                                <a href="{{ route('student.assignments.submissions.show', [$a, $submission]) }}" class="text-green-600 hover:underline text-xs whitespace-nowrap">View Feedback</a>
                            @elseif($status === 'Submitted')
                                 <span class="text-gray-500 text-xs whitespace-nowrap">Awaiting Grade</span>
                            @elseif($status === 'Closed')
                                 <span class="text-red-500 text-xs whitespace-nowrap">Past Due</span>
                             @else
                                 <span class="text-gray-400 text-xs">—</span>
                            @endif

                            {{-- Download link for assignment file (uses new route) --}}
                            @if($a->file_path)
                                <a href="{{ route('student.assignments.download', $a) }}" class="text-gray-500 hover:underline text-xs whitespace-nowrap mt-1">(Download Task File)</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="px-6 py-6 text-gray-500 text-center" colspan="7">
                        No upload links found matching your filters and level.
                    </td>
                </tr>
            @endforelse
            </tbody>
          </table>
        </div>

        {{-- Paginator --}}
        @if($assignments->hasPages()) {{-- Check directly on paginator instance --}}
            <div class="mt-4 px-6 py-3">{{ $assignments->links() }}</div>
        @endif
    </div>

</x-layout>
