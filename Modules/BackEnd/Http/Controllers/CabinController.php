<?php
namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Modules\BackEnd\Http\Requests\CabinRequest;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Helpers\FacilityProfileUtils;
use Modules\BackEnd\Services\AppCabinService;
use Modules\BackEnd\Services\AppCruiseService;
use Modules\BackEnd\Entities\AppAmenity;
use Modules\BackEnd\Entities\AppCabinRoom;
use Modules\BackEnd\Entities\AppCabinAmenity;
use Modules\BackEnd\Entities\AppCabinPrice;
use Modules\BackEnd\Entities\AppCabinSuitableAudience;
use Modules\BackEnd\Entities\AppGroup;

class CabinController extends Controller
{
    private $baseView = 'backend::cabin.';
    
    public function index(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = __('backend::cabin.page_index');

        \SEO::setTitle($title);

        $listCruise = AppCruiseService::getAll($language->id)->pluck('name', 'id');
        $listCabinType = AppGroup::where('type', config('backend.groupType.cabin'))
            ->where('language_id', $language->id)
            ->orderBy('ord')
            ->pluck('name', 'id');

        $param = [];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }
        if ($request->group_id) {
            $param['group_id'] = $request->group_id;
        }
        if ($request->cruise_id) {
            $param['cruise_id'] = $request->cruise_id;
        }
        $list = AppCabinService::getPaging($param, $language->id);

        Logging::logInfo('Xem danh sách facility.');

