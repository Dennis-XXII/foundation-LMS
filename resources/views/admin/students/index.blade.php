<x-layout>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Students</h1>
        <a href="{{ route('admin.students.create') }}" class="px-4 py-2 bg-purple-900 text-white rounded">Add Student</a>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-50 text-green-800 border border-green-200 px-4 py-2 rounded">{{ session('success') }}</div>
    @endif

    <div class="bg-white border rounded overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Student ID</th>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Email</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $s)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $s->student_id }}</td>
                        <td class="px-4 py-2">{{ $s->user->name }}</td>
                        <td class="px-4 py-2">{{ $s->user->email }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            <a href="{{ route('admin.students.edit', $s) }}" class="px-3 py-1.5 border rounded">Edit</a>
                            <form method="POST" action="{{ route('admin.students.destroy', $s) }}"
                                  class="inline"
                                  onsubmit="return confirm('Delete this student and their account?')">
                                @csrf @method('DELETE')
                                <button class="px-3 py-1.5 bg-rose-600 text-white rounded">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr class="border-t"><td colspan="4" class="px-4 py-6 text-gray-500">No students.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $students->links() }}
    </div>
</x-layout>
