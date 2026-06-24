<x-layout title="Useful Links">
    <nav class="mb-2 text-sm text-gray-600 p-3" aria-label="Breadcrumb">
        <ol class="list-reset flex">
            <li>
                <a
                    href="{{ route("lecturer.dashboard") }}"
                    class="hover:underline"
                >
                    Dashboard
                </a>
                <span class="mx-2">/</span>
            </li>
            <li class="text-black font-semibold">Useful Links</li>
        </ol>
    </nav>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 bg-white rounded-lg shadow border">
        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-6 border-b border-gray-200 gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Useful Links — {{ $course->code }}
                </h1>
                <p class="text-sm text-gray-600">
                    {{ $course->name }}
                </p>
            </div>
            <a
                href="{{ route("lecturer.courses.useful_links.create", $course) }}"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition"
            >
                + Add Useful Link
            </a>
        </div>

        {{-- Flash Success Message --}}
        @if (session("success"))
            <div class="mb-6 p-4 rounded bg-green-50 text-green-700 border border-green-200">
                {{ session("success") }}
            </div>
        @endif

        {{-- Useful Links Table --}}
        @if ($usefulLinks->isEmpty())
            <div class="p-8 text-center text-gray-500 border border-dashed border-gray-300 rounded-lg">
                <p class="text-lg font-medium mb-1">No useful links yet.</p>
                <p class="text-sm">Click "+ Add Useful Link" to add resource links for this course.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-700 uppercase text-xs border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Title</th>
                            <th class="px-6 py-4 font-semibold">Description</th>
                            <th class="px-6 py-4 font-semibold">Link URL</th>
                            <th class="px-6 py-4 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($usefulLinks as $link)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $link->title }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $link->description ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-blue-600 break-all">
                                    <a href="{{ $link->link }}" target="_blank" class="hover:underline">
                                        {{ $link->link }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-3">
                                        <a
                                            href="{{ route("lecturer.useful_links.edit", $link) }}"
                                            class="px-3 py-1 bg-yellow-500 text-white rounded text-xs font-semibold hover:bg-yellow-600 transition"
                                        >
                                            Edit
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route("lecturer.useful_links.destroy", $link) }}"
                                            onSubmit="return confirm('Are you sure you want to delete this link?');"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="px-3 py-1 bg-rose-600 text-white rounded text-xs font-semibold hover:bg-rose-700 transition"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layout>
