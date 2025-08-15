<x-layout>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">New Announcement</h1>
        <a href="{{ route('admin.announcements.index') }}" class="text-blue-600 hover:underline">Back</a>
    </div>

    <form class="bg-white border rounded-lg shadow p-6 space-y-4"
          method="POST" action="{{ route('admin.announcements.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="text" name="title" placeholder="Title" class="w-full border rounded px-4 py-2" required>
        <textarea name="description" rows="3" placeholder="Write something..." class="w-full border rounded px-4 py-2"></textarea>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Attach file (optional)</label>
                <input type="file" name="file" class="w-full border rounded px-3 py-2">
            </div>
            <div class="flex items-center gap-3">
                <label class="inline-flex items-center gap-2 mt-6">
                    <input type="checkbox" name="is_global" value="1">
                    <span>Global</span>
                </label>
            </div>
        </div>

        {{-- If course-scoped mapping is allowed, you can multi-select courses --}}
        @isset($courses)
        <div>
            <label class="block text-sm font-medium mb-1">Target Courses (when not global)</label>
            <select name="course_ids[]" multiple class="w-full border rounded px-3 py-2">
                @foreach($courses as $c)
                    <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">Leave empty if Global is checked.</p>
        </div>
        @endisset

        <button class="px-4 py-2 bg-blue-600 text-white rounded">Publish</button>
    </form>
</x-layout>
