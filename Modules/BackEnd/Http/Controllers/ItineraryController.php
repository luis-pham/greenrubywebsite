<?php

namespace Modules\BackEnd\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Modules\BackEnd\Entities\AppExpActivity;
use Modules\BackEnd\Entities\AppItinerary;
use Modules\BackEnd\Entities\AppService;
use Modules\BackEnd\Helpers\CruiseUtils;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Http\Requests\Itinerary\ItineraryCreateUpdateRequest;
use Modules\BackEnd\Http\Requests\Itinerary\ItineraryIndexPagingRequest;
use Modules\BackEnd\Services\AppExpActivityService;
use Modules\BackEnd\Services\AppItineraryService;
use Modules\BackEnd\Services\AppServiceService;

class ItineraryController extends Controller
{
    private $baseView = 'backend::itinerary.';

    public function index(ItineraryIndexPagingRequest $request) {
        $title = "Danh sách hành trình";
        \SEO::setTitle($title);

        $language = LanguageUtils::getCurrentLanguage();

        $params = $request->validated();

        $list = AppItineraryService::getAll($language->id,$params);
        $listDuration = CruiseUtils::getListDuration();

        Logging::logInfo("Xem danh sách hành trình");
        return view($this->baseView . __FUNCTION__, compact('title','list','listDuration'));
    }

    public function show(Request $request){
        $title = "Chi tiết hành trình";
        \SEO::setTitle($title);

        $id = $request->route('id');
        $language = LanguageUtils::getCurrentLanguage();
        $itinerary = AppItineraryService::findJoin($id,$language->id);

        if($itinerary == null){
            abort(404);
        }

        $isEdit = false;
        $readOnly = true;

        $listService = AppServiceService::getAll($language->id);

        $listInclusiveService = $listService->filter(function($s){
            return $s->type === config('backend.appServiceType.inclusive');
        });
        $listExclusiveService = $listService->filter(function($s){
            return $s->type === config('backend.appServiceType.exclusive');
        });

        $listActivity = AppExpActivityService::getAll($language->id);

        Logging::logInfo("Xem chi tiết hành trình. ","id = " . $id);
        return view($this->baseView . 'detail',compact('title','itinerary','isEdit','readOnly','listExclusiveService','listInclusiveService','listActivity'));
    }

    public function create(){
        $title = "Thêm hành trình";
        \SEO::setTitle($title);

        $language = LanguageUtils::getCurrentLanguage();

        $isEdit = false;
        $readOnly = false;
        $itinerary = null;
        $listService = AppServiceService::getAll($language->id);
        $listInclusiveService = $listService->filter(function($s){
            return $s->type === config('backend.appServiceType.inclusive');
        });
        $listExclusiveService = $listService->filter(function($s){
           return $s->type === config('backend.appServiceType.exclusive');
        });
        $listActivity = AppExpActivityService::getAll($language->id);

        return view($this->baseView . 'detail',compact('title','itinerary','isEdit','readOnly','listExclusiveService','listInclusiveService','listActivity'));
    }

    public function store(ItineraryCreateUpdateRequest $request){
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        try{
            $data = $request->validated();
            $data['language_id'] = $language->id;
            $data['start_time'] = $this->resolveItineraryStartTime($data['itinerary_days'] ?? []);

            $id = AppItineraryService::create($data);

            Logging::logInfo("Thêm hành trình thành công. ","id = " . $id);

            $route = route(Utilities::getRouteName('backend.itinerary.show'), ['languageCode' => $languageCode, 'id' => $id]);
            return redirect($route)->with('flash-message',"Thêm hành trình thành công!");
        }
        catch(\Exception $ex){
            Logging::logError("Thêm hành trình lỗi. ", "Exception = " . $ex->getMessage());
            return redirect()->back()->withInput()->withErrors("Thêm hành trình lỗi!");
        }
    }

    public function edit(Request $request){
        $title = "Cập nhật hành trình";
        \SEO::setTitle($title);

        $language = LanguageUtils::getCurrentLanguage();
        $id = $request->route('id');

        $itinerary = AppItineraryService::findJoin($id,$language->id);
        if($itinerary == null){
            abort(404);
        }
        $isEdit = true;
        $readOnly = false;

        $listService = AppServiceService::getAll($language->id);
        $listInclusiveService = $listService->filter(function($s){
            return $s->type === config('backend.appServiceType.inclusive');
        });
        $listExclusiveService = $listService->filter(function($s){
           return $s->type === config('backend.appServiceType.exclusive');

        });
        $listActivity = AppExpActivityService::getAll($language->id);

        Logging::logInfo("Xem chi tiết hành trình. ","id = " . $id);
        return view($this->baseView . 'detail',compact('title','itinerary','isEdit','readOnly','listExclusiveService','listInclusiveService','listActivity'));
    }

    public function update(ItineraryCreateUpdateRequest $request){
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');
        $id = $request->route('id');
        try{
            $data = $request->validated();
            $data['language_id'] = $language->id;
            $data['start_time'] = $this->resolveItineraryStartTime($data['itinerary_days'] ?? []);

            AppItineraryService::update($id,$data);

            Logging::logInfo("Cập nhật hành trình thành công. ","id = " . $id);

            $route = route(Utilities::getRouteName('backend.itinerary.show'), ['languageCode' => $languageCode, 'id' => $id]);
            return redirect($route)->with('flash-message',"Cập nhật hành trình thành công!");
        }
        catch(\Exception $ex){
            Logging::logError("Cập nhật hành trình lỗi. ", "Exception = " . $ex->getMessage());
            return redirect()->back()->withInput()->withErrors("Cập nhật hành trình lỗi!");
        }
    }

    public  function destroy(Request $request){
        $id = $request->input('id');

        try {
            AppItineraryService::delete($id);

            Session::flash('flash-message', 'Xóa hành trình thành công!');
            Logging::LogInfo('Xóa hành trình thành công.', 'id = ' . json_encode($id, JSON_UNESCAPED_UNICODE));

            return response()->json(['msg' => 'success']);
        } catch (\Exception $e) {
            Session::flash('errors', new MessageBag(['Xóa câu hỏi thường gặp lỗi!']));
            Logging::LogError('Xóa hành trình lỗi.', 'id = ' . json_encode($id, JSON_UNESCAPED_UNICODE) . '. Exception = ' . $e->getMessage());

            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }

    private function resolveItineraryStartTime(array $itineraryDays): ?string
    {
        $firstDay = $itineraryDays[0] ?? null;
        if (!$firstDay) {
            return null;
        }

        $details = $firstDay['itinerary_day_details'] ?? [];
        if (empty($details)) {
            return null;
        }

        $firstDetail = collect($details)->sortBy('time')->first();

        return is_array($firstDetail) ? ($firstDetail['time'] ?? null) : null;
    }
}
