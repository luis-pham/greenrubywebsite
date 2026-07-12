<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetHtmlCacheHeaders
{
    /**
     * Short browser/CDN cache for anonymous HTML GET responses.
     * Uses private cache when a session cookie is present (CSRF / personalization).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $maxAge
     * @param  string  $visibility  public|private|auto
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $maxAge = 60, $visibility = 'auto')
    {
        $response = $next($request);

        if (!$request->isMethodCacheable() || !$response->isSuccessful()) {
            return $response;
        }

        $maxAge = (int) $maxAge;
        if ($maxAge < 1) {
            return $response;
        }

        $mode = $visibility;
        if ($mode === 'auto') {
            $mode = $request->hasCookie(config('session.cookie')) ? 'private' : 'public';
        }

        if ($mode === 'public') {
            $response->headers->set(
                'Cache-Control',
                sprintf('public, max-age=%d, s-maxage=%d, stale-while-revalidate=%d', $maxAge, $maxAge * 2, $maxAge)
            );
        } else {
            $response->headers->set(
                'Cache-Control',
                sprintf('private, max-age=%d, stale-while-revalidate=%d', $maxAge, (int) max(15, $maxAge / 2))
            );
        }

        $response->headers->remove('Pragma');
        $response->headers->remove('Expires');

        return $response;
    }
}
