<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Services\Content\ItineraryContentService;
use Illuminate\Http\Request;

class ItineraryController extends Controller
{
    use ResolvesContentLanguage;

    public function index(Request $request)
    {
        return response()->json(ItineraryContentService::index($this->resolveLanguage($request)));
    }

    /**
     * GET /itineraries/{cruiseId}/{itineraryId}. Only the itinerary must exist in the language; the
     * payload is built for the canonical cruise (see "canonical") so the frontend can 301 like the Blade site.
     */
    public function show(Request $request, $cruiseId, $itineraryId)
    {
        if (!ctype_digit((string) $cruiseId) || !ctype_digit((string) $itineraryId)) {
            return $this->notFound();
        }

        $data = ItineraryContentService::detail((int) $itineraryId, $this->resolveLanguage($request));

        return $data === null ? $this->notFound() : response()->json($data);
    }
}
