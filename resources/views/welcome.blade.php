<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>LMS Portal</title>
        @vite(["resources/css/app.css", "resources/js/app.js"])
    </head>
    @php
        $studentcolor = "bg-purple-700";
        $lecturercolor = "bg-slate-500";
    @endphp

    <body class="bg-gray-50 text-gray-800 antialiased font-sans">
        <div
            class="min-h-screen flex flex-col items-center justify-center text-center px-6"
        >
            <h1 class="text-4xl md:text-5xl font-bold text-purple-800">
                Foundation Program Moodle
            </h1>
            <p class="mt-2 text-xs text-gray-400 max-w-xl">
                Learning Management System for Foundation Program.
            </p>
            <h2 class="mt-6 text-2xl font-semibold text-purple-700">
                Log in as:
            </h2>

            <div class="mt-8 space-x-2">
                <a
                    href="{{ route("login") }}"
                    class="inline-block px-6 py-2 {{ $studentcolor }} text-white rounded shadow hover:bg-purple-800"
                >
                    Student
                </a>

                <a
                    href="{{ route("login") }}"
                    class="inline-block px-6 py-2 {{ $lecturercolor }} text-white rounded shadow hover:bg-slate-800"
                >
                    Lecturer
                </a>
                <p class="mt-4 text-gray-600">
                    Don't have an account?
                    <a
                        href="{{ route("register.student") }}"
                        class="inline-block mt-4 text-purple-700 hover:underline"
                    >
                        Register here
                    </a>
                </p>
            </div>

            <footer class="mt-12 text-sm text-gray-400">
                &copy; {{ date("Y") }} Foundation Programs Moodle. Built by
                66's ICT students using Laravel + Tailwind.
            </footer>
        </div>
    </body>
</html>
