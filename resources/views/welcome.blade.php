<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>LMS Portal</title>
        @vite(["resources/css/app.css", "resources/js/app.js"])
    </head>
    <body class="bg-gray-50 text-gray-800 antialiased font-sans">
        <div
            class="min-h-screen flex flex-col items-center justify-center text-center px-6"
        >
            <h1 class="text-4xl md:text-5xl font-bold text-purple-600">
                Foundation Programs Moodle
            </h1>
            <p class="mt-4 text-lg text-gray-600 max-w-xl">
                Learning Management System for Foundation Programs. Enroll in
                courses, access materials and upload assignments.
            </p>

            <div class="mt-8 space-x-4">
                <a
                    href="{{ route("login") }}"
                    class="inline-block px-6 py-2 bg-purple-600 text-white rounded shadow hover:bg-blue-700"
                >
                    Login
                </a>

                <a
                    href="{{ route("register.student") }}"
                    class="inline-block px-6 py-2 bg-white border border-gray-300 text-gray-700 rounded shadow hover:bg-gray-100"
                >
                    Register
                </a>
            </div>

            <footer class="mt-12 text-sm text-gray-400">
                &copy; {{ date("Y") }} Foundation Programs Moodle. Built by
                66's ICT students using Laravel + Tailwind.
            </footer>
        </div>
    </body>
</html>
