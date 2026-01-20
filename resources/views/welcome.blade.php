<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>LMS Portal - Foundation Program Moodle</title>
        @vite(["resources/css/app.css", "resources/js/app.js"])
    </head>
    @php
        $studentcolor = "bg-[#7D3C98]";
        $lecturercolor = "bg-slate-500";
    @endphp

    <body class="w-full bg-gray-50 text-gray-800 lg:text-base antialiased">
        <div
            class="min-h-screen flex flex-col items-center justify-center px-6 py-12"
        >
            <!-- Main Content Card -->
            <div
                class="max-w-xl w-full bg-white rounded-2xl shadow-lg p-4 sm:p-10"
            >
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1
                        class="text-3xl sm:text-4xl lg:text-5xl font-bold text-[#7D3C98] leading-tight mb-3"
                    >
                        Foundation Program
                        <br />
                        Moodle
                    </h1>
                    <p class="text-gray-600 text-sm sm:text-base">
                        Foundation Program Learning Management System
                    </p>
                </div>

                <!-- Login Section -->
                <div class="space-y-6">
                    <h2
                        class="text-lg sm:text-xl font-semibold text-gray-800 text-center"
                    >
                        Log in as:
                    </h2>

                    <!-- Login Buttons -->
                    <div class="space-y-3">
                        <a
                            href="{{ route("login") }}"
                            class="block w-full px-6 py-3.5 {{ $studentcolor }} text-white text-center rounded-lg shadow-md hover:bg-[#6B2D84] hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 font-medium text-base"
                        >
                            Student Login
                        </a>

                        <a
                            href="{{ route("login") }}"
                            class="block w-full px-6 py-3.5 {{ $lecturercolor }} text-white text-center rounded-lg shadow-md hover:bg-slate-600 hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 font-medium text-base"
                        >
                            Lecturer Login
                        </a>
                    </div>

                    <!-- Divider -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-white text-gray-500">
                                Don't have an account?
                            </span>
                        </div>
                    </div>

                    <!-- Registration Link -->
                    <div class="text-center">
                        <a
                            href="{{ route("register.student") }}"
                            class="inline-block text-[#7D3C98] hover:text-[#6B2D84] font-medium hover:underline transition-colors duration-200"
                        >
                            Register Now
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="mt-8 text-sm text-gray-500 text-center max-w-md">
                <p>
                    &copy; {{ date("Y") }} Foundation Programs Moodle. All
                    rights reserved.
                </p>
            </footer>
        </div>
    </body>
</html>
