<x-layout>
    @php
        $level = (int) request('level');
        $tab   = request('tab'); // null | 'assess'
        $isAssess = $tab === 'assess';
        $type = request('type') ?? 'lesson'; // default to lesson
        $levelLabel = $level ? ('LEVEL '.$level) : null;
    @endphp

      <!--breadcrumbs-->
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
  <!--breadcrumbs end-->

    {{-- Top options: Post / Assess (active highlight) --}}
    <div class="flex items-center justify-center gap-6 mb-8">
        <a href="{{ route('lecturer.courses.assignments.index', $course) }}?level={{ $level }}"
           class="px-6 py-2.5 rounded-lg shadow {{ $isAssess ? 'bg-rose-200 text-rose-800' : 'bg-rose-500 text-white hover:bg-rose-600' }}">
            Post Upload Links
        </a>
        <a href="{{ route('lecturer.courses.assignments.index', $course) }}?level={{ $level }}&tab=assess"
           class="px-6 py-2.5 rounded-lg shadow {{ $isAssess ? 'bg-blue-600 text-white' : 'bg-blue-200 text-blue-800 hover:bg-blue-300' }}">
            Assess Student Uploads
        </a>
    </div>

    {{-- If no level provided, guide the lecturer --}}
    @if(!$level)
        <div class="rounded border bg-yellow-50 text-yellow-800 px-4 py-3 mb-6">
            Please select a level from the dashboard. Example:
            <code class="px-1 py-0.5 bg-yellow-100 rounded">?level=1</code>
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

    @if(!$isAssess)
        {{-- ===================== POST UPLOAD LINKS (CREATE + LIST) ===================== --}}
        <div class="bg-white rounded-lg shadow border p-6 mb-8">
            {{-- Upload form (inline, per UI) --}}
            <form method="POST"
                  action="{{ route('lecturer.courses.assignments.store', $course) }}"
                  enctype="multipart/form-data"
                  class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                @csrf
                <input type="hidden" name="level" value="{{ $level }}">

                <div class="md:col-span-1">
                    <label class="block text-sm text-gray-700 mb-1">File name</label>
                    <input name="title" placeholder="Assignment 1"
                           class="w-full border rounded px-3 py-2" required>
                </div>

                <div class="md:col-span-1">
                    <label class="block text-sm text-gray-700 mb-1">Due Date</label>
                    <input type="date" name="due_at" class="w-full border rounded px-3 py-2">
                </div>

                <div class="md:col-span-1">
                    <label class="block text-sm text-gray-700 mb-1">Attachment (optional)</label>
                    <input type="file" name="file" class="w-full border rounded px-3 py-2">
                </div>

                <div class="md:col-span-1">
                    <button class="w-full md:w-auto px-5 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                        Upload
                    </button>
                </div>

                <div class="md:col-span-4">
                    <label class="block text-sm text-gray-700 mb-1 ">Instruction</label>
                    <textarea name="instruction" rows="2" class="w-full border rounded px-3 py-2 min-h-[100px]"
                              placeholder="Give instructions here..."></textarea>
                </div>

                <div class="md:col-span-4">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="is_published" value="1" checked>
                        <span class="text-sm text-gray-700">Active</span>
                    </label>
                </div>
            </form>
        </div>

        {{-- Uploaded Links table (only current level) --}}
        <div class="bg-white rounded-lg shadow border overflow-hidden">
            <div class="bg-rose-300 px-6 py-3 font-semibold">Uploaded Links</div>
            <table class="w-full text-left">
                <thead class="bg-gray-100">
                    <tr class="text-sm text-gray-600">
                        <th class="px-6 py-3">Assignment title</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Due Date</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                @forelse($assignments as $a)
                    <tr>
                        <td class="px-6 py-3">{{ $a->title }}</td>
                        <td class="px-6 py-3">
                            @php $active = (bool) $a->is_published; @endphp
                            <span class="px-2 py-1 text-xs rounded {{ $active ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-600' }}">
                                {{ $active ? 'Active' : 'Due' }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            @if($a->due_at)
                                {{ \Illuminate\Support\Carbon::parse($a->due_at)->timezone(config('app.timezone'))->format('d M Y') }}
                            @else
                                <span class="text-red-400">Overdue</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                @if(!empty($a->file_path))
                                    <a class="text-blue-700 underline"
                                       href="{{ route('lecturer.assignments.show', $a) }}">Download</a>
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
                        <td class="px-6 py-6 text-gray-500" colspan="4">
                            No upload links for this level yet.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{-- Paginator (if you passed a paginator) --}}
            @if(method_exists($assignments, 'links'))
                <div class="px-6 py-3">{{ $assignments->withQueryString()->links() }}</div>
            @endif
        </div>
    @else
        {{-- ===================== ASSESS TAB (GROUPED BY ASSIGNMENT) ===================== --}}
        @forelse($assignments as $assignment)
            <div class="mb-8 bg-white rounded-lg shadow border overflow-hidden">
                <div class="bg-rose-300 px-6 py-3 font-semibold">
                    {{ $assignment->title }}
                </div>

                <table class="w-full text-left">
                    <thead class="bg-gray-100">
                        <tr class="text-sm text-gray-600">
                            <th class="px-6 py-3">Student Name</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Turn in Date</th>
                            <th class="px-6 py-3">Score</th>
                            <th class="px-6 py-3">Comment</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @php $subs = $assignment->submissions ?? collect(); @endphp

                        @forelse($subs as $s)
                            @php $done = !empty($s->submitted_at); @endphp
                            <tr>
                                <td class="px-6 py-3">{{ $s->student->user->name ?? '—' }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 text-xs rounded {{ $done ? 'bg-green-100 text-green-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $done ? 'Finish' : 'Miss' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    @if($s->submitted_at)
                                        {{ \Illuminate\Support\Carbon::parse($s->submitted_at)->format('d M Y') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2">
                                        <input type="number" name="score" min="0" max="10"
                                               form="assess-{{ $s->id }}"
                                               value="{{ old('score', $s->score) }}"
                                               class="w-16 border rounded px-2 py-1 text-sm">
                                        <span class="text-sm text-gray-600">/ 10</span>
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    <input type="text" name="comment"
                                           form="assess-{{ $s->id }}"
                                           value="{{ old('comment', $s->comment) }}"
                                           class="w-64 border rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        @if(!empty($s->file_path))
                                            <a class="text-blue-700 underline"
                                               href="{{ route('lecturer.submissions.assessments.edit', [$s, $s->assessment ?? null]) }}">
                                                Download
                                            </a>
                                        @endif

                                        {{-- Mark (POST to create/update assessment) --}}
                                        <form id="assess-{{ $s->id }}" method="POST"
                                              action="{{ route('lecturer.submissions.assessments.store', $s) }}"
                                              enctype="multipart/form-data"
                                              class="flex items-center gap-2">
                                            @csrf
                                            <input type="file" name="feedback_file" class="text-xs">
                                            <button class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm">Mark</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-6 text-gray-500" colspan="6">
                                    No turned in assignments yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @empty
            <div class="rounded border bg-gray-50 text-gray-700 px-4 py-6">
                No assignments for this level yet.
            </div>
        @endforelse
    @endif
</x-layout>
