<?php

namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Http\Requests\ArticleRequest;
use Modules\BackEnd\Services\AppCategoryService;
use Modules\BackEnd\Services\AppArticleService;

class ArticleController extends Controller
{
    private $baseView = 'backend::article.';

    public function index(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Danh sách tin tức';

        \SEO::setTitle($title);

        $listCategory = AppCategoryService::getAll(config('backend.categoryType.article'), $language->id);
        for ($i = 0; $i < count($listCategory); $i++) {
            $listCategory[$i]->name = Utilities::getCategoryNameByLevel($listCategory[$i]->name, $listCategory[$i]->lvl);
        }
        $listCategory = $listCategory->pluck('name', 'id');

        $param = [];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }
        if ($request->category_id) {
            $param['category_id'] = $request->category_id;
        }
        if ($request->is_featured || (string) $request->is_featured === '0') {
            $param['is_featured'] = $request->is_featured;
        }
        if ($request->is_published || (string) $request->is_published === '0') {
            $param['is_published'] = $request->is_published;
        }
        $list = AppArticleService::getPaging($param, $language->id);

        Logging::logInfo('Xem danh sách tin tức.');

        return view($this->baseView . __FUNCTION__, compact('title', 'listCategory', 'list'));
    }

    public function create()
    {
        $language = LanguageUtils::getCurrentLanguage();

        $title = 'Thêm tin tức';

        \SEO::setTitle($title);

        $listCategory = AppCategoryService::getAll(config('backend.categoryType.article'), $language->id);
        for ($i = 0; $i < count($listCategory); $i++) {
            $listCategory[$i]->name = Utilities::getCategoryNameByLevel($listCategory[$i]->name, $listCategory[$i]->lvl);
        }
        $listCategory = $listCategory->pluck('name', 'id');

        return view($this->baseView . __FUNCTION__, compact('title', 'listCategory'));
    }

    public function store(ArticleRequest $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        try {
            $data = $request->all();
            $data['publish_date'] = Utilities::parseDateTime($request->publish_date);
            $data['is_featured'] = array_key_exists('is_featured', $data);
            $data['is_published'] = array_key_exists('is_published', $data);

            $id = AppArticleService::create($data, $language->id);

            $lastUrl = $request->get('lastUrl');
            $route = route(Utilities::getRouteName('backend.article.show'), ['languageCode' => $languageCode, 'id' => $id, 'lastUrl' => $lastUrl]);

            Logging::logInfo('Thêm tin tức thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Thêm tin tức thành công!');
        } catch (\Exception $e) {
            Logging::logError('Thêm tin tức lỗi.', 'Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Thêm tin tức lỗi!');
        }
    }

    public function show(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();

        $title = 'Chi tiết tin tức';

        \SEO::setTitle($title);

        $id = $request->route('id');
        $obj = AppArticleService::findJoin($id, $language->id);
        if (!$obj) {
            return abort(404);
        }

        Logging::logInfo('Xem chi tiết tin tức.', 'id = ' . $id);

        return view($this->baseView . __FUNCTION__, compact('title', 'obj'));
    }

    public function edit(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();

        $title = 'Sửa tin tức';

        \SEO::setTitle($title);

        $id = $request->route('id');
        $obj = AppArticleService::find($id, $language->id);
        if (!$obj) {
            return abort(404);
        }

        $listCategory = AppCategoryService::getAll(config('backend.categoryType.article'), $language->id);
        for ($i = 0; $i < count($listCategory); $i++) {
            $listCategory[$i]->name = Utilities::getCategoryNameByLevel($listCategory[$i]->name, $listCategory[$i]->lvl);
        }
        $listCategory = $listCategory->pluck('name', 'id');

        return view($this->baseView . __FUNCTION__, compact('title', 'obj', 'listCategory'));
    }

    public function update(ArticleRequest $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');
        $id = $request->route('id');
        $obj = AppArticleService::find($id, $language->id);
        if (!$obj) {
            return abort(404);
        }

        try {
            $data = $request->all();
            $data['id'] = $id;
            $data['publish_date'] = Utilities::parseDateTime($data['publish_date']);
            $data['is_featured'] = array_key_exists('is_featured', $data);
            $data['is_published'] = array_key_exists('is_published', $data);

            AppArticleService::update($data, $language->id);

            $lastUrl = $request->get('lastUrl');
            $route = route(Utilities::getRouteName('backend.article.show'), ['languageCode' => $languageCode, 'id' => $id, 'lastUrl' => $lastUrl]);

            Logging::logInfo('Sửa tin tức thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Sửa tin tức thành công!');
        } catch (\Exception $e) {
            Logging::logError('Sửa tin tức lỗi.', 'id = ' . $id . '. Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Sửa tin tức lỗi!');
        }
    }

    public function destroy(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        $data = $request->all();
        try {
            AppArticleService::delete($data['id'], $language->id);

            Session::flash('flash-message', 'Xóa tin tức thành công!');
            Logging::logInfo('Xóa tin tức thành công.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE));

            return response()->json(['msg' => 'success']);
        } catch (\Exception $e) {
            Session::flash('errors', new MessageBag(['Xóa tin tức lỗi!']));
            Logging::logError('Xóa tin tức lỗi.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE) . '. Exception = ' . $e->getMessage());

            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }
}
