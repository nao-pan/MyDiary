<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Http\Request;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
            $exceptions->render(function (AuthorizationException|AccessDeniedHttpException $e, Request $request) {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'アクセスが許可されていません',
            ], 403);
        }

        return redirect()->route('diary.index')
            ->with('error', 'そのページにはアクセスできません');
    });
    })->create();
