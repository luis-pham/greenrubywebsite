<?php
namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Modules\BackEnd\Http\Requests\ServiceRequest;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AppServiceService;
use Modules\BackEnd\Services\AppGroupService;

class ServiceController extends Controller
{
    private $baseView = 'backend::service.';
    
    public function index(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Quản lý dịch vụ';

        \SEO::setTitle($title);

        $listGroup = AppGroupService::getAll(config('backend.groupType.service'), $language->id)->pluck('name', 'id');
        $param = [];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }
        if ($request->group_id) {
            $param['group_id'] = $request->group_id;
        }
        $list = AppServiceService::getPaging($param, $language->id);

        Logging::logInfo('Xem danh sách dịch vụ.');

        return view($this->baseView . __FUNCTION__, compact('title', 'listGroup', 'list'));
    }

    public function create(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Thêm dịch vụ';

        \SEO::setTitle($title);

        $listGroup = AppGroupService::getAll(config('backend.groupType.service'), $language->id)->pluck('name', 'id');

        return view($this->baseView . __FUNCTION__, compact('title', 'listGroup'));
    }

    public function store(ServiceRequest $request)
    {
        try {
            $language = LanguageUtils::getCurrentLanguage();
            $data = $request->only([
                'name',
                'group_id',
                'description',
                'image_link',
                'price',
                'type',
                'status',
                'service_gallery'
            ]);

            $id = AppServiceService::create($data, $language->id);

            $lastUrl = $request->get('lastUrl');
            $languageCode = $request->route('languageCode');
            $routeParams = ['lastUrl' => $lastUrl];
            if ($languageCode) {
                $routeParams['languageCode'] = $languageCode;
            }
            $route = route(Utilities::getRouteName('backend.service.index'), $routeParams);

            Logging::logInfo('Thêm dịch vụ thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Thêm dịch vụ thành công!');
        } catch (\Exception $e) {
            Logging::logError('Thêm dịch vụ lỗi.', 'Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Thêm dịch vụ lỗi!');
        }
    }
    
    public function show($id, Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Chi tiết dịch vụ';

        \SEO::setTitle($title);

        $routeId = $request->route('id');
        if ($routeId !== null) {
            $id = $routeId;
        }

        $obj = AppServiceService::findJoin($id);
        if (!$obj) {
            return abort(404);
        }

        Logging::logInfo('Xem chi tiết dịch vụ.', 'id = ' . $id);

        $galleryImages = AppServiceService::getServiceGallery($id);

        return view($this->baseView . __FUNCTION__, compact('title', 'obj', 'galleryImages'));
    }

    public function edit($id, Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Sửa dịch vụ';

        \SEO::setTitle($title);

        $routeId = $request->route('id');
        if ($routeId !== null) {
            $id = $routeId;
        }

        $obj = AppServiceService::find($id);
        if (!$obj) {
            return abort(404);
        }

        $listGroup = AppGroupService::getAll(config('backend.groupType.service'), $language->id)->pluck('name', 'id');
        $galleryImages = AppServiceService::getServiceGallery($id);

        return view($this->baseView . __FUNCTION__, compact('title', 'obj', 'listGroup', 'galleryImages'));
    }

    public function update(ServiceRequest $request, $id)
    {
        $language = LanguageUtils::getCurrentLanguage();

        $routeId = $request->route('id');
        if ($routeId !== null) {
            $id = $routeId;
        }

        $obj = AppServiceService::find($id);
        if (!$obj) {
            return abort(404);
        }

        try {
            $data = $request->only([
                'name',
                'group_id',
                'description',
                'image_link',
                'price',
                'type',
                'status',
                'service_gallery'
            ]);
            $data['id'] = $id;

            AppServiceService::update($data, $language->id);

            $lastUrl = $request->get('lastUrl');
            $languageCode = $request->route('languageCode');
            $routeParams = ['lastUrl' => $lastUrl];
            if ($languageCode) {
                $routeParams['languageCode'] = $languageCode;
            }
            $route = route(Utilities::getRouteName('backend.service.index'), $routeParams);

            Logging::logInfo('Sửa dịch vụ thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Sửa dịch vụ thành công!');
        } catch (\Exception $e) {
            Logging::logError('Sửa hoạt động trải nghiệm lỗi.', 'id = ' . $id . '. Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Sửa dịch vụ lỗi!');
        }
    }

    public function destroy(Request $request)
    {
        $data = $request->all();
        try
        {
            if (!$data['id']) {
                throw new \Exception('Parameter invalid.');
            }

            $language = LanguageUtils::getCurrentLanguage();

            AppServiceService::delete($data['id'], $language->id);
            
            Session::flash('flash-message', 'Xóa dịch vụ thành công!');
            Logging::logInfo('Xóa dịch vụ thành công.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE));
            
            return response()->json(['msg' => 'success']);
        }
        catch (\Exception $e)
        {
            Session::flash('errors', new MessageBag(['Xóa dịch vụ lỗi!']));
            Logging::logError('Xóa dịch vụ lỗi.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE) . '. Exception = ' . $e->getMessage());
            
            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }

}