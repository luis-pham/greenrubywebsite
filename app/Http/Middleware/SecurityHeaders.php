<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Baseline security headers on every Laravel response (admin, Content/booking APIs, /thumbnail), matching
 * the Next.js frontend. Headers already set by a response (e.g. ImageController's nosniff) are kept.
 * Payment pages may be framed by the gateway, so they get no framing restriction.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Strict-Transport-Security' => 'max-age=31536000',
        ];
        if (!$request->is('payment/*', 'api/payment/*')) {
            $headers['X-Frame-Options'] = 'SAMEORIGIN';
            $headers['Content-Security-Policy'] = "frame-ancestors 'self'; upgrade-insecure-requests";
        }
        foreach ($headers as $name => $value) {
            if (!$response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }
}
