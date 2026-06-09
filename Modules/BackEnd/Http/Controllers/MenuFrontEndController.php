<?php

namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Http\Requests\MenuFrontEndStoreRequest;
use Modules\BackEnd\Http\Requests\MenuFrontEndUpdateRequest;
use Modules\BackEnd\Services\AppMenuFrontEndService;

class MenuFrontEndController extends Controller
{
    private $baseView = 'backend::menu-front-end.';

    public function index(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();

        $title = 'Danh sách menu (Front-End)';

        \SEO::setTitle($title);

        $param = [];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }
        $list = AppMenuFrontEndService::getPaging($param, $language->id);

        Logging::LogInfo('Xem danh sách menu (Front-End).');

        return view($this->baseView . __FUNCTION__, compact('title', 'list'));
    }

    public function create()
    {
        $language = LanguageUtils::getCurrentLanguage();

        $title = 'Thêm menu (Front-End)';

        \SEO::setTitle($title);

        return view($this->baseView . __FUNCTION__, compact('title'));
    }

    public function store(MenuFrontEndStoreRequest $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        try {
            $data = $request->all();
            $id = AppMenuFrontEndService::create($data, $language->id);

            $lastUrl = $request->get('lastUrl');
            $route = route(Utilities::getRouteName('backend.menuFrontEnd.show'), ['languageCode' => $languageCode, 'id' => $id, 'lastUrl' => $lastUrl]);

            Logging::LogInfo('Thêm menu (Front-End) thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Thêm menu (Front-End) thành công!');
        } catch (\Exception $e) {
            Logging::LogError('Thêm menu (Front-End) lỗi.', 'Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Thêm menu (Front-End) lỗi!');
        }
    }

    public function show(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();

        $title = 'Chi tiết menu (Front-End)';

        \SEO::setTitle($title);

        $id = $request->route('id');
        $obj = AppMenuFrontEndService::find($id, $language->id);
        if (!$obj) {
            return abort(404);
        }

        Logging::LogInfo('Xem chi tiết menu (Front-End).', 'id = ' . $id);

        return view($this->baseView . __FUNCTION__, compact('title', 'obj'));
    }

    public function edit(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();

        $title = 'Sửa menu (Front-End)';

        \SEO::setTitle($title);

        $id = $request->route('id');
        $obj = AppMenuFrontEndService::find($id, $language->id);
        if (!$obj) {
            return abort(404);
        }

        return view($this->baseView . __FUNCTION__, compact('title', 'obj'));
    }

    public function update(MenuFrontEndUpdateRequest $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $id = $request->route('id');
        $obj = AppMenuFrontEndService::find($id, $language->id);
        if (!$obj) {
            return abort(404);
        }

        try {
            $data = $request->all();
            $data['id'] = $id;

            AppMenuFrontEndService::update($data, $language->id);

            $lastUrl = $request->get('lastUrl');
            $route = route(Utilities::getRouteName('backend.menuFrontEnd.show'), ['languageCode' => $languageCode, 'id' => $id, 'lastUrl' => $lastUrl]);

            Logging::LogInfo('Sửa menu (Front-End) thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Sửa menu (Front-End) thành công!');
        } catch (\Exception $e) {
            Logging::LogError('Sửa menu (Front-End) lỗi.', 'id = ' . $id . '. Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Sửa menu (Front-End) lỗi!');
        }
    }

    public function destroy(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();

        $data = $request->all();
        try {
            AppMenuFrontEndService::delete($data['id'], $language->id);

            Session::flash('flash-message', 'Xóa menu (Front-End) thành công!');
            Logging::LogInfo('Xóa menu (Front-End) thành công.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE));

            return response()->json(['msg' => 'success']);
        } catch (\Exception $e) {
            Session::flash('errors', new MessageBag(['Xóa menu (Front-End) lỗi!']));
            Logging::LogError('Xóa menu (Front-End) lỗi.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE) . '. Exception = ' . $e->getMessage());

            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }
}
