<x-guest-layout>
    <div
        class="flex flex-col items-center justify-center min-h-[86vh] bg-white px-4 my-8"
    >
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-sm p-4 sm:p-10 border border-[#7D3C98]/20">
            <a href="{{ route("welcome") }}" class="block text-xs mb-6 text-[#7D3C98]"
                >&larr; Go back</a
            >
            <h1
                class="text-xl lg:text-2xl font-bold text-center text-[#7D3C98] mb-6"
            >
                Student Login
            </h1>

            <form
                method="POST"
                action="{{ route("login.student.post") }}"
                class="space-y-6"
            >
                @csrf

                @if ($errors->any())
                    <div class="bg-red-100 text-red-800 p-4 rounded-md mb-6">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="space-y-2">
                    <label
                        for="login_identifier"
                        class="block text-sm lg:text-base font-normal text-gray-800"
                    >
                        Student ID
                    </label>
                    <input
                        type="text"
                        name="login_identifier"
                        value="{{ old("login_identifier") }}"
                        class="w-full border border-gray-400 rounded-md px-4 py-2 focus:ring-2 focus:ring-[#b085c2] focus:outline-none placeholder-gray-300"
                        placeholder="eg. 6xxxxxx"
                        required
                    />
                    @error("login_identifier")
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label
                        for="password"
                        class="block text-sm lg:text-base font-normal text-gray-800"
                    >
                        Password
                    </label>
                    <input
                        type="password"
                        name="password"
                        class="w-full border border-gray-400 rounded-md px-4 py-2 focus:ring-2 focus:ring-[#b085c2] focus:outline-none"
                        required
                    />
                    @error("password")
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-8">
                    <button
                        type="submit"
                        class="w-full bg-[#7D3C98] text-white font-medium py-3 rounded-md hover:bg-[#701b94] transition"
                    >
                        Log In
                    </button>
                </div>

                <div class="flex justify-center items-center gap-2 mt-6">
                    <p class="text-gray-800">Don't have an account?</p>
                    <a
                        href="{{ route("register.student") }}"
                        class="text-[#7c369a] hover:text-[#755882] font-semibold hover:underline"
                    >
                        Register
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
