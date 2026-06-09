<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AdLanguage;
use Modules\BackEnd\Entities\AppCruise;
use Modules\BackEnd\Entities\AppItinerary;
use Modules\BackEnd\Entities\AppService;
use Modules\BackEnd\Helpers\CruiseUtils;
use Modules\BackEnd\Helpers\Utilities;
use Modules\FrontEnd\Helpers\FeLanguageUtils;

class BookingApiController extends Controller
{
    
    public function departureDates(Request $request)
    {
        $lang = $request->get('lang', 'vi');
        $languages = AdLanguage::whereIn('code', ['vi', 'en'])->get()->keyBy('code');
        $language = $languages->get($lang) ?? $languages->get('vi') ?? AdLanguage::where('is_default', true)->first();

        if (!$language) {
            return response()->json(['success' => false, 'message' => 'Language not found']);
        }

        $query = DB::table('app_cruise_itinerary')
            ->select(DB::raw('DATE(start_at) as start_at'))
            ->join('app_itinerary', 'app_itinerary.id', '=', 'app_cruise_itinerary.itinerary_id')
            ->join('app_cruise', 'app_cruise.id', '=', 'app_cruise_itinerary.cruise_id')
            ->where('app_itinerary.language_id', $language->id)
            ->where('app_cruise.language_id', $language->id);

        if ($request->filled('cruise_id')) {
            $query->where('app_cruise.id', (int) $request->get('cruise_id'));
        }

        if ($request->filled('itinerary_id')) {
            $query->where('app_itinerary.id', (int) $request->get('itinerary_id'));
        }

        $today = date('Y-m-d');
        $query->whereDate('start_at', '>=', $today);

        $rows = $query
            ->groupBy(DB::raw('DATE(start_at)'))
            ->orderBy('start_at', 'asc')
            ->get();

        $items = $rows->map(function ($row) {
            return [
                'start_at' => $row->start_at ? date('Y-m-d', strtotime($row->start_at)) : null,
            ];
        })->filter(function ($item) {
            return !empty($item['start_at']);
        })->values();

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }

    public function itineraries(Request $request)
    {
        $lang = $request->get('lang', 'vi');
        $languages = AdLanguage::whereIn('code', ['vi', 'en'])->get()->keyBy('code');
        $langVi = $languages->get('vi');
        $langEn = $languages->get('en');

        $selectedDate = null;
        $dateParam = $request->get('date');
        if ($dateParam) {
            try {
                $d = new \DateTime($dateParam);
                $selectedDate = $d->format('Y-m-d');
            } catch (\Exception $e) {
                $selectedDate = null;
            }
        }

        $wantAll = ($lang === 'all');
        $languageIds = $wantAll && $langVi && $langEn
            ? [$langVi->id, $langEn->id]
            : [($languages->get($lang) ?? $langVi ?? AdLanguage::where('is_default', true)->first())?->id];

        $languageIds = array_filter($languageIds);

        if (empty($languageIds)) {
            return response()->json(['success' => false, 'message' => 'Language not found']);
        }

        $list = DB::table('app_itinerary')
            ->select(
                'app_itinerary.id as itinerary_id',
                'app_itinerary.name as itinerary_name',
                'app_itinerary.duration',
                'app_itinerary.destination',
                'app_itinerary.language_id',
                'app_cruise.id as cruise_id',
                'app_cruise.name as cruise_name'
            )
            ->join('app_cruise_itinerary', 'app_cruise_itinerary.itinerary_id', '=', 'app_itinerary.id')
            ->join('app_cruise', 'app_cruise.id', '=', 'app_cruise_itinerary.cruise_id')
            ->whereIn('app_itinerary.language_id', $languageIds)
            ->whereIn('app_cruise.language_id', $languageIds)
            ->when($selectedDate, function ($q) use ($selectedDate) {
                $q->whereDate('app_cruise_itinerary.start_at', $selectedDate);
            })
            ->groupBy('app_itinerary.id', 'app_itinerary.name', 'app_itinerary.duration', 'app_itinerary.destination', 'app_itinerary.language_id', 'app_cruise.id', 'app_cruise.name')
            ->orderBy('app_itinerary.id', 'desc')
            ->get();

        $list = $list->unique(function ($row) {
            return $row->itinerary_id . '-' . $row->cruise_id;
        })->values();

        if ($request->get('unique') === 'itinerary') {
            $list = $list->unique(function ($row) {
                return $row->itinerary_id;
            })->values();
        }

        $idToCode = [];
        foreach ($languages as $code => $l) {
            $idToCode[$l->id] = $code;
        }

        $items = $list->map(function ($row) use ($idToCode) {
            $durationLabel = $row->duration ? CruiseUtils::formatDisplayDurationName($row->duration) : '';
            $destination = '';
            try {
                $destArr = json_decode($row->destination ?? '[]', true);
                $destination = is_array($destArr) ? implode(', ', $destArr) : '';
            } catch (\Exception $e) {
            }
            $langCode = $idToCode[$row->language_id] ?? 'vi';
            return [
                'id' => $row->itinerary_id . '-' . $row->cruise_id,
                'itinerary_id' => (int) $row->itinerary_id,
                'cruise_id' => (int) $row->cruise_id,
                'name' => $row->itinerary_name,
                'cruise_name' => $row->cruise_name,
                'duration' => (int) $row->duration,
                'duration_label' => $durationLabel,
                'destination' => $destination,
                'lang' => $langCode,
            ];
        });

        return response()->json([
            'success' => true,
            'lang' => $lang,
            'items' => $items,
        ]);
    }

