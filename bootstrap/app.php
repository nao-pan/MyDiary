<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Console\Scheduling\Schedule;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Http\Request;
use App\Http\Middleware\AdminMiddleware;
use App\Console\Commands\BuildDailyMetrics;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 未認証ユーザーは必ず /login へ
        $middleware->redirectGuestsTo(fn(Request $request) => route('login'));
        // 管理者ユーザーのみアクセスを許可
        $middleware->alias(['admin' => AdminMiddleware::class]);
    })
    ->withCommands([
        BuildDailyMetrics::class,
    ])
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('metrics:build-daily')->dailyAt('03:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthorizationException|AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'アクセスが許可されていません',
                ], 403);
            }

            abort(403);
        });
    })->create();
