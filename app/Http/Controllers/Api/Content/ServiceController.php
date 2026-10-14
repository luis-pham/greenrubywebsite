<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Services\Content\ServiceContentService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    use ResolvesContentLanguage;

    public function index(Request $request)
    {
        return response()->json(ServiceContentService::index($this->resolveLanguage($request)));
    }
}
