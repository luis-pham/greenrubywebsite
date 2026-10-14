<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Services\Content\CabinContentService;
use Illuminate\Http\Request;

class CabinController extends Controller
{
    use ResolvesContentLanguage;

    public function index(Request $request)
    {
        $language = $this->resolveLanguage($request);
        $cruiseId = $request->query('cruiseId');
        $cruiseId = is_numeric($cruiseId) ? (int) $cruiseId : null;

        $items = CabinContentService::listAccommodation((int) $language->id, $cruiseId)
            ->map(fn ($cabin) => CabinContentService::summary($cabin))
            ->values();

        return response()->json(['items' => $items]);
    }

    public function show(Request $request, $id)
    {
        if (!ctype_digit((string) $id)) {
            return $this->notFound();
        }

        $language = $this->resolveLanguage($request);

        $cabin = CabinContentService::findAccommodation((int) $id, (int) $language->id);
        if (!$cabin) {
            return $this->notFound();
        }

        return response()->json(CabinContentService::detail($cabin, $language));
    }
}
