<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Services\Content\GalleryContentService;
use App\Services\Content\PageConfigContentService;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    use ResolvesContentLanguage;

    /** GET /gallery?category=&page= */
    public function index(Request $request)
    {
        $data = GalleryContentService::index(
            $this->resolveLanguage($request),
            $this->stringQuery($request, 'category'),
            PageConfigContentService::pageNumber($request->query('page'))
        );

        return $data === null ? $this->notFound() : response()->json($data);
    }
}
