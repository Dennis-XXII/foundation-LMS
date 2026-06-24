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

    <body class="w-full bg-white text-gray-800 lg:text-base antialiased">
        <div
            class="min-h-screen flex flex-col items-center justify-center px-6 py-12"
        >
            <!-- Main Content Card -->
            <div
                class="max-w-lg w-full bg-white rounded-2xl shadow-sm p-4 sm:p-10 border border-[#7D3C98]/20"
            >
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1
                        class="text-2xl sm:text-3xl lg:text-4xl font-bold text-[#7D3C98] tracking-tight mb-3"
                    >
                        Foundation Program Moodle
                    </h1>
                </div>

                <!-- Login Section -->
                <div class="space-y-6">
                    <h2
                        class="text-lg sm:text-xl font-semibold text-gray-800 text-center"
                    >
                        Log in as:
                    </h2>

                    <!-- Login Buttons -->
                    <div class="grid grid-cols-2 gap-4">
                        <a
                            href="{{ route("login") }}"
                            class="block w-full px-6 py-3.5 {{ $studentcolor }} text-white text-center rounded-lg shadow-md hover:bg-[#6B2D84] hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 font-medium text-base"
                        >
                            Student
                        </a>

                        <a
                            href="{{ route("login.lecturer") }}"
                            class="block w-full px-6 py-3.5 {{ $lecturercolor }} text-white text-center rounded-lg shadow-md hover:bg-slate-600 hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 font-medium text-base"
                        >
                            Lecturer
                        </a>
                    </div>

                    <!-- Divider -->
                    <div class="relative mt-8 mb-2">
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
                    &copy; {{ date("Y") }} Foundation Program Moodle. All
                    rights reserved.
                </p>
            </footer>
        </div>
    </body>
</html>