    /**
     * Get cabin list for booking step \"Select Sanctuary & Guests\".
     * ?lang=vi|en|all, optional ?cruise_id=ID
     */
    public function cabins(Request $request)
    {
        $lang = $request->get('lang', 'vi');
        $selectedDuration = (int) $request->get('duration', 0);
        $languages = AdLanguage::whereIn('code', ['vi', 'en'])->get()->keyBy('code');
        $langVi = $languages->get('vi');
        $langEn = $languages->get('en');

        $wantAll = ($lang === 'all');
        $languageIds = $wantAll && $langVi && $langEn
            ? [$langVi->id, $langEn->id]
            : [($languages->get($lang) ?? $langVi ?? AdLanguage::where('is_default', true)->first())?->id];

        $languageIds = array_filter($languageIds);

        if (empty($languageIds)) {
            return response()->json(['success' => false, 'message' => 'Language not found']);
        }

        $minPriceExpression = '(SELECT MIN(price) FROM app_cabin_price WHERE app_cabin_price.cabin_id = app_cabin.id';
        if ($selectedDuration > 0) {
            $minPriceExpression .= ' AND app_cabin_price.duration = ' . $selectedDuration;
        }
        $minPriceExpression .= ') AS min_price';

        $query = DB::table('app_cabin')
            ->select(
                'app_cabin.id',
                'app_cabin.name',
                'app_cabin.summary',
                'app_cabin.view',
                'app_cabin.capacity',
                'app_cabin.over_capacity_adult',
                'app_cabin.over_capacity_child_6_12',
                'app_cabin.over_capacity_child_2_5',
                'app_cabin.over_capacity_infant',
                'app_cabin.area',
                'app_cabin.image_link',
                'app_cabin.language_id',
                'app_cruise.id as cruise_id',
                'app_cruise.name as cruise_name',
                DB::raw($minPriceExpression)
            )
            ->leftJoin('app_cruise', 'app_cruise.id', '=', 'app_cabin.cruise_id');

        $slugFilter = null;
        if ($lang === 'en') {
            $slugFilter = 'accommodation';
        } elseif ($lang === 'vi') {
            $slugFilter = 'phong-o';
        }

        if ($slugFilter !== null) {
            $query->leftJoin('app_group', function ($join) use ($slugFilter, $languageIds) {
                $join->on('app_group.id', '=', 'app_cabin.group_id')
                    ->where('app_group.type', '=', config('backend.groupType.cabin'));

                if (!empty($languageIds)) {
                    $join->whereIn('app_group.language_id', $languageIds);
                }

                $join->where(function ($q) use ($slugFilter) {
                    if ($slugFilter === 'phong-o') {
                        $q->where('app_group.slug', 'phong-o')
                          ->orWhere('app_group.slug', 'phong_o');
                    } else {
                        $q->where('app_group.slug', $slugFilter);
                    }
                });
            });

            $query->whereNotNull('app_group.id');
        }

        if (!empty($languageIds)) {
            $query->whereIn('app_cabin.language_id', $languageIds);
            $query->whereIn('app_cruise.language_id', $languageIds);
        }

        if ($request->filled('cruise_id')) {
            $query->where('app_cabin.cruise_id', $request->get('cruise_id'));
        }

        $cabins = $query->orderBy('app_cabin.ord', 'asc')->get();

        $idToCode = [];
        foreach ($languages as $code => $l) {
            $idToCode[$l->id] = $code;
        }

        // Get all cabin prices for the selected cabins
        $cabinIds = $cabins->pluck('id')->toArray();
        $cabinPrices = [];
        if (!empty($cabinIds)) {
            $pricesData = DB::table('app_cabin_price')
                ->whereIn('cabin_id', $cabinIds)
                ->when($selectedDuration > 0, function ($q) use ($selectedDuration) {
                    $q->where('duration', $selectedDuration);
                })
                ->select('cabin_id', 'duration', 'guest', 'price')
                ->get();
            
            foreach ($pricesData as $priceRow) {
                $cabinId = (int) $priceRow->cabin_id;
                $duration = (int) $priceRow->duration;
                $guest = (int) $priceRow->guest;
                
                if (!isset($cabinPrices[$cabinId])) {
                    $cabinPrices[$cabinId] = [];
                }
                if (!isset($cabinPrices[$cabinId][$duration])) {
                    $cabinPrices[$cabinId][$duration] = [];
                }
                $cabinPrices[$cabinId][$duration][$guest] = (float) $priceRow->price;
            }
        }

        $items = $cabins->map(function ($row) use ($idToCode, $cabinPrices) {
            $langCode = $idToCode[$row->language_id] ?? 'vi';

            $imageUrl = null;
            if (!empty($row->image_link ?? null)) {
                try {
                    $fileLink = Utilities::getFileLink($row->image_link);
                    if ($fileLink) {
                        $imageUrl = asset($fileLink);
                    }
                } catch (\Exception $e) {
                    $imageUrl = null;
                }
            }

            $cabinId = (int) $row->id;
            $prices = $cabinPrices[$cabinId] ?? [];

            return [
                'id' => $cabinId,
                'name' => $row->name,
                'summary' => $row->summary,
                'view' => $row->view,
                'capacity' => (int) ($row->capacity ?? 0),
                'over_capacity_adult' => $row->over_capacity_adult !== null ? (int) $row->over_capacity_adult : null,
                'over_capacity_child_6_12' => $row->over_capacity_child_6_12 !== null ? (int) $row->over_capacity_child_6_12 : null,
                'over_capacity_child_2_5' => $row->over_capacity_child_2_5 !== null ? (int) $row->over_capacity_child_2_5 : null,
                'over_capacity_infant' => $row->over_capacity_infant !== null ? (int) $row->over_capacity_infant : null,
                'area' => $row->area !== null ? (float) $row->area : null,
                'cruise_id' => $row->cruise_id !== null ? (int) $row->cruise_id : null,
                'cruise_name' => $row->cruise_name,
                'min_price' => $row->min_price !== null ? (float) $row->min_price : null,
                'lang' => $langCode,
                'image_url' => $imageUrl,
                'prices' => $prices,
            ];
        });

        return response()->json([
            'success' => true,
            'lang' => $lang,
            'items' => $items,
        ]);
    }

