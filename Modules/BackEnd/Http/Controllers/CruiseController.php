<?php

namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Modules\BackEnd\Http\Requests\Cruise\CruiseItineraryCreateUpdateRequest;
use Modules\BackEnd\Services\AppItineraryService;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Http\Requests\Cruise\CruiseCreateUpdateRequest;
use Modules\BackEnd\Services\AppAmenityService;
use Modules\BackEnd\Services\AppCruiseService;
use Modules\BackEnd\Services\AppServiceService;

class CruiseController extends Controller
{
    private string $baseView = 'backend::cruise.';

    public function index() {
        $title = "Danh sách du thuyền";
        \SEO::setTitle($title);

        $language = LanguageUtils::getCurrentLanguage();

        $list = AppCruiseService::getAllJoinItinerary($language->id);
        $listItinerary = AppItineraryService::getAll($language->id);

        Logging::logInfo("Xem danh sách du thuyền");
        return view($this->baseView . __FUNCTION__, compact('title','list','listItinerary'));
    }

    public function show(Request $request){
        $title = "Chi tiết du thuyền";
        \SEO::setTitle($title);

        $id = $request->route('id');
        $language = LanguageUtils::getCurrentLanguage();

        $cruise = AppCruiseService::find($id,$language->id);
        if($cruise == null){
            abort(404);
        }

        $listAmenity = AppAmenityService::getAll($language->id);
        $listService = AppServiceService::getByType(config('backend.appServiceType.inclusive'),$language->id);

        $isEdit = false;
        $readOnly = true;

        Logging::logInfo("Xem chi tiết du thuyền. ","id = " . $id);
        return view($this->baseView . 'detail', compact('title','cruise','isEdit','readOnly','listAmenity','listService'));
    }

    public function create(){
        $title = "Thêm du thuyền";
        \SEO::setTitle($title);
        $language = LanguageUtils::getCurrentLanguage();

        $listAmenity = AppAmenityService::getAll($language->id);
        $listService = AppServiceService::getByType(config('backend.appServiceType.inclusive'),$language->id);

        $isEdit = false;
        $readOnly = false;
        $cruise = null;
        return view($this->baseView . 'detail',compact('title','listAmenity','listService','cruise','isEdit','readOnly'));
    }

    public function store(CruiseCreateUpdateRequest $request){
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        try{
            $data = $request->validated();
            $data['language_id'] = $language->id;
            $data['green_technology'] = (object)$data['green_technology'];

            $id = AppCruiseService::create($data);

            Logging::logInfo("Thêm du thuyền thành công. ","id = " . $id);

            $route = route(Utilities::getRouteName('backend.cruise.show'), ['languageCode' => $languageCode, 'id' => $id]);
            return redirect($route)->with('flash-message',"Thêm du thuyền thành công!");
        }
        catch(\Exception $ex){
            Logging::logError("Thêm du thuyền lỗi. ", "Exception = " . $ex->getMessage());
            return redirect()->back()->withInput()->withErrors("Thêm du thuyền lỗi!");
        }
    }

    public function edit(Request $request){
        $title = "Cập nhật du thuyền";
        \SEO::setTitle($title);

        $language = LanguageUtils::getCurrentLanguage();
        $id = $request->route('id');

        $cruise = AppCruiseService::find($id,$language->id);
        if($cruise == null){
            abort(404);
        }

        $listAmenity = AppAmenityService::getAll($language->id);
        $listService = AppServiceService::getByType(config('backend.appServiceType.inclusive'),$language->id);

        $isEdit = true;
        $readOnly = false;

        return view($this->baseView . 'detail',compact('title','isEdit','listAmenity','readOnly','cruise','listService'));
    }

    public function update(CruiseCreateUpdateRequest $request){
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');
        $id = $request->route('id');

        try{
            $data = $request->validated();
            $data['language_id'] = $language->id;
            $data['green_technology'] = (object)$data['green_technology'];

            AppCruiseService::update($id,$data);

            Logging::logInfo("Cập nhật du thuyền thành công. ","id = " . $id);

            $route = route(Utilities::getRouteName('backend.cruise.show'), ['languageCode' => $languageCode, 'id' => $id]);
            return redirect($route)->with('flash-message',"Cập nhật du thuyền thành công!");
        }
        catch(\Exception $ex){
            Logging::logError("Cập nhật du thuyền lỗi. ", "Exception = " . $ex->getMessage());
            return redirect()->back()->withInput()->withErrors("Cập nhật du thuyền lỗi!");
        }
    }

