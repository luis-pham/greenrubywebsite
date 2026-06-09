<?php

namespace Modules\BackEnd\Http\Middleware;

use Illuminate\Support\Facades\Auth;

class CanCategory
{
    public function handle($request, \Closure $next, $action)
    {
        $user = Auth::user();
        $typeName = $request->route('typeName');
        if (!$user->can('category-' . $typeName . '-' . $action)) {
            return abort(403);
        }

        return $next($request);
    }
}
