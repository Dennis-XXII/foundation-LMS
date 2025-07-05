<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0 final-scale=1.0 user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'LMS Portal' }}</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-white">
    <div class="flex flex-col min-h-screen">
        <!-- Header with Logo and Title -->
        <header class="border-b border-gray-200">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center justify-center w-full">
                    <img src="/images/logo_new.png" alt="Logo" class="w-14 h-14 md:w-16 md:h-16">
                    <h1 class="font-bold text-black mx-auto text-xl md:text-2xl">LMS Portal</h1>
                    <img src="/images/logo_new.png" alt="Logo" class="w-14 h-14 md:w-16 md:h-16">
                </div>
            </div>

            <!-- Navigation Bar with only logout -->
            <div class="flex text-xs bg-purple-100 justify-end px-1 py-1 lg:px-4 lg:py-2 border-t border-purple-200 shadow-sm md:text-sm lg:text-base">
                @auth
                    <form method="POST" action="{{ route('logout') }}" class="inline" onsubmit="return confirmLogout()">
                        @csrf
                        <button type="submit"
                            class="text-white mr-1 bg-red-500 px-1 py-1 lg:px-4 lg:py-2 rounded-[25%] hover:bg-red-600 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 3H6a2 2 0 0 0-2 2v14c0 1.1.9 2 2 2h4M16 17l5-5-5-5M19.8 12H9" />
                            </svg>
                        </button>
                    </form>
                @endauth
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 p-4">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-[#7D3C98] text-xs lg:text-base text-center text-white p-4">
            <p>&copy; {{ date('Y') }} LMS. All rights reserved.</p>
        </footer>
    </div>

    <script>
        function confirmLogout() {
            return confirm('Are you sure you want to log out?');
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
</body>
</html>
