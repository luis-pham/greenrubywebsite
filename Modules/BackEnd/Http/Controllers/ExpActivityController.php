<?php
namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Modules\BackEnd\Http\Requests\ExpActivityRequest;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AppExpActivityService;
use Modules\BackEnd\Services\AppGroupService;
use Modules\BackEnd\Entities\AppGroup;
class ExpActivityController extends Controller
{
    private $baseView = 'backend::exp-activity.';
    
    public function index(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Quản lý hoạt động trải nghiệm';

        \SEO::setTitle($title);

        $listGroup = AppGroupService::getAll(config('backend.groupType.expActivity'), $language->id)->pluck('name', 'id');
        $param = [];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }
        if ($request->group_id) {
            $param['group_id'] = $request->group_id;
        }
        $list = AppExpActivityService::getPaging($param, $language->id);

        Logging::logInfo('Xem danh sách hoạt động trải nghiệm.');

        return view($this->baseView . __FUNCTION__, compact('title', 'listGroup', 'list'));
    }

    public function create(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Thêm hoạt động trải nghiệm';

        \SEO::setTitle($title);

        $listGroup = AppGroupService::getAll(config('backend.groupType.expActivity'), $language->id)->pluck('name', 'id');
        
        $listSuitableAudience = AppGroup::where('type', config('backend.groupType.suitableAudience'))
            ->where('language_id', $language->id)
            ->orderBy('ord')
            ->get();

        return view($this->baseView . __FUNCTION__, compact('title', 'listGroup', 'listSuitableAudience'));
    }

    public function store(ExpActivityRequest $request)
    {
        try {
            $language = LanguageUtils::getCurrentLanguage();
            $data = $request->only([
                'name',
                'summary',
                'content',
                'image_link',
                'cover_link',
                'group_id',
                'duration',
                'start_time',
                'end_time',
                'note',
                'is_featured',
                'audience_group_ids',
                'activity_gallery'
            ]);

            $id = AppExpActivityService::create($data, $language->id);

            $lastUrl = $request->get('lastUrl');
            $languageCode = $request->route('languageCode');
            $routeParams = ['lastUrl' => $lastUrl];
            if ($languageCode) {
                $routeParams['languageCode'] = $languageCode;
            }
            $route = route(Utilities::getRouteName('backend.exp-activity.index'), $routeParams);

            Logging::logInfo('Thêm hoạt động trải nghiệm thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Thêm hoạt động trải nghiệm thành công!');
        } catch (\Exception $e) {
            Logging::logError('Thêm hoạt động trải nghiệm lỗi.', 'Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Thêm hoạt động trải nghiệm lỗi!');
        }
    }
    
    public function show($id, Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Chi tiết hoạt động trải nghiệm';

        \SEO::setTitle($title);

        $routeId = $request->route('id');
        if ($routeId !== null) {
            $id = $routeId;
        }

        $obj = AppExpActivityService::findJoin($id);
        if (!$obj) {
            return abort(404);
        }
        $suitableAudiences = AppExpActivityService::getSuitableAudiences($id, $language->id);

        Logging::logInfo('Xem chi tiết hoạt động trải nghiệm.', 'id = ' . $id);

        $galleryImages = AppExpActivityService::getActivityGallery($id);

        return view($this->baseView . __FUNCTION__, compact('title', 'obj', 'suitableAudiences', 'galleryImages'));
    }

    public function edit($id, Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Sửa hoạt động trải nghiệm';

        \SEO::setTitle($title);

        $routeId = $request->route('id');
        if ($routeId !== null) {
            $id = $routeId;
        }

        $obj = AppExpActivityService::find($id);
        if (!$obj) {
            return abort(404);
        }

        $listGroup = AppGroupService::getAll(config('backend.groupType.expActivity'), $language->id)->pluck('name', 'id');
        $suitableAudiences = AppExpActivityService::getSuitableAudiences($id, $language->id);
        $listSuitableAudience = AppGroup::where('type', config('backend.groupType.suitableAudience'))
            ->where('language_id', $language->id)
            ->orderBy('ord')
            ->get();
        
        $galleryImages = AppExpActivityService::getActivityGallery($id);

        return view($this->baseView . __FUNCTION__, compact('title', 'obj', 'listGroup', 'suitableAudiences', 'listSuitableAudience', 'galleryImages'));
    }

    public function update(ExpActivityRequest $request, $id)
    {
        $language = LanguageUtils::getCurrentLanguage();

        $routeId = $request->route('id');
        if ($routeId !== null) {
            $id = $routeId;
        }

        $obj = AppExpActivityService::find($id);
        if (!$obj) {
            return abort(404);
        }

        try {
            $data = $request->only([
                'name',
                'summary',
                'content',
                'image_link',
                'cover_link',
                'group_id',
                'duration',
                'start_time',
                'end_time',
                'note',
                'is_featured',
                'audience_group_ids',
                'activity_gallery'
            ]);
            $data['id'] = $id;

            AppExpActivityService::update($data, $language->id);

            $lastUrl = $request->get('lastUrl');
            $languageCode = $request->route('languageCode');
            $routeParams = ['lastUrl' => $lastUrl];
            if ($languageCode) {
                $routeParams['languageCode'] = $languageCode;
            }
            $route = route(Utilities::getRouteName('backend.exp-activity.index'), $routeParams);

            Logging::logInfo('Sửa hoạt động trải nghiệm thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Sửa hoạt động trải nghiệm thành công!');
        } catch (\Exception $e) {
            Logging::logError('Sửa hoạt động trải nghiệm lỗi.', 'id = ' . $id . '. Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Sửa hoạt động trải nghiệm lỗi!');
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

            AppExpActivityService::delete($data['id'], $language->id);
            
            Session::flash('flash-message', 'Xóa hoạt động trải nghiệm thành công!');
            Logging::logInfo('Xóa hoạt động trải nghiệm thành công.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE));
            
            return response()->json(['msg' => 'success']);
        }
        catch (\Exception $e)
        {
            Session::flash('errors', new MessageBag(['Xóa hoạt động trải nghiệm lỗi!']));
            Logging::logError('Xóa hoạt động trải nghiệm lỗi.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE) . '. Exception = ' . $e->getMessage());
            
            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }

}