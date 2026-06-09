<?php

namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\MessageBag;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Http\Requests\GroupRequest;
use Modules\BackEnd\Services\AppGroupService;

class GroupController extends Controller
{
    private $baseView = 'backend::group.';

    public function index(Request $request)
    {
        $typeName = $request->route('typeName');
        $type = $this->getTypeByName($typeName);
        if (!$type) {
            return abort(404);
        }
        $language = LanguageUtils::getCurrentLanguage();

        $groupName = $this->getGroupNameByTypeName($type);
        $title = 'Danh sách ' . $groupName;

        \SEO::setTitle($title);

        $param = [
            'is_disabled_paginate' => true
        ];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }
        $list = AppGroupService::getPaging($param, $type, $language->id);

        Logging::logInfo('Xem danh sách ' . $groupName . '.');

        return view($this->baseView . __FUNCTION__, compact('title', 'list', 'type'));
    }

    public function create(Request $request)
    {
        $typeName = $request->route('typeName');
        $type = $this->getTypeByName($typeName);
        if (!$type) {
            return abort(404);
        }
        $language = LanguageUtils::getCurrentLanguage();

        $groupName = $this->getGroupNameByTypeName($type);
        $title = 'Thêm ' . $groupName;

        \SEO::setTitle($title);

        $listTab = Utilities::getListTabByType($type, $language->code);

        return view($this->baseView . __FUNCTION__, compact('title', 'type', 'listTab'));
    }

    public function store(GroupRequest $request)
    {
        $typeName = $request->route('typeName');
        $type = $this->getTypeByName($typeName);
        if (!$type) {
            return abort(404);
        }
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $groupName = $this->getGroupNameByTypeName($type);
        
        try {
            $data = $request->all();
            $data['ord'] = AppGroupService::getNextOrder($type, $language->id);
            $id = AppGroupService::create($data, $type, $language->id);

            $lastUrl = $request->get('lastUrl');
            $route = route(Utilities::getRouteName('backend.group.show'), ['languageCode' => $languageCode, 'typeName' => $typeName, 'id' => $id, 'lastUrl' => $lastUrl]);

            Logging::logInfo('Thêm ' . $groupName . ' thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Thêm ' . $groupName . ' thành công!');
        } catch (\Exception $e) {
            Logging::logError('Thêm ' . $groupName . ' lỗi.', 'Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Thêm ' . $groupName . ' lỗi!');
        }
    }

    public function show(Request $request)
    {
        $typeName = $request->route('typeName');
        $type = $this->getTypeByName($typeName);
        if (!$type) {
            return abort(404);
        }
        $language = LanguageUtils::getCurrentLanguage();

        $groupName = $this->getGroupNameByTypeName($type);
        $title = 'Chi tiết ' . $groupName;

        \SEO::setTitle($title);

        $id = $request->route('id');
        $obj = AppGroupService::find($id, $type, $language->id);
        if (!$obj) {
            return abort(404);
        }

        Logging::logInfo('Xem chi tiết ' . $groupName . '.', 'id = ' . $id);

        return view($this->baseView . __FUNCTION__, compact('title', 'obj'));
    }

    public function edit(Request $request)
    {
        $typeName = $request->route('typeName');
        $type = $this->getTypeByName($typeName);
        if (!$type) {
            return abort(404);
        }
        $language = LanguageUtils::getCurrentLanguage();

        $groupName = $this->getGroupNameByTypeName($type);
        $title = 'Sửa ' . $groupName;

        \SEO::setTitle($title);

        $id = $request->route('id');
        $obj = AppGroupService::find($id, $type, $language->id);
        if (!$obj) {
            return abort(404);
        }

        $listTab = Utilities::getListTabByType($type, $language->code);

        return view($this->baseView . __FUNCTION__, compact('title', 'obj', 'type', 'listTab'));
    }

    public function update(GroupRequest $request)
    {
        $typeName = $request->route('typeName');
        $type = $this->getTypeByName($typeName);
        if (!$type) {
            return abort(404);
        }
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $id = $request->route('id');
        $obj = AppGroupService::find($id, $type, $language->id);
        if (!$obj) {
            return abort(404);
        }
        
        $groupName = $this->getGroupNameByTypeName($type);

        try {
            $data = $request->all();
            $data['id'] = $id;
            AppGroupService::update($data, $type, $language->id);

            $lastUrl = $request->get('lastUrl');
            $route = route(Utilities::getRouteName('backend.group.show'), ['languageCode' => $languageCode, 'typeName' => $typeName, 'id' => $id, 'lastUrl' => $lastUrl]);

            Logging::logInfo('Sửa ' . $groupName . ' thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Sửa ' . $groupName . ' thành công!');
        } catch (\Exception $e) {
            Logging::logError('Sửa ' . $groupName . ' lỗi.', 'id = ' . $id . '. Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Sửa ' . $groupName . ' lỗi!');
        }
    }

    public function destroy(Request $request)
    {
        $typeName = $request->route('typeName');
        $type = $this->getTypeByName($typeName);
        if (!$type) {
            return abort(404);
        }
        $language = LanguageUtils::getCurrentLanguage();

        $groupName = $this->getGroupNameByTypeName($type);

        $data = $request->all();
        try {
            AppGroupService::delete($data['id'], $type, $language->id);

            Session::flash('flash-message', 'Xóa ' . $groupName . ' thành công!');
            Logging::logInfo('Xóa ' . $groupName . ' thành công.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE));

            return response()->json(['msg' => 'success']);
        } catch (\Exception $e) {
            Session::flash('errors', new MessageBag(['Xóa ' . $groupName . ' lỗi!']));
            Logging::logError('Xóa ' . $groupName . ' lỗi.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE) . '. Exception = ' . $e->getMessage());

            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }

    public function orderUpdate(Request $request)
    {
        $typeName = $request->route('typeName');
        $type = $this->getTypeByName($typeName);
        if (!$type) {
            return abort(404);
        }
        $language = LanguageUtils::getCurrentLanguage();

        $groupName = $this->getGroupNameByTypeName($type);

        $data = $request->all();
        try
        {
            AppGroupService::saveOrder($data['id'], $type, $language->id);

            Logging::LogInfo('Sắp xếp ' . $groupName . ' thành công.');

            return response()->json(['msg' => 'success']);
        } catch (\Exception $e) {
            Logging::LogError('Sắp xếp ' . $groupName . ' lỗi.', 'Exception = ' . $e->getMessage());

            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }

    private function getGroupNameByTypeName($type)
    {
        switch ($type) {
            case config('backend.groupType.faq'):
                return 'chuyên mục câu hỏi thường gặp';
            case config('backend.groupType.cabin'):
                return 'loại cabin';
            case config('backend.groupType.expActivity'):
                return 'loại hoạt động trải nghiệm';
            case config('backend.groupType.service'):
                return 'loại dịch vụ';
            case config('backend.groupType.suitableAudience'):
                return 'đối tượng phù hợp';
            default:
                return '';
        }
    }

    private function getTypeByName($name)
    {
        $key = Str::camel($name);
        $list = config('backend.groupType');
        return array_key_exists($key, config('backend.groupType')) ? $list[$key] : null;
    }
}
