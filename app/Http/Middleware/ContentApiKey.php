<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Guards the server-to-server Content API (/api/v1/content/*).
 * The caller must send header X-Content-Key matching config('services.content_api.key').
 */
class ContentApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $expected = (string) config('services.content_api.key', '');
        if ($expected === '') {
            return response()->json(['error' => 'content_api_not_configured'], 503);
        }

        $provided = $request->header('X-Content-Key');
        if (!is_string($provided) || $provided === '' || !hash_equals($expected, $provided)) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        return $next($request);
    }
}
