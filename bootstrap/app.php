<?php

declare(strict_types=1);

use App\Http\Responses\ErrorApiResponse;
use App\Http\Responses\ErrorInternalServerErrorResponse;
use App\Http\Responses\ErrorNotFoundResponse;
use App\Http\Responses\ErrorTooManyAttemptsResponse;
use App\Http\Responses\ErrorUnauthenticatedResponse;
use App\Http\Responses\ErrorValidationResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->group('api', [
            EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }
            if ($e instanceof HttpResponseException) {
                return null;
            }

            $response = match (true) {
                $e instanceof AuthenticationException => ErrorUnauthenticatedResponse::make(),
                $e instanceof ValidationException => ErrorValidationResponse::make($e->errors()),
                $e instanceof ThrottleRequestsException => ErrorTooManyAttemptsResponse::make(),
                $e instanceof AuthorizationException, $e instanceof AccessDeniedHttpException => ErrorApiResponse::make($e->getMessage() ?: 'Forbidden', 403),
                $e instanceof RouteNotFoundException, $e instanceof NotFoundHttpException => ErrorNotFoundResponse::make($e->getMessage() ?: 'Resource not found'),
                default => null,
            };

            if (! $response) {
                $message = config('app.debug') ? $e->getMessage() : '';
                $response = ErrorInternalServerErrorResponse::make($message);
            }

            return $response->toResponse($request);
        });
    })->create();
