<?php

namespace Modules\FrontEnd\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ConfigureFrontendSession
{
    /**
     * Apply FrontEnd-specific session settings before StartSession runs.
     */
    public function handle(Request $request, Closure $next)
    {
        $frontend = config('session.frontend', []);

        config([
            'session.lifetime' => (int) ($frontend['lifetime'] ?? 30 * 24 * 60),
            'session.expire_on_close' => (bool) ($frontend['expire_on_close'] ?? false),
        ]);

        return $next($request);
    }
}
