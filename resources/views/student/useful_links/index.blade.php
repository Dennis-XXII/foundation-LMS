<x-layout title="Useful Links">
    <nav class="mb-2 text-sm text-gray-600 p-3" aria-label="Breadcrumb">
        <ol class="list-reset flex">
            <li>
                <a
                    href="{{ route("student.dashboard") }}"
                    class="hover:underline"
                >
                    Dashboard
                </a>
                <span class="mx-2">/</span>
            </li>
            <li class="text-black font-semibold">Links</li>
        </ol>
    </nav>

    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow border border-gray-100">
        {{-- Header Section --}}
        <div class="pb-6 border-b border-gray-200 bg-[#7D3C98]/20 p-4 rounded-t-lg">
            <h1 class="text-2xl font-bold text-[#7D3C98]">
                Useful External Links : {{ $course->code }}
            </h1>
            <p class="text-sm text-gray-700">
                {{ $course->name }}
            </p>
        </div>

        {{-- Useful Links List --}}
        @if ($usefulLinks->isEmpty())
            <div class="p-4 text-center text-gray-500 border border-dashed border-gray-300 rounded-lg bg-gray-50">
                <p class="text-lg font-medium mb-1">No useful links found.</p>
                <p class="text-sm">Your lecturer has not added any useful resource links for this course yet.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 lg:p-4">
                @foreach ($usefulLinks as $link)
                    <div class="relative flex flex-col justify-between border border-gray-200 rounded-xl p-4 bg-white hover:shadow-md hover:border-[#7D3C98]/30 transition group">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 group-hover:text-[#7D3C98] transition">
                                {{ $link->title }}
                            </h2>
                            <p class="text-xs text-gray-500 mt-1 mb-2 leading-relaxed">
                                {{ $link->description ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="pt-1 border-t border-gray-100 flex items-center justify-between">
                            <a
                                href="{{ $link->link }}"
                                target="_blank"
                                class="text-xs font-medium text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1.5 break-all"
                            >
                                <span>Go to Link</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="2 2 20 20" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layout>
