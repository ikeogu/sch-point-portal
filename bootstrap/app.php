<?php

use App\Http\Middleware\CustomCors;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            /*       Route::prefix('auth')
            ->group(base_path('routes/routes_includes/auth.php'));

            Route::middleware('api')
                ->prefix('admin')
                ->group(base_path('routes/routes_includes/admin.php'));

            Route::middleware('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('api')
                ->prefix('farmer')
                ->group(base_path('routes/routes_includes/farmer.php'));
 */
        }

    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->alias([
            'log.sanctum.token' => \App\Http\Middleware\LogSanctumToken::class,
        ]);

        $middleware->validateCsrfTokens(except: [
         "https://portal.school-point.com/*"
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'status' => 'failed',
                    'message' =>  'Unauthenticated. Please login to proceed'
                ], Response::HTTP_UNAUTHORIZED);
            }

            if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                ], Response::HTTP_FORBIDDEN);
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return response()->json([
                    'status' => 'failed',
                    'message' =>  'Requested resource is not found'
                ], Response::HTTP_NOT_FOUND);
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'status' => 'failed',
                    'errors' => $e->validator->errors()->all()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($e instanceof ThrottleRequestsException) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $e->getMessage()
                ], Response::HTTP_TOO_MANY_REQUESTS);
            }

            if ($e instanceof TokenMismatchException) {
                return response()->json([
                    'status' => 'failed',
                    'message' =>  $e->getMessage()
                ], Response::HTTP_FORBIDDEN);
            }

            return response()->json([
                'status' => 'failed',
                'message' =>  $e->getMessage()
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        });
    })->create();