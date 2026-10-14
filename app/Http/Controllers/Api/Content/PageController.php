<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Services\Content\HomepageContentService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    use ResolvesContentLanguage;

    /** GET /pages/{code}. Only pages migrated to the Next.js frontend are exposed. */
    public function show(Request $request, string $code)
    {
        $language = $this->resolveLanguage($request);

        return match ($code) {
            'homepage' => response()->json(HomepageContentService::build($language)),
            default => $this->notFound(),
        };
    }
}
