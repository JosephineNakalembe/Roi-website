<?php

use App\Services\ErrorReporter;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            if ($status >= 500) {
                ErrorReporter::report($e, $request);
            }

            // Authenticated admins still get the detailed debug page while app.debug is enabled.
            if ($request->user()?->isAdmin() && config('app.debug')) {
                return null;
            }

            // API/JSON clients get a generic JSON error.
            if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {
                return response()->json(
                    ['message' => "Couldn't connect to server, please try again later."],
                    $status >= 500 ? 500 : $status
                );
            }

            // Client errors (404, 419, 403, ...) keep their normal pages.
            if ($status < 500) {
                return null;
            }

            // Server errors: generic page for buyers, error details go to the admin only.
            return response()->view('errors.500', [], 500);
        });
    })->create();
