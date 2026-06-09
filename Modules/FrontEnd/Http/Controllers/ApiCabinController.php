<?php
namespace Modules\FrontEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackEnd\Services\AppCabinService;
use Modules\FrontEnd\Services\AppCabinService as FeAppCabinService;
use Modules\FrontEnd\Helpers\FeLanguageUtils;
use Modules\FrontEnd\Helpers\FeUtils;

class ApiCabinController extends Controller
{
    public function getById(Request $request)
    {
        $language = FeLanguageUtils::getCurrentLanguage();
        
        $id = $request->id;
        $obj = AppCabinService::find($id, $language->id);
        if (!$obj) {
            return response()->json([
                'msg' => 'fail',
                'err' => 'Cabin not found'
            ], 404);
        }

        try {
            $listAmenity = FeAppCabinService::getAmenityById($id);
            for ($i = 0; $i < count($listAmenity); $i++) {
                $listAmenity[$i]->amenity_icon = FeUtils::getImageLink($listAmenity[$i]->amenity_icon);
            }
            $obj->amenity = $listAmenity;

            $listFile = FeAppCabinService::getFileById($id);
            for ($i = 0; $i < count($listFile); $i++) {
                $listFile[$i]->thumbnail = FeUtils::getThumbnail(['link' => $listFile[$i]->link, 'w' => 126, 'h' => 68]);
                $listFile[$i]->link = FeUtils::getThumbnail(['link' => $listFile[$i]->link, 'w' => 720, 'h' => 540]);
            }
            $obj->file = $listFile;

            return response()->json([
                'msg' => 'success',
                'data' => $obj
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }
}