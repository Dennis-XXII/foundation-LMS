<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0 final-scale=1.0 user-scalable=no"
        />
        <meta http-equiv="X-UA-Compatible" content="ie=edge" />
        <title>{{ $title ?? "LMS Portal" }}</title>
        @vite("resources/css/app.css")
    </head>

    @php
        $userRole = auth()->user()->role;

        $userColors = [
                "admin" => "bg-green-100",
                "lecturer" => "bg-slate-300",
                "student" => "bg-purple-100", 
        ];
            // Use level filter for header, default to gray
            $headerColor = $userColors[$userRole ?? null] ?? "bg-gray-200";
    @endphp

    <body class="bg-white">
        <div class="flex flex-col min-h-screen">
            <!-- Header with Logo and Title -->
            <header class="border-b border-gray-200 flex {{ $headerColor }} items-center px-1 py-1 lg:px-5 lg:py-3 border-t border-purple-200 shadow-sm md:text-sm lg:text-base justify-between ">
            <div class="flex items-center">
                <!-- Navigation Bar with only logout -->
                    <h1
                        class="font-semibold text-gray-900 mx-auto text-xl md:text-2xl"
                    >
                        FOUNDATION PROGRAM MOODLE
                    </h1>
                </div>
            <div>
                    @auth
                        <form
                            method="POST"
                            action="{{ route("logout") }}"
                            class="inline"
                            onsubmit="return confirmLogout();"
                        >
                            @csrf
                            <button
                                type="submit"
                                class="flex position-sticky text-white mr-1 bg-red-500 px-3 py-2 rounded-[999px] hover:bg-red-600 transition"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="20"
                                    height="20"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="#ffffff"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    
                                >
                                    <path
                                        d="M10 3H6a2 2 0 0 0-2 2v14c0 1.1.9 2 2 2h4M16 17l5-5-5-5M19.8 12H9"
                                    />
                                </svg>
                                <p class="ml-2 text-sm">Log Out</p>
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
            <footer
                class="{{ $headerColor }} text-xs text-center text-gray-500 p-2"
            >
                <p> Designed and Developed by ICT Students.</p>
                <p class="mt-1">&copy; Rangsit International College ({{ date("Y") }}). All rights reserved.</p>
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
