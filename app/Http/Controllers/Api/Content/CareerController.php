<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Services\Content\CareerContentService;

/** Job postings of the website's /tuyen-dung pages (Vietnamese only, no ?lang). Drafts are never served. */
class CareerController extends Controller
{
    public function index()
    {
        return response()->json(CareerContentService::index());
    }

    public function show(string $slug)
    {
        $data = CareerContentService::detail($slug);

        return $data === null ? response()->json(['error' => 'not_found'], 404) : response()->json($data);
    }
}
