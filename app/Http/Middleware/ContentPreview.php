<?php

namespace App\Http\Middleware;

use App\Services\Preview\Preview;
use Closure;
use Illuminate\Http\Request;

/**
 * Content API preview reads (phase 1E-d). A request with X-Preview-Key (a secret separate from the content key) and
 * X-Preview-Session previews the session's one entity; anything else is an ordinary public read. A preview header
 * with a wrong key or an unknown/expired session is refused (403), never silently answered publicly. Preview
 * answers are private, uncacheable and noindex.
 */
class ContentPreview
{
    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('X-Preview-Key');
        $session = $request->header('X-Preview-Session');
        // The verify call itself carries the key but no session yet (PreviewController checks the key).
        if (($key === null && $session === null) || optional($request->route())->getName() === 'api.content.preview.verify') {
            return $next($request);
        }
        $expected = (string) config('i18n.preview.key');
        $scope = $expected !== '' && is_string($key) && hash_equals($expected, $key) && is_string($session) ? Preview::session($session) : null;
        if (!$scope) {
            return self::private(response()->json(['error' => 'preview_invalid'], 403));
        }
        Preview::enter($scope);
        try {
            $response = $next($request);
        } finally {
            Preview::leave();
        }

        return self::private($response);
    }

    public static function private($response)
    {
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
