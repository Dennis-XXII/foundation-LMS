<x-layout>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Edit Announcement</h1>
        <a
            href="{{ route("admin.announcements.index") }}"
            class="text-blue-600 hover:underline"
        >
            Back
        </a>
    </div>

    <form
        class="bg-white border rounded-lg shadow p-6 space-y-4"
        method="POST"
        action="{{ route("admin.announcements.update", $announcement) }}"
        enctype="multipart/form-data"
    >
        @csrf
        @method("PUT")
        <input
            type="text"
            name="title"
            value="{{ old("title", $announcement->title) }}"
            class="w-full border rounded px-4 py-2"
            required
        />
        <textarea
            name="description"
            rows="3"
            class="w-full border rounded px-4 py-2"
        >
{{ old("description", $announcement->description) }}</textarea
        >

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">
                    Replace file (optional)
                </label>
                <input
                    type="file"
                    name="file"
                    class="w-full border rounded px-3 py-2"
                />
            </div>
            <div class="flex items-center gap-3">
                <label class="inline-flex items-center gap-2 mt-6">
                    <input
                        type="checkbox"
                        name="is_global"
                        value="1"
                        {{ old("is_global", $announcement->is_global) ? "checked" : "" }}
                    />
                    <span>Global</span>
                </label>
            </div>
        </div>

        @isset($courses)
            <div>
                <label class="block text-sm font-medium mb-1">
                    Target Courses (when not global)
                </label>
                <select
                    name="course_ids[]"
                    multiple
                    class="w-full border rounded px-3 py-2"
                >
                    @foreach ($courses as $c)
                        <option
                            value="{{ $c->id }}"
                            @selected(in_array($c->id, old("course_ids", $announcement->courses->pluck("id")->all() ?? [])))
                        >
                            {{ $c->code }} — {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endisset

        <div class="flex items-center gap-3">
            <button class="px-4 py-2 bg-blue-600 text-white rounded">
                Save
            </button>
            <form
                method="POST"
                action="{{ route("admin.announcements.destroy", $announcement) }}"
                onsubmit="return confirm('Delete this announcement?');"
            >
                @csrf
                @method("DELETE")
                <button class="px-4 py-2 bg-rose-600 text-white rounded">
                    Delete
                </button>
            </form>
        </div>
    </form>
</x-layout>
