{{-- resources/views/lecturer/materials/create.blade.php --}}
<x-layout>
  <nav class="mb-6 text-sm text-gray-600" aria-label="Breadcrumb">
    <ol class="list-reset flex">
      <li>
        <a href="{{ route('lecturer.dashboard') }}" class="hover:underline">Dashboard</a>
        <span class="mx-2">/</span>
      </li>
      <li>
        <a href="{{ route('lecturer.courses.materials.index', $course) }}{{ request('type') || request('level') ? '?' . http_build_query(array_filter(['type'=>request('type'),'level'=>request('level')])) : '' }}" class="hover:underline">
            {{ str(request('type') ?? $type)->replace('_', ' ')->title() }}
        </a>
        <span class="mx-2">/</span>
      </li>
      <li class="text-black font-semibold">
        Add Material
      </li>
    </ol>
  </nav>
  {{-- Course header --}}
  <div class="max-w-4xl mx-auto p-6">
    <h1 class="text-2xl font-semibold">
      Add Materials — {{ $course->code }} {{ $course->name }} 
    </h1>
    <h1 class="text-xl text-gray-500 font-thin pt-3">Level - {{ request('level') ?? ($level ?? '—') }} {{ request ('type') ? str(request('type'))->replace('_', ' ')->title() : '' }}</h1>

    {{-- Validation errors --}}
    @if($errors->any())
      <div class="mt-4 p-3 rounded bg-red-50 text-red-700">
        <ul class="list-disc ml-5">
          @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
      </div>
    @endif

    <form class="mt-6 space-y-4"
          method="POST"
          action="{{ route('lecturer.courses.materials.store', $course) }}"
          enctype="multipart/form-data">
      @csrf

      <div>
        <label class="block text-sm font-medium">Title</label>
        <input name="title"
               class="mt-1 w-full border rounded px-3 py-2"
               placeholder="Title of your material"
               value="{{ old('title') }}"
               required>
      </div>

      <div>
        <label class="block text-sm font-medium">Descriptions</label>
        <textarea name="descriptions"
                  rows="4"
                  placeholder="You can provide links to external videos and resources here."
                  class="mt-1 w-full border rounded px-3 py-2 min-h-[100px]">{{ old('descriptions') }}</textarea>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- TYPE: lock if ?type= is present --}}
        <div>
          @if(request()->filled('type'))
            <input type="hidden" name="type" value="{{ request('type') }}">
            <label class="block text-sm font-medium">Type</label>
            <div class="mt-1 px-3 py-2 border rounded bg-gray-50">
              {{ str(request('type'))->replace('_',' ')->title() }}
              <span class="ml-2 text-xs text-gray-500">(locked)</span>
            </div>
          @else
            <label class="block text-sm font-medium">Type</label>
            <select name="type" class="mt-1 w-full border rounded py-2" required>
              @foreach(['lesson'=>'Lesson Materials','worksheet'=>'Worksheets','self_study'=>'Self-study'] as $v=>$label)
                <option value="{{ $v }}" @selected(old('type', request('type'))===$v)>{{ $label }}</option>
              @endforeach
            </select>
          @endif
        </div>

        {{-- LEVEL: lock if ?level= is present --}}
        <div>
          @if(request()->filled('level'))
            <input type="hidden" name="level" value="{{ request('level') }}">
            <label class="block text-sm font-medium">Level</label>
            <div class="mt-1 px-3 py-2 border rounded bg-gray-50">
              Level {{ request('level') }}
              <span class="ml-2 text-xs text-gray-500">(locked)</span>
            </div>
          @else
            <label class="block text-sm font-medium">Level</label>
            <select name="level" class="mt-1 w-full border rounded px-3 py-2">
              <option value="">Select level (optional)</option>
              @foreach([1,2,3] as $lv)
                <option value="{{ $lv }}" @selected(old('level', request('level'))==$lv)>Level {{ $lv }}</option>
              @endforeach
            </select>
          @endif
        </div>

        {{-- NEW: Week --}}
        <div>
          <label class="block text-sm font-medium">Week</label>
          <select name="week" class="mt-1 w-full border rounded px-3 py-2">
            <option value="">Select week (optional)</option>
            @foreach(range(1, 8) as $w)
              <option value="{{ $w }}" @selected(old('week') == $w)>Week {{ $w }}</option>
            @endforeach
          </select>
        </div>

        {{-- NEW: Day --}}
        <div>
          <label class="block text-sm font-medium">Day</label>
          <select name="day" class="mt-1 w-full border rounded px-3 py-2">
            <option value="">Select day (optional)</option>
            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
              <option value="{{ $day }}" @selected(old('day') == $day)>{{ $day }}</option>
            @endforeach
          </select>
        </div>

        <div class="flex items-center gap-2 md:col-start-1"> {{-- Moved to new line for grid flow --}}
          <input id="is_published" name="is_published" type="checkbox" value="1" @checked(old('is_published',1))>
          <label for="is_published" class="text-sm">Published</label>
        </div>
      </div>

      {{-- File + URL (both allowed for materials) --}}
      <div class="border rounded p-4 space-y-3">
        <div>
          <label class="block text-xs text-gray-600">Upload file</label>
          <input type="file" name="file" class="mt-1 block">
          <p class="text-xs text-gray-500">Allowed: pdf, doc, docx, ppt, pptx, zip. Max 20MB.</p>
        </div>
        <div>
          <label class="block text-xs text-gray-600">Optional external URL</label>
          <input type="url" name="url" value="{{ old('url') }}" class="mt-1 w-full border rounded px-3 py-2" placeholder="https://...">
        </div>
      </div>

      <div class="flex gap-3">
        <button class="px-4 py-2 rounded bg-black text-white">Create</button>

        {{-- Preserve filters on back link --}}
        @php
          $backUrl = route('lecturer.courses.materials.index', $course);
          $qs = http_build_query(array_filter([
              'type'  => request('type'),
              'level' => request('level'),
          ]));
          if ($qs) { $backUrl .= '?'.$qs; }
        @endphp
        <a href="{{ $backUrl }}" class="px-4 py-2 rounded border">Back</a>
      </div>
    </form>
  </div>
</x-layout>
