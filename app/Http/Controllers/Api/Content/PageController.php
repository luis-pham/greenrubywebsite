<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Services\Content\HomepageContentService;
use App\Services\Content\PageConfigContentService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    use ResolvesContentLanguage;

    /**
     * GET /pages/{code}. "homepage" has a dedicated shape; the other hub/static page codes
     * (PageConfigContentService::PAGE_CODES) return {code, seo, noIndex, config, updatedAt}.
     */
    public function show(Request $request, string $code)
    {
        $language = $this->resolveLanguage($request);

        if ($code === 'homepage') {
            return response()->json(HomepageContentService::build($language));
        }

        $data = PageConfigContentService::page($code, $language);

        return $data === null ? $this->notFound() : response()->json($data);
    }
}