        return view($this->baseView . __FUNCTION__, compact('title', 'listCruise', 'listCabinType', 'list'));
    }

    public function create(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = __('backend::cabin.page_create');

        $listCabinType = AppGroup::where('type', config('backend.groupType.cabin'))
            ->where('language_id', $language->id)
            ->orderBy('ord')
            ->get(['id', 'name', 'slug']);

        \SEO::setTitle($title);

        $listCruise = AppCruiseService::getAll($language->id)->pluck('name', 'id');
        $listAmenity = AppAmenity::where('language_id', $language->id)->orderBy('ord')->get();
        $listAudience = AppGroup::where('type', config('backend.groupType.suitableAudience'))
            ->where('language_id', $language->id)
            ->orderBy('ord')
            ->get();

        $facilityProfileConfig = FacilityProfileUtils::getJsConfig();

        return view($this->baseView . __FUNCTION__, compact('title', 'listCruise', 'listCabinType', 'listAmenity', 'listAudience', 'facilityProfileConfig'));
    }

    public function store(CabinRequest $request)
    {
        try {
            $language = LanguageUtils::getCurrentLanguage();
            $data = $request->only([
                'group_id', 'cruise_id', 'name', 'summary', 'content', 'image_link', 'image_gallery', 'view', 'cabin_class',
                'capacity',
                'over_capacity_adult', 'over_capacity_child_6_12', 'over_capacity_child_2_5', 'over_capacity_infant',
                'area', 'discount_percent', 'ord',
                'room_title', 'room_description',
                'amenity_ids', 'amenity_name', 'amenity_description', 'amenity_icon',
                'price',
                'audience_group_ids',
            ]);

            $id = AppCabinService::create($data, $language->id);

            $lastUrl = $request->get('lastUrl');
            $languageCode = $request->route('languageCode');
            $routeParams = ['lastUrl' => $lastUrl];
            if ($languageCode) {
                $routeParams['languageCode'] = $languageCode;
            }
            $route = route(Utilities::getRouteName('backend.cabin.index'), $routeParams);

            Logging::logInfo('Thêm cabin thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', __('backend::cabin.msg_create_success'));
        } catch (\Exception $e) {
            Logging::logError('Thêm cabin lỗi.', 'Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors(__('backend::cabin.msg_create_error'));
        }
    }
    
    public function show($id, Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = __('backend::cabin.page_show');

        \SEO::setTitle($title);

        // Đảm bảo lấy đúng id khi có languageCode trên route
        $routeId = $request->route('id');
        if ($routeId !== null) {
            $id = $routeId;
        }

        $obj = AppCabinService::findJoin($id, $language->id);
        if (!$obj) {
            return abort(404);
        }

        $listCabinType = AppGroup::where('type', config('backend.groupType.cabin'))
            ->where('language_id', $language->id)
            ->orderBy('ord')
            ->pluck('name', 'id');

        $listCruise = AppCruiseService::getAll($language->id)->pluck('name', 'id');
        $listAmenity = AppAmenity::where('language_id', $language->id)->orderBy('ord')->get();
        $selectedAmenityIds = AppCabinAmenity::where('cabin_id', $obj->id)->pluck('amenity_id')->toArray();
        $rooms = AppCabinRoom::where('cabin_id', $obj->id)->orderBy('ord')->get();
        $prices = AppCabinPrice::where('cabin_id', $obj->id)->get();
        $listAudience = AppGroup::where('type', config('backend.groupType.suitableAudience'))
            ->where('language_id', $language->id)
            ->orderBy('ord')
            ->get();
        
        $audiences = [];
        $suitableAudiences = AppCabinSuitableAudience::where('cabin_id', $obj->id)->orderBy('ord')->get();
        foreach ($suitableAudiences as $sa) {
            $group = AppGroup::find($sa->group_id);
            if ($group) {
                $icon = null;
                if ($group->description) {
                    $descData = json_decode($group->description, true);
                    if (isset($descData['icon'])) {
                        $icon = $descData['icon'];
                    }
                }
                $audiences[] = (object)[
                    'id' => $group->id,
                    'name' => $group->name,
                    'icon' => $icon
                ];
            }
        }

        $cabinGallery = AppCabinService::getCabinGallery($obj->id);

        Logging::logInfo('Xem chi tiết cabin.', 'id = ' . $id);

        return view($this->baseView . __FUNCTION__, compact('title', 'obj', 'listCruise', 'listCabinType', 'listAmenity', 'selectedAmenityIds', 'rooms', 'prices', 'audiences', 'listAudience', 'cabinGallery'));
    }

    public function edit($id, Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = __('backend::cabin.page_edit');

        \SEO::setTitle($title);

        $routeId = $request->route('id');
        if ($routeId !== null) {
            $id = $routeId;
        }

        $obj = AppCabinService::find($id);
        if (!$obj) {
            return abort(404);
        }

        $listCabinType = AppGroup::where('type', config('backend.groupType.cabin'))
            ->where('language_id', $language->id)
            ->orderBy('ord')
            ->get(['id', 'name', 'slug']);

        $listCruise = AppCruiseService::getAll($language->id)->pluck('name', 'id');
        $listAmenity = AppAmenity::where('language_id', $language->id)->orderBy('ord')->get();
        $selectedAmenityIds = AppCabinAmenity::where('cabin_id', $obj->id)->pluck('amenity_id')->toArray();
        $rooms = AppCabinRoom::where('cabin_id', $obj->id)->orderBy('ord')->get();
        $prices = AppCabinPrice::where('cabin_id', $obj->id)->get();
        $listAudience = AppGroup::where('type', config('backend.groupType.suitableAudience'))
            ->where('language_id', $language->id)
            ->orderBy('ord')
            ->get();
        
        $audiences = [];
        $suitableAudiences = AppCabinSuitableAudience::where('cabin_id', $obj->id)->orderBy('ord')->get();
        foreach ($suitableAudiences as $sa) {
            $group = AppGroup::find($sa->group_id);
            if ($group) {
                $icon = null;
                if ($group->description) {
                    $descData = json_decode($group->description, true);
                    if (isset($descData['icon'])) {
                        $icon = $descData['icon'];
                    }
                }
                $audiences[] = (object)[
                    'id' => $group->id,
                    'name' => $group->name,
                    'icon' => $icon
                ];
            }
        }

        $cabinGallery = AppCabinService::getCabinGallery($obj->id);
        $facilityProfileConfig = FacilityProfileUtils::getJsConfig();

        return view($this->baseView . __FUNCTION__, compact('title', 'obj', 'listCruise', 'listCabinType', 'listAmenity', 'selectedAmenityIds', 'rooms', 'prices', 'audiences', 'listAudience', 'cabinGallery', 'facilityProfileConfig'));
    }

    public function update(CabinRequest $request, $id)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $routeId = $request->route('id');
        if ($routeId !== null) {
            $id = $routeId;
        }

        $obj = AppCabinService::find($id);
        if (!$obj) {
            return abort(404);
        }

            try {
            $data = $request->only([
                'group_id', 'cruise_id', 'name', 'summary', 'content', 'image_link', 'image_gallery', 'view', 'cabin_class',
                'capacity',
                'over_capacity_adult', 'over_capacity_child_6_12', 'over_capacity_child_2_5', 'over_capacity_infant',
                'area', 'discount_percent', 'ord',
                'room_title', 'room_description',
                'amenity_ids', 'amenity_name', 'amenity_description', 'amenity_icon',
                'price',
                'audience_group_ids',
            ]);
            $data['id'] = $id;

            AppCabinService::update($data, $language->id);

            $lastUrl = $request->get('lastUrl');
            $languageCode = $request->route('languageCode');
            $routeParams = ['lastUrl' => $lastUrl];
            if ($languageCode) {
                $routeParams['languageCode'] = $languageCode;
            }
            $route = route(Utilities::getRouteName('backend.cabin.index'), $routeParams);

            Logging::logInfo('Sửa cabin thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', __('backend::cabin.msg_update_success'));
        } catch (\Exception $e) {
            Logging::logError('Sửa cabin lỗi.', 'id = ' . $id . '. Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors(__('backend::cabin.msg_update_error'));
        }
    }

    public function destroy(Request $request)
    {
        $data = $request->only('id');
        try
        {
            if (empty($data['id'])) {
                throw new \Exception('Parameter invalid.');
            }

            $language = LanguageUtils::getCurrentLanguage();

            AppCabinService::delete($data['id'], $language->id);
            
            Session::flash('flash-message', __('backend::cabin.msg_delete_success'));
            Logging::logInfo('Xóa cabin thành công.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE));
            
            return response()->json(['msg' => 'success']);
        }
        catch (\Exception $e)
        {
            Session::flash('errors', new MessageBag([__('backend::cabin.msg_delete_error')]));
            Logging::logError('Xóa cabin lỗi.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE) . '. Exception = ' . $e->getMessage());
            
            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }
}
