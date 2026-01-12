<x-layout>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Whitelist Student ID</h1>
        <a
            href="{{ route('admin.students.index') }}"
            class="text-blue-600 hover:underline"
        >
            Back to List
        </a>
    </div>

    @if (session("success"))
        <div class="mb-4 bg-green-50 text-green-800 border border-green-200 px-4 py-2 rounded">
            {{ session("success") }}
        </div>
    @endif

    <div class="max-w-xl">
        <form
            method="POST"
            action="{{ route('admin.students.store') }}" 
            class="bg-white border rounded-lg shadow p-6 space-y-4"
        >
            @csrf
            
            <p class="text-gray-600 text-sm mb-4">
                Enter a Student ID below to allow a student to register for an account. 
                They will use this ID during their signup process.
            </p>

            <div>
                <label class="block text-sm font-medium mb-1">Student ID</label>
                <input
                    name="student_id"
                    type="text"
                    value="{{ old('student_id') }}"
                    placeholder="e.g. 6401234"
                    class="w-full border rounded px-3 py-2 @error('student_id') border-red-500 @enderror"
                    required
                    autofocus
                />
                @error('student_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-6 py-2 bg-purple-900 text-white rounded hover:bg-purple-800 transition">
                    Whitelist ID
                </button>
            </div>
        </form>
    </div>
</x-layout>