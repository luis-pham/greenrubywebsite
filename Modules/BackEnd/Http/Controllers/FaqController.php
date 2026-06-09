<?php

namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Http\Requests\FaqRequest;
use Modules\BackEnd\Services\AppFaqService;
use Modules\BackEnd\Services\AppGroupService;

class FaqController extends Controller
{
    private $baseView = 'backend::faq.';

    public function index(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();

        $title = 'Danh sách câu hỏi thường gặp';

        \SEO::setTitle($title);

        $listGroup = AppGroupService::getAll(config('backend.groupType.faq'), $language->id)->pluck('name', 'id');

        $param = [
            'is_disabled_paginate' => true
        ];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }
        if ($request->group_id) {
            $param['group_id'] = $request->group_id;
        }
        $list = AppFaqService::getPaging($param, $language->id);

        Logging::LogInfo('Xem danh sách câu hỏi thường gặp.');

        return view($this->baseView . __FUNCTION__, compact('title', 'listGroup', 'list'));
    }

    public function create()
    {
        $language = LanguageUtils::getCurrentLanguage();

        $title = 'Thêm câu hỏi thường gặp';

        \SEO::setTitle($title);

        $listGroup = AppGroupService::getAll(config('backend.groupType.faq'), $language->id)->pluck('name', 'id');

        return view($this->baseView . __FUNCTION__, compact('title', 'listGroup'));
    }

    public function store(FaqRequest $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        try {
            $data = $request->only('group_id', 'question', 'answer');
            $data['ord'] = AppFaqService::getNextOrder($language->id);
            $id = AppFaqService::create($data, $language->id);

            $lastUrl = $request->get('lastUrl');
            $route = route(Utilities::getRouteName('backend.faq.show'), ['languageCode' => $languageCode, 'id' => $id, 'lastUrl' => $lastUrl]);

            Logging::LogInfo('Thêm câu hỏi thường gặp thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Thêm câu hỏi thường gặp thành công!');
        } catch (\Exception $e) {
            Logging::LogError('Thêm câu hỏi thường gặp lỗi.', 'Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Thêm câu hỏi thường gặp lỗi!');
        }
    }

    public function show(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();

        $title = 'Chi tiết câu hỏi thường gặp';

        \SEO::setTitle($title);

        $id = $request->route('id');
        $obj = AppFaqService::findJoin($id, $language->id);
        if (!$obj) {
            return abort(404);
        }

        Logging::LogInfo('Xem chi tiết câu hỏi thường gặp.', 'id = ' . $id);

        return view($this->baseView . __FUNCTION__, compact('title', 'obj'));
    }

    public function edit(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Sửa câu hỏi thường gặp';

        \SEO::setTitle($title);

        $id = $request->route('id');
        $obj = AppFaqService::find($id, $language->id);
        if (!$obj) {
            return abort(404);
        }

        $listGroup = AppGroupService::getAll(config('backend.groupType.faq'), $language->id)->pluck('name', 'id');

        return view($this->baseView . __FUNCTION__, compact('title', 'obj', 'listGroup'));
    }

    public function update(FaqRequest $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');
        $id = $request->route('id');
        $obj = AppFaqService::find($id, $language->id);
        if (!$obj) {
            return abort(404);
        }

        try {
            $data = $request->only('group_id', 'question', 'answer');
            $data['id'] = $id;

            AppFaqService::update($data, $language->id);

            $lastUrl = $request->get('lastUrl');
            $route = route(Utilities::getRouteName('backend.faq.show'), ['languageCode' => $languageCode, 'id' => $id, 'lastUrl' => $lastUrl]);

            Logging::LogInfo('Sửa câu hỏi thường gặp thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Sửa câu hỏi thường gặp thành công!');
        } catch (\Exception $e) {
            Logging::LogError('Sửa câu hỏi thường gặp lỗi.', 'id = ' . $id . '. Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Sửa câu hỏi thường gặp lỗi!');
        }
    }

    public function destroy(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        $data = $request->only('id');
        try {
            AppFaqService::delete($data['id'], $language->id);

            Session::flash('flash-message', 'Xóa câu hỏi thường gặp thành công!');
            Logging::LogInfo('Xóa câu hỏi thường gặp thành công.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE));

            return response()->json(['msg' => 'success']);
        } catch (\Exception $e) {
            Session::flash('errors', new MessageBag(['Xóa câu hỏi thường gặp lỗi!']));
            Logging::LogError('Xóa câu hỏi thường gặp lỗi.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE) . '. Exception = ' . $e->getMessage());

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
        try
        {
            AppFaqService::saveOrder($data['id'], $language->id);

            Logging::LogInfo('Sắp xếp câu hỏi thường gặp thành công.');

            return response()->json(['msg' => 'success']);
        } catch (\Exception $e) {
            Logging::LogError('Sắp xếp câu hỏi thường gặp lỗi.', 'Exception = ' . $e->getMessage());

            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }
}
