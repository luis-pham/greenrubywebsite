<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Services\Content\ExperienceContentService;
use Illuminate\Http\Request;

class ExperienceController extends Controller
{
    use ResolvesContentLanguage;

    public function index(Request $request)
    {
        return response()->json(ExperienceContentService::index($this->resolveLanguage($request)));
    }

    public function show(Request $request, $id)
    {
        if (!ctype_digit((string) $id)) {
            return $this->notFound();
        }

        $data = ExperienceContentService::detail((int) $id, $this->resolveLanguage($request));

        return $data === null ? $this->notFound() : response()->json($data);
    }
}
