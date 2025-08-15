<x-layout>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Edit Student</h1>
        <a href="{{ url()->previous() }}" class="text-blue-600 hover:underline">Back</a>
    </div>

    <form method="POST" action="{{ route('admin.students.update', $student) }}" class="bg-white border rounded-lg shadow p-6 space-y-4">
        @csrf @method('PUT')
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input name="name" value="{{ old('name', $student->user->name ?? '') }}" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Student ID</label>
                <input name="student_id" value="{{ old('student_id', $student->student_id ?? '') }}" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input name="email" value="{{ old('email', $student->user->email ?? '') }}" class="w-full border rounded px-3 py-2" required>
            </div>
        </div>
        <button class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
    </form>
</x-layout>
