<x-layout>
    <nav>
        <ol
            class="list-reset flex text-sm text-gray-600 mb-4"
            aria-label="Breadcrumb"
        >
            <li>
                <a
                    href="{{ route("admin.dashboard") }}"
                    class="hover:underline"
                >
                    Dashboard
                </a>
                <span class="mx-2">/</span>
            </li>
            <li class="text-black font-semibold">Courses</li>
        </ol>
    </nav>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Courses</h1>
        <a
            href="{{ route("admin.courses.create") }}"
            class="px-4 py-2 bg-blue-600 text-white rounded"
        >
            New Course
        </a>
    </div>

    <div class="bg-white rounded-lg shadow border">
        <table class="min-w-full divide-y">
            @foreach ($courses as $course)
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold text-gray-600">
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Year / Program</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
            @endforeach

            <tbody class="divide-y">
                @forelse ($courses as $c)
                    <tr
                        class="hover:bg-gray-50 rounded-2xl cursor-pointer"
                        onClick="window.location='{{ route("admin.courses.edit", $c) }}'"
                    >
                        <td class="px-4 py-3">{{ $c->code }}</td>
                        <td class="px-4 py-3">{{ $c->name }}</td>
                        <td class="px-4 py-3">{{ $c->level ?? "—" }}</td>
                        <td class="px-4 py-3 text-right">
                            <a
                                href="{{ route("admin.courses.edit", $c) }}"
                                class="text-blue-600 hover:underline"
                            >
                                Edit
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-gray-500" colspan="4">
                            No courses yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layout>
