<x-layout>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Lecturers</h1>
        <a href="{{ route('admin.lecturers.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">New Lecturer</a>
    </div>

    <div class="bg-white rounded-lg shadow border">
        <table class="min-w-full divide-y">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs font-semibold text-gray-600">
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($lecturers as $lec)
                    <tr>
                        <td class="px-4 py-3">{{ $lec->user->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $lec->user->email ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.lecturers.edit', $lec) }}" class="text-blue-600 hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-6 text-gray-500" colspan="3">No lecturers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layout>
