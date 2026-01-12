<x-layout>
    <!-- Error Message -->
    @if ($errors->any())
        <div class="bg-red-100 text-red-800 p-4 rounded-md mb-6">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Success Message -->
    @if (session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded-md mb-6">
            {{ session('success') }}
        </div>
    @endif
    <div>
        <h2 class="text-center mb-4 text-[28px] underline">English Foundation RIC</h2>


        <div class=" w-4/5 m-auto p-2 mb-4 text-[12px] mt-5">
            <div id="accordion-collapse-timetable" data-accordion="collapse" class="w-4/5 flex m-auto flex-row gap-1">
        </div>       
    </div>
</x-layout>
