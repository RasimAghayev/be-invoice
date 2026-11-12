<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->prefix('v1')->group(function (): void {
    Route::get('health', fn () => response()->json([
        'timestamp' => now()->toIso8601String(),
        'path' => 'api/v1/health',
        'method' => 'GET',
        'error' => null,
        'result' => ['status' => 'ok'],
    ]));

    require __DIR__.'/api/auth.php';
    require __DIR__.'/api/invoices.php';
    require __DIR__.'/api/integrations.php';
});

Route::middleware('auth:sanctum')->get('/user', fn (Request $request) => $request->user());
