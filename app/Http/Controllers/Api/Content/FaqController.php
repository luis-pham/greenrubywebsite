<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Services\Content\FaqContentService;
use App\Services\Content\PageConfigContentService;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use ResolvesContentLanguage;

    /** GET /faqs?group=&page=&k= */
    public function index(Request $request)
    {
        $data = FaqContentService::index(
            $this->resolveLanguage($request),
            $this->stringQuery($request, 'group'),
            PageConfigContentService::pageNumber($request->query('page')),
            $this->stringQuery($request, 'k')
        );

        return $data === null ? $this->notFound() : response()->json($data);
    }
}
