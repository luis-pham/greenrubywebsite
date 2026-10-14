<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Services\Content\ArticleContentService;
use App\Services\Content\PageConfigContentService;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    use ResolvesContentLanguage;

    /** GET /articles?category=&page=&k= */
    public function index(Request $request)
    {
        $data = ArticleContentService::index(
            $this->resolveLanguage($request),
            $this->stringQuery($request, 'category'),
            PageConfigContentService::pageNumber($request->query('page')),
            $this->stringQuery($request, 'k')
        );

        return $data === null ? $this->notFound() : response()->json($data);
    }

    /** GET /articles/{categorySlug}/{slug} */
    public function show(Request $request, string $categorySlug, string $slug)
    {
        $data = ArticleContentService::detail($this->resolveLanguage($request), $categorySlug, $slug);

        return $data === null ? $this->notFound() : response()->json($data);
    }

    /** GET /articles/legacy/{id} */
    public function legacy(Request $request, $id)
    {
        if (!ctype_digit((string) $id)) {
            return $this->notFound();
        }

        $data = ArticleContentService::legacy($this->resolveLanguage($request), (int) $id);

        return $data === null ? $this->notFound() : response()->json($data);
    }
}
