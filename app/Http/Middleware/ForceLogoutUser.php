<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ForceLogoutUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard('admin')->check()) {
            $obj = Auth::guard('admin')->user();
            if ($obj->status != config('backend.userStatus')['actived']) {
                Auth::guard('admin')->logout();
                return redirect()->route('backend.auth.index', ['lastUrl' => $request->getRequestUri()]);
            }
        }
        
        if (Auth::check()) {
            $obj = Auth::user();
            if ($obj->status != config('backend.userStatus')['actived']) {
                Auth::logout();
                return redirect()->route('frontend.index');
            }
        }

        return $next($request);
    }
}
