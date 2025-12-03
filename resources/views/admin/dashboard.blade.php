<x-layout>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold pl-2 font-thin">Admin Dashboard</h1>
    </div>

    {{-- Flashes / Errors --}}
    @if (session("success"))
        <div
            class="mb-4 bg-green-50 text-green-800 border border-green-200 px-4 py-2 rounded"
        >
            {{ session("success") }}
        </div>
    @endif

    @if ($errors->any())
        <div
            class="mb-4 bg-rose-50 text-rose-800 border border-rose-200 px-4 py-2 rounded"
        >
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Courses overview --}}
    <section class="bg-white rounded-lg shadow border overflow-hidden">
        <div class="bg-purple-900 text-white px-6 py-4">
            <h2 class="text-lg font-semibold">Courses</h2>
        </div>

        <div class="p-6">
            @php
                //Normalize inputs so the view works whether you pass $course or $courses
                $hasCollection = isset($courses) && $courses instanceof \Illuminate\Support\Collection && $courses->isNotEmpty();
                $hasSingle = ! $hasCollection && isset($course) && $course;
            @endphp

            {{-- No course(s) --}}
            @if (! $hasCollection && ! $hasSingle)
                <div class="flex items-center justify-between">
                    <p class="text-gray-600">No courses yet.</p>
                    <a
                        href="{{ route("admin.courses.create") }}"
                        class="px-4 py-2 bg-blue-600 text-white rounded"
                    >
                        Create Course
                    </a>
                </div>

                {{-- Single course card --}}
            @elseif ($hasSingle)
                @php
                    $c = $course;
                @endphp

                <div class="grid grid-cols-1">
                    <div class="border rounded-lg p-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="text-sm text-gray-500">
                                    {{ $c->code }}
                                </div>
                                <div class="text-xl font-semibold">
                                    {{ $c->name }}
                                </div>
                                @if (! empty($c->description))
                                    <div class="text-gray-600 mt-1">
                                        {{ $c->description }}
                                    </div>
                                @endif
                            </div>
                            <a
                                href="{{ route("admin.courses.edit", $c) }}"
                                class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                            >
                                Edit
                            </a>
                        </div>

                        @if (isset($c->stats) && is_array($c->stats))
                            <div class="mt-3 text-sm text-gray-600">
                                Students:
                                <span class="font-medium">
                                    {{ $c->stats["students"] ?? "0" }}
                                </span>
                                · Materials:
                                <span class="font-medium">
                                    {{ $c->stats["materials"] ?? "0" }}
                                </span>
                                · Upload Links:
                                <span class="font-medium">
                                    {{ $c->stats["assignments"] ?? "0" }}
                                </span>
                            </div>
                        @endif

                        <div class="mt-4 flex flex-wrap gap-2">
                            {{-- Per-course ENROLLMENTS (add/remove students in this course) --}}
                            <a
                                href="{{ route("admin.courses.students.index", $c) }}"
                                class="px-3 py-1.5 text-sm border rounded hover:bg-gray-50"
                            >
                                Manage Enrollments
                            </a>

                            {{-- Global students (create/edit/delete student accounts) --}}
                            <a
                                href="{{ route("admin.students.index") }}"
                                class="px-3 py-1.5 text-sm border rounded hover:bg-gray-50"
                            >
                                Manage Students
                            </a>

                            {{-- Announcements center --}}
                            <a
                                href="{{ route("admin.announcements.index") }}"
                                class="px-3 py-1.5 text-sm border rounded hover:bg-gray-50"
                            >
                                Announcements
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Multiple courses --}}
            @else
                <div
                    class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
                >
                    @foreach ($courses as $c)
                        <div class="border rounded-lg p-5">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-sm text-gray-500">
                                        {{ $c->code }}
                                    </div>
                                    <div class="text-xl font-semibold">
                                        {{ $c->name }}
                                    </div>
                                    @if (! empty($c->description))
                                        <div class="text-gray-600 mt-1">
                                            {{ $c->description }}
                                        </div>
                                    @endif
                                </div>
                                <a
                                    href="{{ route("admin.courses.edit", $c) }}"
                                    class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                                >
                                    Edit
                                </a>
                            </div>

                            @if (isset($c->stats) && is_array($c->stats))
                                <div class="mt-3 text-sm text-gray-600">
                                    Students:
                                    <span class="font-medium">
                                        {{ $c->stats["students"] ?? "0" }}
                                    </span>
                                    · Materials:
                                    <span class="font-medium">
                                        {{ $c->stats["materials"] ?? "0" }}
                                    </span>
                                    · Upload Links:
                                    <span class="font-medium">
                                        {{ $c->stats["assignments"] ?? "0" }}
                                    </span>
                                </div>
                            @endif

                            {{-- Quick actions per course --}}
                            <div class="mt-4 flex flex-wrap gap-2">
                                {{-- Per‑course ENROLLMENTS (add/remove students in this course) --}}
                                <a
                                    href="{{ route("admin.courses.enrollments.index", $c) }}"
                                    class="px-3 py-1.5 text-sm border rounded hover:bg-gray-50"
                                >
                                    Manage Enrollments
                                </a>

                                {{-- Global students (create/edit/delete student accounts) --}}
                                <a
                                    href="{{ route("admin.students.index") }}"
                                    class="px-3 py-1.5 text-sm border rounded hover:bg-gray-50"
                                >
                                    Manage Students
                                </a>

                                {{-- Announcements center --}}
                                <a
                                    href="{{ route("admin.announcements.index") }}"
                                    class="px-3 py-1.5 text-sm border rounded hover:bg-gray-50"
                                >
                                    Announcements
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Quick actions --}}
    <section class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
        <a
            href="{{ route("admin.courses.index") }}"
            class="p-5 bg-white border rounded hover:bg-gray-50"
        >
            <div class="text-sm text-gray-600">Browse</div>
            <div class="text-lg font-semibold">All Courses</div>
        </a>

        <a
            href="{{ route("admin.students.index") }}"
            class="p-5 bg-white border rounded hover:bg-gray-50"
        >
            <div class="text-sm text-gray-600">Browse</div>
            <div class="text-lg font-semibold">All Students</div>
        </a>

        <a
            href="{{ route("admin.announcements.index") }}"
            class="p-5 bg-white border rounded hover:bg-gray-50"
        >
            <div class="text-sm text-gray-600">Browse</div>
            <div class="text-lg font-semibold">All Announcements</div>
        </a>
    </section>
</x-layout>
