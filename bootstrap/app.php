<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\StudentMiddleware;
use App\Http\Middleware\LecturerMiddleware;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Foundation\Configuration\Exceptions;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware (runs on every request)
        $middleware->append([
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            // Add your own like TrustProxies/PreventRequestsDuringMaintenance if you created them
        ]);

        // WEB group (sessions + CSRF must be here for login/forms)
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // API group (basic)
        $middleware->appendToGroup('api', [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Route middleware aliases (use these in routes)
        $middleware->alias([
            'auth'      => \App\Http\Middleware\Authenticate::class,
            'guest'     => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'throttle'  => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'signed'    => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'verified'  => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

            // Your role gates
            'student'   => StudentMiddleware::class,
            'lecturer'  => LecturerMiddleware::class, // or MentorMiddleware if that's your class
            'admin'     => AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