    public function destroy(Request $request){
        $id = $request->input('id');

        try {
            AppCruiseService::delete($id);

            Session::flash('flash-message', 'Xóa du thuyền thành công!');
            Logging::LogInfo('Xóa du thuyền thành công.', 'id = ' . json_encode($id, JSON_UNESCAPED_UNICODE));

            return response()->json(['msg' => 'success']);
        } catch (\Exception $e) {
            Session::flash('errors', new MessageBag(['Xóa câu hỏi thường gặp lỗi!']));
            Logging::LogError('Xóa du thuyền lỗi.', 'id = ' . json_encode($id, JSON_UNESCAPED_UNICODE) . '. Exception = ' . $e->getMessage());

            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }

    public function storeItinerary(CruiseItineraryCreateUpdateRequest $request){
        $language = LanguageUtils::getCurrentLanguage();
        $id = $request->route('id');
        $data = $request->validated();

        try{
            AppCruiseService::storeItinerary($id,$language->id,$data);

            Logging::logInfo('Thêm lịch trình cho du thuyền thành công','cruise_id = '.json_encode($id,JSON_UNESCAPED_UNICODE).',itinerary_id = '.json_encode($data['itinerary_id'],JSON_UNESCAPED_UNICODE));

            return response()->json([
                'message' => 'Thêm lịch trình cho du thuyền thành công',
            ],201);
        }
        catch(\Exception $e){
            Logging::LogError('Thêm lịch trình cho du thuyền lỗi.', 'cruise_id = ' . json_encode($id, JSON_UNESCAPED_UNICODE) . ',itinerary_id = ' . json_encode($data['itinerary_id'],JSON_UNESCAPED_UNICODE) . '. Exception = ' . $e->getMessage());

            return response()->json([
                'message' => $e->getMessage()
            ],500);
        }
    }

//    public function updateItinerary(Request $request){
//        $language = LanguageUtils::getCurrentLanguage();
//        $id = $request->route('id');
//        $data = $request->only('id','start_at');
//
//        try{
//            AppCruiseService::updateItinerary($id,$language->id,$data);
//
//            Logging::logInfo('Cập nhật lịch trình cho du thuyền thành công','cruise_id = '.json_encode($id,JSON_UNESCAPED_UNICODE).',itinerary_id = '.json_encode($data['itinerary_id'],JSON_UNESCAPED_UNICODE));
//
//            return response()->json([
//                'msg' => 'success',
//                'content' => $id
//            ]);
//        }
//        catch(\Exception $e){
//            Logging::LogError(' lịch trình cho du thuyền lỗi.', 'cruise_id = ' . json_encode($id, JSON_UNESCAPED_UNICODE) . ',itinerary_id = ' . json_encode($data['itinerary_id'],JSON_UNESCAPED_UNICODE) . '. Exception = ' . $e->getMessage());
//            return response()->json([
//                'msg' => 'fail',
//                'err' => $e->getMessage()
//            ]);
//        }
//    }

    public function destroyItinerary(Request $request){
        $language = LanguageUtils::getCurrentLanguage();
        $id = $request->route('id');
        $data = $request->only('itinerary_id','start_at');

        try{
            AppCruiseService::deleteItinerary($id,$language->id,$data);

            Logging::logInfo('Xóa lịch trình thành công','cruise_id = '.json_encode($id,JSON_UNESCAPED_UNICODE).',itinerary_id = '.json_encode($data['itinerary_id'],JSON_UNESCAPED_UNICODE));

            return response()->json([
                'message' => 'Xóa lịch trình thành công',
            ]);
        }
        catch(\Exception $e) {
            Logging::LogError('Xóa lịch trình lỗi.', 'cruise_id = ' . json_encode($id, JSON_UNESCAPED_UNICODE) . ',itinerary_id = ' . json_encode($data['itinerary_id'],JSON_UNESCAPED_UNICODE) . '. Exception = ' . $e->getMessage());

            return response()->json([
                'message' => $e->getMessage()
            ],500);
        }
    }
}
