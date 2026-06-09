<?php
namespace Modules\FrontEnd\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Modules\BackEnd\Services\AdLanguageService;
use Modules\BackEnd\Services\AppCruiseService;
use Modules\BackEnd\Services\AppCabinService;
use Modules\BackEnd\Services\AppItineraryService;
use Modules\BackEnd\Services\AppExpActivityService;
use Modules\BackEnd\Services\AppGroupService;
use Modules\BackEnd\Helpers\Utilities;
use Modules\FrontEnd\Helpers\FeUtils;

class PublicDataController extends Controller
{
    public function index(Request $request)
    {
        $languageCode = $request->route('languageCode');
        $type = $request->route('type');

        $language = AdLanguageService::findByCode($languageCode);
        if (!$language) {
            $language = AdLanguageService::getDefaultLanguage();
        }

        $languageId = $language->id;

        switch ($type) {
            case 'cruises':
                $data = AppCruiseService::getAll($languageId)
                ->map(function ($x) {
                    $greenTech = null;
                    if ($x->green_technology) {
                        $gt = is_string($x->green_technology) ? json_decode($x->green_technology) : (object)$x->green_technology;
                        if (!empty($gt->name)) {
                            $greenTech = [
                                'name' => $gt->name,
                                'description' => $gt->description,
                                'image_link' => isset($gt->image_link) ? asset(FeUtils::getImageLink($gt->image_link)) : null,
                            ];
                        }
                    }
                    return [
                        'id' => $x->id,
                        'name' => $x->name,
                        'summary' => $x->summary,
                        'content' => $x->content,
                        'image_link' => asset(FeUtils::getImageLink($x->image_link)),
                        'cover_link' => asset(FeUtils::getImageLink($x->cover_link)),
                        'star_rating' => $x->star_rating,
                        'capacity' => $x->capacity,
                        'total_floor' => $x->total_floor,
                        'dimension_length' => $x->dimension_length,
                        'year_built' => $x->year_built,
                        'description_design' => $x->description_design,
                        
                        'green_technology' => $greenTech,
                    ];
                })
                ->values();
                break;
            case 'cabins':
                $data = AppCabinService::getAll($languageId)
                ->map(function ($x) {
                    return [
                        'id' => $x->id,
                        'cruise_id' => $x->cruise_id,
                        'name' => $x->name,
                        'summary' => $x->summary,
                        'content' => strip_tags($x->content),
                        'image_link' => asset(FeUtils::getImageLink($x->image_link)),
                        'view' => $x->view,
                        'cabin_class' => $x->cabin_class,
                        'capacity' => $x->capacity,
                        'area' => $x->area,
                        'discount_percent' => $x->discount_percent,
                    ];
                })
                ->values();
                break;

            case 'itineraries':
                $data = AppItineraryService::getAll($languageId)
                ->map(function ($x) {
                    $rawNotes = is_string($x->important_note) ? json_decode($x->important_note, true) : $x->important_note;
                        
                    $cleanedNotes = collect($rawNotes)->map(function($note) {
                        return [
                            'content' => isset($note['content']) ? trim(strip_tags($note['content'])) : '',
                            'image_link' => isset($note['image_link']) ? asset(FeUtils::getImageLink($note['image_link'])) : null,
                        ];
                    })->filter(fn($n) => !empty($n['content']))->values(); 
                
                    $destinations = is_string($x->destination) ? json_decode($x->destination, true) : $x->destination;
                
                    return [
                        'id' => (int) $x->id,
                        'name' => $x->name,
                        'description' => trim(strip_tags($x->description)),
                        'duration' => $x->duration,
                        'image_link' => asset(FeUtils::getImageLink($x->image_link)),
                        'cover_link' => asset(FeUtils::getImageLink($x->cover_link)),                 
                        'important_note' => $cleanedNotes,    
                        'destination' => $destinations, 
                        'start_time' => $x->start_time,
                        'bay' => (int) $x->bay,
                    ];
                })
                ->values();
                break;
               
            case 'experiences':
                $data = AppExpActivityService::getAll($languageId)
                ->map(function ($x) {
                    $note = is_string($x->note) ? json_decode($x->note, true) : $x->note;
                    return [
                        'id' => $x->id,
                        'name' => $x->name,
                        'group_name' => $x->group_id ? AppGroupService::find($x->group_id, config('backend.groupType.expActivity'), $x->language_id)->name : null,
                        'summary' => $x->summary,
                        'content' => strip_tags($x->content),
                        'image_link' => asset(FeUtils::getImageLink($x->image_link)),
                        'cover_link' => asset(FeUtils::getImageLink($x->cover_link)),
                        'duration' => $x->duration,
                        'start_time' => $x->start_time,
                        'end_time' => $x->end_time,
                        'note' => $note,
                        'is_featured' => (bool) $x->is_featured,
                    ];
                })
                ->values();
                break;

            case 'departures':
                $data = AppCruiseService::getAllJoin($languageId)
                    ->map(function ($x) {
                        $rawNotes = is_string($x->important_note) ? json_decode($x->important_note, true) : $x->important_note;
                        $cleanedNotes = collect($rawNotes ?: [])->map(function ($note) {
                            return [
                                'content' => isset($note['content']) ? trim(strip_tags($note['content'])) : '',
                                'image_link' => isset($note['image_link']) ? asset(FeUtils::getImageLink($note['image_link'])) : null,
                            ];
                        })->filter(fn ($n) => !empty($n['content']))->values();

                        $destinations = is_string($x->destination) ? json_decode($x->destination, true) : $x->destination;
                        $greenTech = null;
                        if ($x->green_technology) {
                            $gt = is_string($x->green_technology) ? json_decode($x->green_technology) : (object)$x->green_technology;
                            if (!empty($gt->name)) {
                                $greenTech = [
                                    'name' => $gt->name,
                                    'description' => $gt->description,
                                    'image_link' => isset($gt->image_link) ? asset(FeUtils::getImageLink($gt->image_link)) : null,
                                ];
                            }
                        }
                        return [
                            'id' => (int) $x->id,
                            'language_id' => (int) $x->language_id,
                            'name' => $x->name,
                            'summary' => $x->summary,
                            'content' => $x->content,
                            'image_link' => asset(FeUtils::getImageLink($x->image_link)),
                            'cover_link' => asset(FeUtils::getImageLink($x->cover_link)),
                            'star_rating' => $x->star_rating,
                            'capacity' => $x->capacity,
                            'total_floor' => $x->total_floor,
                            'dimension_length' => $x->dimension_length,
                            'year_built' => $x->year_built,
                            'description_design' => $x->description_design,
                            'green_technology' => $greenTech,
                            'created_at' => $x->created_at,
                            'created_by' => $x->created_by,
                            'updated_at' => $x->updated_at,
                            'updated_by' => $x->updated_by,

                            'description' => trim(strip_tags($x->description)),
                            'bay' => (int) $x->bay,
                            'duration' => $x->duration,
                            'important_note' => $cleanedNotes,
                            'destination' => $destinations,
                            'start_time' => $x->start_time,
                            'start_at' => $x->start_at,
                        ];
                    })
                    ->values();
                break;
        }

        return response()->json([
            'meta' => [
                'language' => $language->code,
                'count' => $data->count(),
            ],
            'data' => $data,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}