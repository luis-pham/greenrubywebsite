<?php

namespace App\Http\Middleware;

use App\Services\Publication\PublicationContext;
use Closure;
use Illuminate\Http\Request;

/** Marks a public read: unpublished rows are hidden from it (PublishedScope, phase 1C-2). */
class PublicContent
{
    public function handle(Request $request, Closure $next)
    {
        PublicationContext::enterPublic();

        return $next($request);
    }
}
