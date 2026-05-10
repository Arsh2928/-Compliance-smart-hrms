<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Gracefully handle MongoDB connection failures (internet down, Atlas unreachable, etc.)
        $mongoExceptions = [
            \MongoDB\Driver\Exception\ConnectionTimeoutException::class,
            \MongoDB\Driver\Exception\RuntimeException::class,
            \MongoDB\Driver\Exception\ConnectionException::class,
        ];

        foreach ($mongoExceptions as $exceptionClass) {
            $exceptions->render(function (\Exception $e, $request) use ($exceptionClass) {
                if ($e instanceof $exceptionClass) {
                    \Illuminate\Support\Facades\Log::error('MongoDB connection failed: ' . $e->getMessage());
                    return response()->view('errors.db_unavailable', [], 503);
                }
            });
        }

        // Also catch generic exceptions that are MongoDB-related by message
        $exceptions->render(function (\Exception $e, $request) {
            $msg = strtolower($e->getMessage());
            if (
                str_contains($msg, 'connection refused') ||
                str_contains($msg, 'connection timed out') ||
                str_contains($msg, 'no suitable servers found') ||
                str_contains($msg, 'failed to connect') ||
                str_contains($msg, 'server selection error') ||
                str_contains($msg, 'could not connect to mongodb')
            ) {
                \Illuminate\Support\Facades\Log::error('MongoDB connection failed: ' . $e->getMessage());
                return response()->view('errors.db_unavailable', [], 503);
            }
        });
    })->create();
