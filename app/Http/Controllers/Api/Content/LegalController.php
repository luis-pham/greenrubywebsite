<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Services\Content\LegalContentService;
use Illuminate\Http\Request;

class LegalController extends Controller
{
    use ResolvesContentLanguage;

    public function show(Request $request)
    {
        return response()->json(LegalContentService::build($this->resolveLanguage($request)));
    }
}
