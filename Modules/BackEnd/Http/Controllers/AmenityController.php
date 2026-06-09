<?php
namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AppAmenityService;
use Modules\BackEnd\Http\Requests\AmenityRequest;

class AmenityController extends Controller
{
    private $baseView = 'backend::amenity.';
    
    public function index(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Quản lý tiện ích';
        \SEO::setTitle($title);

        $param = [];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }

        $list = AppAmenityService::getPaging($param, $language->id);

        Logging::logInfo('Xem danh sách tiện ích.');

        return view($this->baseView . __FUNCTION__, compact('title', 'list'));
    }

    public function create(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Thêm tiện ích';

        \SEO::setTitle($title);

    

        return view($this->baseView . __FUNCTION__, compact('title'));
    }

    public function store(AmenityRequest $request)
    {
        try {
            $language = LanguageUtils::getCurrentLanguage();
            $data = $request->only([
                'name',
                'description',
                'icon',
                'ord'
            ]);

            $id = AppAmenityService::create($data, $language->id);

            $lastUrl = $request->get('lastUrl');
            $languageCode = $request->route('languageCode');
            $routeParams = ['lastUrl' => $lastUrl];
            if ($languageCode) {
                $routeParams['languageCode'] = $languageCode;
            }
            $route = route(Utilities::getRouteName('backend.amenity.index'), $routeParams);

            Logging::logInfo('Thêm tiện ích thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Thêm tiện ích thành công!');
        } catch (\Exception $e) {
            Logging::logError('Thêm tiện ích lỗi.', 'Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Thêm tiện ích lỗi!');
        }
    }
    
    public function show($id, Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Chi tiết tiện ích';

        \SEO::setTitle($title);

        $routeId = $request->route('id');
        if ($routeId !== null) {
            $id = $routeId;
        }

        $obj = AppAmenityService::find($id);
        if (!$obj) {
            return abort(404);
        }

        Logging::logInfo('Xem chi tiết tiện ích.', 'id = ' . $id);

        return view($this->baseView . __FUNCTION__, compact('title', 'obj'));
    }

    public function edit($id, Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Sửa tiện ích';

        \SEO::setTitle($title);

        $routeId = $request->route('id');
        if ($routeId !== null) {
            $id = $routeId;
        }

        $obj = AppAmenityService::find($id);
        if (!$obj) {
            return abort(404);
        }        

        return view($this->baseView . __FUNCTION__, compact('title', 'obj'));
    }

    public function update(AmenityRequest $request, $id)
    {
        $language = LanguageUtils::getCurrentLanguage();

        $routeId = $request->route('id');
        if ($routeId !== null) {
            $id = $routeId;
        }

        $obj = AppAmenityService::find($id);
        if (!$obj) {
            return abort(404);
        }

        try {
            $data = $request->only([
                'name',
                'description',
                'icon',
                'ord'
            ]);
            $data['id'] = $id;

            AppAmenityService::update($data, $language->id);

            $lastUrl = $request->get('lastUrl');
            $languageCode = $request->route('languageCode');
            $routeParams = ['lastUrl' => $lastUrl];
            if ($languageCode) {
                $routeParams['languageCode'] = $languageCode;
            }
            $route = route(Utilities::getRouteName('backend.amenity.index'), $routeParams);

            Logging::logInfo('Sửa tiện ích thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Sửa tiện ích thành công!');
        } catch (\Exception $e) {
            Logging::logError('Sửa tiện ích lỗi.', 'id = ' . $id . '. Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Sửa tiện ích lỗi!');
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

            AppAmenityService::delete($data['id'], $language->id);
            
            Session::flash('flash-message', 'Xóa tiện ích thành công!');
            Logging::logInfo('Xóa tiện ích thành công.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE));
            
            return response()->json(['msg' => 'success']);
        }
        catch (\Exception $e)
        {
            Session::flash('errors', new MessageBag(['Xóa tiện ích lỗi!']));
            Logging::logError('Xóa tiện ích lỗi.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE) . '. Exception = ' . $e->getMessage());
            
            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }

    public function orderUpdate(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        $data = $request->only('id');
        try {
            AppAmenityService::saveOrder($data['id'], $language->id);

            Logging::logInfo('Sắp xếp tiện ích thành công.');

            return response()->json(['msg' => 'success']);
        } catch (\Exception $e) {
            Logging::logError('Sắp xếp tiện ích lỗi.', 'Exception = ' . $e->getMessage());

            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }
}