<x-layout>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Announcements</h1>
        <a href="{{ route('admin.announcements.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">New Announcement</a>
    </div>

    <div class="bg-white rounded-lg shadow border">
        <table class="min-w-full divide-y">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold text-gray-600">
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Scope</th>
                    <th class="px-4 py-3">Posted</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($announcements as $a)
                    <tr>
                        <td class="px-4 py-3">{{ $a->title }}</td>
                        <td class="px-4 py-3">
                            @if($a->is_global)
                                <span class="text-xs px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded">Global</span>
                            @else
                                <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-700 rounded">Course</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            {{ optional($a->posted_at)->timezone(config('app.timezone'))->format('d M Y, H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.announcements.edit', $a) }}" class="text-blue-600 hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-6 text-gray-500" colspan="4">No announcements yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layout>
