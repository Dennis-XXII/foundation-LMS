<x-layout>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Courses</h1>
        <a href="{{ route('admin.courses.create') }}"
           class="px-4 py-2 bg-blue-600 text-white rounded">New Course</a>
    </div>

    <div class="bg-white rounded-lg shadow border">
        <table class="min-w-full divide-y">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold text-gray-600">
                    <th class="px-4 py-3">Code</th>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Level</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($courses as $c)
                    <tr>
                        <td class="px-4 py-3">{{ $c->code }}</td>
                        <td class="px-4 py-3">{{ $c->name }}</td>
                        <td class="px-4 py-3">{{ $c->level ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.courses.edit', $c) }}" class="text-blue-600 hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-6 text-gray-500" colspan="4">No courses yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layout>
