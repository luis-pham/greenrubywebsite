<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Services\Content\CruiseContentService;
use Illuminate\Http\Request;

class CruiseController extends Controller
{
    use ResolvesContentLanguage;

    public function index(Request $request)
    {
        return response()->json(CruiseContentService::index($this->resolveLanguage($request)));
    }

    public function show(Request $request, $id)
    {
        if (!ctype_digit((string) $id)) {
            return $this->notFound();
        }

        $data = CruiseContentService::detail((int) $id, $this->resolveLanguage($request));

        return $data === null ? $this->notFound() : response()->json($data);
    }
}
