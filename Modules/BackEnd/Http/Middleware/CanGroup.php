<?php

namespace Modules\BackEnd\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CanGroup
{
    public function handle($request, \Closure $next, $action)
    {
        $user = Auth::user();
        $typeName = $request->route('typeName');
        if (!$user->can('group-' . $typeName . '-' . $action)) {
            return abort(403);
        }

        return $next($request);
    }
}
