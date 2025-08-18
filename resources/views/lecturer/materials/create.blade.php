{{-- resources/views/lecturer/materials/create.blade.php --}}
<x-layout>
  <div class="max-w-4xl mx-auto p-6">
    <h1 class="text-2xl font-semibold">Add Materials - {{ $course->code }} {{ $course->name }}</h1>

    @if($errors->any())
      <div class="mt-4 p-3 rounded bg-red-50 text-red-700">
        <ul class="list-disc ml-5">
          @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
      </div>
    @endif

    <form class="mt-6 space-y-4" method="POST" action="{{ route('lecturer.courses.materials.store', $course) }}" enctype="multipart/form-data">
      @csrf

      <div>
        <label class="block text-sm font-medium">Title</label>
        <input name="title" class="mt-1 w-full border rounded px-3 py-2" placeholder="Title of your material" value="{{ old('title') }}" required>
      </div>

      <div>
        <label class="block text-sm font-medium">Descriptions</label>
        <textarea name="descriptions" rows="4"placeholder="You can provide links to external videos and resources here."class="mt-1 w-full border rounded px-3 py-2">{{ old('descriptions') }}</textarea>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium">Type</label>
          <select name="type" class="mt-1 w-full border rounded py-2" required>
            @foreach(['lesson'=>'Lesson Materials','worksheet'=>'Worksheets','self_study'=>'Self‑study'] as $v=>$label)
              <option value="{{ $v }}" @selected(old('type', request('type'))===$v)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium">Level</label>
          <select name="level" class="mt-1 w-full border rounded px-3 py-2">
            @foreach([1,2,3] as $lv)
              <option value="{{ $lv }}" @selected(old('level', request('level'))==$lv)>{{ $lv }}</option>
            @endforeach
          </select>
        </div>
        <div class="flex items-center gap-2">
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
        <a href="{{ route('lecturer.courses.materials.index', $course) }}" class="px-4 py-2 rounded border">Back</a>
      </div>
    </form>
  </div>
</x-layout>