    public function suggestByCabin(Request $request)
    {
        $cabinId = (int) $request->get('cabin_id');
        if (!$cabinId) {
            return response()->json([
                'success' => false,
                'message' => 'cabin_id is required',
            ], 400);
        }

        $lang = $request->get('lang', 'vi');
        $languages = AdLanguage::whereIn('code', ['vi', 'en'])->get()->keyBy('code');
        $langVi = $languages->get('vi');
        $langEn = $languages->get('en');

        $wantAll = ($lang === 'all');
        $languageIds = $wantAll && $langVi && $langEn
            ? [$langVi->id, $langEn->id]
            : [($languages->get($lang) ?? $langVi ?? AdLanguage::where('is_default', true)->first())?->id];

        $languageIds = array_filter($languageIds);

        if (empty($languageIds)) {
            return response()->json(['success' => false, 'message' => 'Language not found']);
        }

        $cabinRow = DB::table('app_cabin')
            ->select(
                'app_cabin.id',
                'app_cabin.language_id',
                'app_cruise.id as cruise_id',
                'app_cruise.name as cruise_name'
            )
            ->leftJoin('app_cruise', 'app_cruise.id', '=', 'app_cabin.cruise_id')
            ->where('app_cabin.id', $cabinId)
            ->first();

        if (!$cabinRow || !$cabinRow->cruise_id) {
            return response()->json([
                'success' => false,
                'message' => 'Cabin or cruise not found',
            ], 404);
        }

        $today = date('Y-m-d');

        $baseQuery = DB::table('app_cruise_itinerary')
            ->select(
                'app_cruise_itinerary.start_at',
                'app_itinerary.id as itinerary_id',
                'app_itinerary.name as itinerary_name',
                'app_itinerary.duration',
                'app_itinerary.destination',
                'app_itinerary.language_id',
                'app_cruise.id as cruise_id',
                'app_cruise.name as cruise_name'
            )
            ->join('app_itinerary', 'app_itinerary.id', '=', 'app_cruise_itinerary.itinerary_id')
            ->join('app_cruise', 'app_cruise.id', '=', 'app_cruise_itinerary.cruise_id')
            ->where('app_cruise.id', $cabinRow->cruise_id);

        if (!empty($languageIds)) {
            $baseQuery->whereIn('app_itinerary.language_id', $languageIds);
            $baseQuery->whereIn('app_cruise.language_id', $languageIds);
        }

        $row = (clone $baseQuery)
            ->whereDate('app_cruise_itinerary.start_at', '>=', $today)
            ->orderBy('app_cruise_itinerary.start_at', 'asc')
            ->first();

        if (!$row) {
            $row = $baseQuery
                ->orderBy('app_cruise_itinerary.start_at', 'asc')
                ->first();
        }

        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'No itinerary found for this cruise',
            ], 404);
        }

        $durationLabel = $row->duration ? CruiseUtils::formatDisplayDurationName($row->duration) : '';
        $destination = '';
        try {
            $destArr = json_decode($row->destination ?? '[]', true);
            $destination = is_array($destArr) ? implode(', ', $destArr) : '';
        } catch (\Exception $e) {
        }

        return response()->json([
            'success' => true,
            'cabin_id' => (int) $cabinId,
            'cruise_id' => (int) $row->cruise_id,
            'itinerary_id' => (int) $row->itinerary_id,
            'itinerary_name' => $row->itinerary_name,
            'cruise_name' => $row->cruise_name,
            'duration' => (int) $row->duration,
            'duration_label' => $durationLabel,
            'destination' => $destination,
            'date' => $row->start_at ? date('Y-m-d', strtotime($row->start_at)) : null,
        ]);
    }

    public function suggestByCruise(Request $request)
    {
        $cruiseId = (int) $request->get('cruise_id');
        if (!$cruiseId) {
            return response()->json([
                'success' => false,
                'message' => 'cruise_id is required',
            ], 400);
        }

        $lang = $request->get('lang', 'vi');
        $languages = AdLanguage::whereIn('code', ['vi', 'en'])->get()->keyBy('code');
        $langVi = $languages->get('vi');
        $langEn = $languages->get('en');

        $wantAll = ($lang === 'all');
        $languageIds = $wantAll && $langVi && $langEn
            ? [$langVi->id, $langEn->id]
            : [($languages->get($lang) ?? $langVi ?? AdLanguage::where('is_default', true)->first())?->id];

        $languageIds = array_filter($languageIds);

        if (empty($languageIds)) {
            return response()->json(['success' => false, 'message' => 'Language not found']);
        }

        $today = date('Y-m-d');

        $baseQuery = DB::table('app_cruise_itinerary')
            ->select(
                'app_cruise_itinerary.start_at',
                'app_itinerary.id as itinerary_id',
                'app_itinerary.name as itinerary_name',
                'app_itinerary.duration',
                'app_itinerary.destination',
                'app_itinerary.language_id',
                'app_cruise.id as cruise_id',
                'app_cruise.name as cruise_name'
            )
            ->join('app_itinerary', 'app_itinerary.id', '=', 'app_cruise_itinerary.itinerary_id')
            ->join('app_cruise', 'app_cruise.id', '=', 'app_cruise_itinerary.cruise_id')
            ->where('app_cruise.id', $cruiseId);

        if (!empty($languageIds)) {
            $baseQuery->whereIn('app_itinerary.language_id', $languageIds);
            $baseQuery->whereIn('app_cruise.language_id', $languageIds);
        }

        $row = (clone $baseQuery)
            ->whereDate('app_cruise_itinerary.start_at', '>=', $today)
            ->orderBy('app_cruise_itinerary.start_at', 'asc')
            ->first();

        if (!$row) {
            $row = $baseQuery
                ->orderBy('app_cruise_itinerary.start_at', 'asc')
                ->first();
        }

        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'No itinerary found for this cruise',
            ], 404);
        }

        $durationLabel = $row->duration ? CruiseUtils::formatDisplayDurationName($row->duration) : '';
        $destination = '';
        try {
            $destArr = json_decode($row->destination ?? '[]', true);
            $destination = is_array($destArr) ? implode(', ', $destArr) : '';
        } catch (\Exception $e) {
        }

        return response()->json([
            'success' => true,
            'cruise_id' => (int) $row->cruise_id,
            'itinerary_id' => (int) $row->itinerary_id,
            'itinerary_name' => $row->itinerary_name,
            'cruise_name' => $row->cruise_name,
            'duration' => (int) $row->duration,
            'duration_label' => $durationLabel,
            'destination' => $destination,
            'date' => $row->start_at ? date('Y-m-d', strtotime($row->start_at)) : null,
        ]);
    }

    public function amenities(Request $request)
    {
        $lang = $request->get('lang', 'vi');
        $itineraryId = $request->get('itinerary_id');
        $languages = AdLanguage::whereIn('code', ['vi', 'en'])->get()->keyBy('code');
        $langVi = $languages->get('vi');
        $langEn = $languages->get('en');

        $wantAll = ($lang === 'all');
        $languageIds = $wantAll && $langVi && $langEn
            ? [$langVi->id, $langEn->id]
            : [($languages->get($lang) ?? $langVi ?? AdLanguage::where('is_default', true)->first())?->id];

        $languageIds = array_filter($languageIds);

        if (empty($languageIds)) {
            return response()->json(['success' => false, 'message' => 'Language not found']);
        }

        $exclusiveType = config('backend.appServiceType.exclusive');

        if ($itineraryId !== null && $itineraryId !== '') {
            $itinerary = AppItinerary::find($itineraryId);
            $services = $itinerary
                ? $itinerary->itineraryServices()
                    ->where('app_service.type', $exclusiveType)
                    ->whereIn('app_service.language_id', $languageIds)
                    ->orderByPivot('ord')
                    ->get()
                : collect();
        } else {
            $services = AppService::query()
                ->where('type', $exclusiveType)
                ->whereIn('language_id', $languageIds)
                ->orderBy('id', 'asc')
                ->get();
        }

        $idToCode = [];
        foreach ($languages as $code => $l) {
            $idToCode[$l->id] = $code;
        }

        $items = $services->map(function ($s) use ($idToCode) {
            $langCode = $idToCode[$s->language_id] ?? 'vi';

            $imageUrl = null;
            if (!empty($s->image_link)) {
                try {
                    $fileLink = Utilities::getFileLink($s->image_link);
                    if ($fileLink) {
                        $imageUrl = asset($fileLink);
                    }
                } catch (\Exception $e) {
                    $imageUrl = null;
                }
            }

            return [
                'id' => (int) $s->id,
                'name' => $s->name,
                'description' => $s->description,
                'price' => $s->price !== null ? (float) $s->price : null,
                'lang' => $langCode,
                'image_url' => $imageUrl,
            ];
        });

        return response()->json([
            'success' => true,
            'lang' => $lang,
            'items' => $items,
        ]);
    }

    public function itineraryDetail($id)
    {
        $itinerary = AppItinerary::with([
            'itineraryActivities' => function ($q) {
                $q->orderBy('app_itinerary_exp_activity.ord', 'asc');
            },
            'itineraryDays.itineraryDayDetails',
        ])->find((int) $id);

        if (!$itinerary) {
            return response()->json(['success' => false, 'message' => 'Itinerary not found'], 404);
        }

        $activities = $itinerary->itineraryActivities->map(function ($a) {
            return [
                'id' => (int) $a->id,
                'name' => $a->name ?? '',
            ];
        });

        $destinations = [];
        try {
            $decoded = json_decode($itinerary->destination ?? '[]', true);
            if (is_array($decoded)) {
                $destinations = array_values($decoded);
            }
        } catch (\Exception $e) {
            $destinations = [];
        }

        return response()->json([
            'success' => true,
            'itinerary_id' => (int) $itinerary->id,
            'activities' => $activities->values()->all(),
            'destinations' => array_values($destinations),
        ]);
    }
}
