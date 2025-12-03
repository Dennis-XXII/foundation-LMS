<x-layout>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Add Student Profile</h1>
        <a
            href="{{ url()->previous() }}"
            class="text-blue-600 hover:underline"
        >
            Back
        </a>
    </div>

    <form
        method="POST"
        action="{{ route("admin.students.store") }}"
        class="bg-white border rounded-lg shadow p-6 space-y-4"
    >
        @csrf
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input
                    name="name"
                    class="w-full border rounded px-3 py-2"
                    required
                />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Student ID</label>
                <input
                    name="student_id"
                    class="w-full border rounded px-3 py-2"
                    required
                />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input
                    name="email"
                    type="email"
                    class="w-full border rounded px-3 py-2"
                    required
                />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input
                    name="password"
                    type="password"
                    class="w-full border rounded px-3 py-2"
                    required
                />
            </div>
        </div>
        <button class="px-4 py-2 bg-blue-600 text-white rounded">Create</button>
    </form>
</x-layout>
