<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Request;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($this->isHttpException($exception)) {
            $statusCode = $exception->getStatusCode();
            $baseUrlBackEnd = route('backend.index', [], false);
            $currentUrl = $request->getPathInfo();
            \SEO::setTitle($statusCode);
            if (\Str::of($currentUrl)->startsWith($baseUrlBackEnd)) {
                return response()->view('backend::error', compact('statusCode'), $statusCode);
            } else {
                return response()->view('frontend::error', compact('statusCode'), $statusCode);
            }
        } elseif ($exception instanceof \PDOException && !config('app.debug')) {
            $statusCode = 500;
            \SEO::setTitle($statusCode);
            return response()->view('error', compact('statusCode'), $statusCode);
        }

        return parent::render($request, $exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $url = '';
        $guard = Arr::get($exception->guards(), 0);
        switch ($guard) {
            case 'admin':
                $lastUrl = $request->getPathInfo();

                $urlIgnore = [
                    route('backend.index', [], $absolute = false),
                    route('backend.auth.logout', [], $absolute = false),
                ];

                if (!in_array($lastUrl, $urlIgnore)) {
                    $url = route('backend.auth.index', ['lastUrl' => $request->getRequestUri()]);
                } else {
                    $url = route('backend.auth.index');
                }
                break;
            default:
                $url = route('frontend.index');
                break;
        }

        return redirect()->guest($url);
    }
}
