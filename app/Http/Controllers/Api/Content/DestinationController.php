<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Services\Content\DestinationContentService;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    use ResolvesContentLanguage;

    public function index(Request $request)
    {
        return response()->json(DestinationContentService::index($this->resolveLanguage($request)));
    }

    public function show(Request $request, string $slug)
    {
        $data = DestinationContentService::detail($slug, $this->resolveLanguage($request));

        return $data === null ? $this->notFound() : response()->json($data);
    }
}
