<?php

namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Http\Requests\CategoryRequest;
use Modules\BackEnd\Services\AppCategoryService;

class CategoryController extends Controller
{
    private $baseView = 'backend::category.';

    public function index(Request $request)
    {
        $typeName = $request->route('typeName');
        $type = $this->getTypeByName($typeName);
        if (!$type) {
            return abort(404);
        }
        $language = LanguageUtils::getCurrentLanguage();

        $categoryName = $this->getCategoryNameByTypeName($type);
        $title = 'Danh sách chuyên mục ' . $categoryName;

        \SEO::setTitle($title);

        $listCategory = AppCategoryService::getAll($type, $language->id);
        for ($i = 0; $i < count($listCategory); $i++) {
            $listCategory[$i]->name = Utilities::getCategoryNameByLevel($listCategory[$i]->name, $listCategory[$i]->lvl);
        }
        $listCategory = $listCategory->pluck('name', 'id');

        $param = [];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }
        if ($request->parent_id) {
            $param['parent_id'] = $request->parent_id;
        }
        $list = AppCategoryService::getPaging($param, $type, $language->id);

        Logging::logInfo('Xem danh sách chuyên mục ' . $categoryName . '.');

        return view($this->baseView . __FUNCTION__, compact('title', 'listCategory', 'list'));
    }

    public function create(Request $request)
    {
        $typeName = $request->route('typeName');
        $type = $this->getTypeByName($typeName);
        if (!$type) {
            return abort(404);
        }
        $language = LanguageUtils::getCurrentLanguage();

        $categoryName = $this->getCategoryNameByTypeName($type);
        $title = 'Thêm chuyên mục ' . $categoryName;

        \SEO::setTitle($title);

        $listParent = AppCategoryService::getAll($type, $language->id);
        for ($i = 0; $i < count($listParent); $i++) {
            $listParent[$i]->name = Utilities::getCategoryNameByLevel($listParent[$i]->name, $listParent[$i]->lvl);
        }
        $listParent = $listParent->pluck('name', 'id');

        return view($this->baseView . __FUNCTION__, compact('title', 'listParent'));
    }

    public function store(CategoryRequest $request)
    {
        $typeName = $request->route('typeName');
        $type = $this->getTypeByName($typeName);
        if (!$type) {
            return abort(404);
        }
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $categoryName = $this->getCategoryNameByTypeName($type);
        
        try {
            $data = $request->all();
            $id = AppCategoryService::create($data, $type, $language->id);

            $lastUrl = $request->get('lastUrl');
            $route = route(Utilities::getRouteName('backend.category.show'), ['languageCode' => $languageCode, 'typeName' => $typeName, 'id' => $id, 'lastUrl' => $lastUrl]);

            Logging::logInfo('Thêm chuyên mục ' . $categoryName . ' thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Thêm chuyên mục ' . $categoryName . ' thành công!');
        } catch (\Exception $e) {
            Logging::logError('Thêm chuyên mục ' . $categoryName . ' lỗi.', 'Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Thêm chuyên mục ' . $categoryName . ' lỗi!');
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

        $categoryName = $this->getCategoryNameByTypeName($type);
        $title = 'Chi tiết chuyên mục ' . $categoryName;

        \SEO::setTitle($title);

        $id = $request->route('id');
        $obj = AppCategoryService::findJoin($id, $type, $language->id);
        if (!$obj || $obj->slug == 'root') {
            return abort(404);
        }

        Logging::logInfo('Xem chi tiết chuyên mục ' . $categoryName . '.', 'id = ' . $id);

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

        $categoryName = $this->getCategoryNameByTypeName($type);
        $title = 'Sửa chuyên mục ' . $categoryName;

        \SEO::setTitle($title);

        $id = $request->route('id');
        $obj = AppCategoryService::find($id, $type, $language->id);
        if (!$obj || $obj->slug == 'root') {
            return abort(404);
        }

        $listParent = AppCategoryService::getAll($type, $language->id);
        for ($i = 0; $i < count($listParent); $i++) {
            $listParent[$i]->name = Utilities::getCategoryNameByLevel($listParent[$i]->name, $listParent[$i]->lvl);
        }
        $listParent = $listParent->pluck('name', 'id');

        return view($this->baseView . __FUNCTION__, compact('title', 'obj', 'listParent'));
    }

    public function update(CategoryRequest $request)
    {
        $typeName = $request->route('typeName');
        $type = $this->getTypeByName($typeName);
        if (!$type) {
            return abort(404);
        }
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $id = $request->route('id');
        $obj = AppCategoryService::find($id, $type, $language->id);
        if (!$obj || $obj->slug == 'root') {
            return abort(404);
        }
        
        $categoryName = $this->getCategoryNameByTypeName($type);

        try {
            $data = $request->all();
            $data['id'] = $id;
            AppCategoryService::update($data, $type, $language->id);

            $lastUrl = $request->get('lastUrl');
            $route = route(Utilities::getRouteName('backend.category.show'), ['languageCode' => $languageCode, 'typeName' => $typeName, 'id' => $id, 'lastUrl' => $lastUrl]);

            Logging::logInfo('Sửa chuyên mục ' . $categoryName . ' thành công.', 'id = ' . $id);

            return redirect($route)->with('flash-message', 'Sửa chuyên mục ' . $categoryName . ' thành công!');
        } catch (\Exception $e) {
            Logging::logError('Sửa chuyên mục ' . $categoryName . ' lỗi.', 'id = ' . $id . '. Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Sửa chuyên mục ' . $categoryName . ' lỗi!');
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
        
        $categoryName = $this->getCategoryNameByTypeName($type);

        $data = $request->all();
        try {
            AppCategoryService::delete($data['id'], $type, $language->id);

            Session::flash('flash-message', 'Xóa chuyên mục ' . $categoryName . ' thành công!');
            Logging::logInfo('Xóa chuyên mục ' . $categoryName . ' thành công.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE));

            return response()->json(['msg' => 'success']);
        } catch (\Exception $e) {
            Session::flash('errors', new MessageBag(['Xóa chuyên mục ' . $categoryName . ' lỗi!']));
            Logging::logError('Xóa chuyên mục ' . $categoryName . ' lỗi.', 'id = ' . json_encode($data['id'], JSON_UNESCAPED_UNICODE) . '. Exception = ' . $e->getMessage());

            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }

    public function moveUp(Request $request)
    {
        $typeName = $request->route('typeName');
        $type = $this->getTypeByName($typeName);
        if (!$type) {
            return abort(404);
        }
        $language = LanguageUtils::getCurrentLanguage();
        
        $categoryName = $this->getCategoryNameByTypeName($type);

        $data = $request->all();
        try {
            AppCategoryService::moveUp($data['id'], $type, $language->id);

            Session::flash('flash-message', 'Di chuyển chuyên mục ' . $categoryName . ' thành công!');
            Logging::logInfo('Di chuyển chuyên mục ' . $categoryName . ' thành công.', 'id = ' . $data['id']);

            return response()->json(['msg' => 'success']);
        } catch (\Exception $e) {
            Session::flash('errors', new MessageBag(['Di chuyển chuyên mục ' . $categoryName . ' lỗi!']));
            Logging::logError('Di chuyển chuyên mục ' . $categoryName . ' lỗi.', 'id = ' . $data['id'] . '. Exception = ' . $e->getMessage());

            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }

    public function moveDown(Request $request)
    {
        $typeName = $request->route('typeName');
        $type = $this->getTypeByName($typeName);
        if (!$type) {
            return abort(404);
        }
        $language = LanguageUtils::getCurrentLanguage();
        
        $categoryName = $this->getCategoryNameByTypeName($type);

        $data = $request->all();
        try {
            AppCategoryService::moveDown($data['id'], $type, $language->id);

            Session::flash('flash-message', 'Di chuyển chuyên mục ' . $categoryName . ' thành công!');
            Logging::logInfo('Di chuyển chuyên mục ' . $categoryName . ' thành công.', 'id = ' . $data['id']);

            return response()->json(['msg' => 'success']);
        } catch (\Exception $e) {
            Session::flash('errors', new MessageBag(['Di chuyển chuyên mục ' . $categoryName . ' lỗi!']));
            Logging::logError('Di chuyển chuyên mục ' . $categoryName . ' lỗi.', 'id = ' . $data['id'] . '. Exception = ' . $e->getMessage());

            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }

    private function getTypeByName($name)
    {
        $key = Str::camel($name);
        $list = config('backend.categoryType');
        return array_key_exists($key, config('backend.categoryType')) ? $list[$key] : null;
    }

    private function getCategoryNameByTypeName($type)
    {
        switch ($type) {
            case config('backend.categoryType.article'):
                return 'tin tức';
            default:
                return '';
        }
    }
}
