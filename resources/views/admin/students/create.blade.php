<x-layout>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Whitelist Student ID</h1>
        <a
            href="{{ route("admin.students.index") }}"
            class="text-blue-600 hover:underline"
        >
            View Registered Students
        </a>
    </div>

    @if (session("success"))
        <div
            class="mb-4 bg-green-50 text-green-800 border border-green-200 px-4 py-2 rounded"
        >
            {{ session("success") }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">
            <form
                method="POST"
                action="{{ route("admin.students.wlStore") }}"
                class="bg-white border rounded-lg shadow p-6 space-y-4"
            >
                @csrf
                <h2 class="text-lg font-semibold border-b pb-2">Add New ID</h2>
                <p class="text-gray-600 text-sm mb-4">
                    Enter a Student ID to allow them to register.
                </p>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Student ID
                    </label>
                    <input
                        name="student_id"
                        type="text"
                        value="{{ old("student_id") }}"
                        placeholder="e.g. 6401234"
                        class="w-full border rounded px-3 py-2 @error("student_id") border-red-500 @enderror"
                        required
                    />
                    @error("student_id")
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full py-2 bg-purple-900 text-white rounded hover:bg-purple-800 transition"
                >
                    Whitelist ID
                </button>
            </form>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white border rounded-lg shadow overflow-hidden">
                <div class="bg-gray-50 px-6 py-3 border-b">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Currently Whitelisted (Pending Registration)
                    </h2>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Student ID
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Added Date
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($whitelistedStudents as $eligible)
                            <tr>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                >
                                    {{ $eligible->student_id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($eligible->registeredStudent)
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"
                                        >
                                            Registered
                                        </span>
                                    @else
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800"
                                        >
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{ $eligible->created_at->format("M d, Y") }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                >
                                    {{-- Only allow removal if they haven't registered yet --}}
                                    @if (! $eligible->registeredStudent)
                                        <form
                                            action="{{ route("admin.students.wlDestroy", $eligible->id) }}"
                                            method="POST"
                                            class="inline"
                                        >
                                            @csrf
                                            @method("DELETE")
                                            <button
                                                class="text-rose-600 hover:text-rose-900"
                                            >
                                                Remove
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            {{-- ... empty state ... --}}
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layout>
