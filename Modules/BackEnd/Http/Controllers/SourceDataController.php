<?php
namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackEnd\Helpers\CruiseUtils;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Helpers\SourceDataUtils;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AppCategoryService;
use Modules\BackEnd\Services\AppCruiseService;
use Modules\BackEnd\Services\AppGroupService;
use Modules\BackEnd\Services\SourceDataService;

class SourceDataController extends Controller
{
    private $baseView = 'backend::source-data.';

    public function articleIndex(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Danh sách tin tức';

        \SEO::setTitle($title);

        $listCategory = AppCategoryService::getAll(config('backend.categoryType.article'), $language->id);
        for ($i = 0; $i < count($listCategory); $i++) {
            $listCategory[$i]->name = Utilities::getCategoryNameByLevel($listCategory[$i]->name, $listCategory[$i]->lvl);
        }
        $listCategory = $listCategory->pluck('name', 'id');

        $param = [
            'is_published' => true
        ];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }
        if ($request->category_id) {
            $param['category_id'] = $request->category_id;
        }
        if ($request->is_featured || (string) $request->is_featured === '0') {
            $param['is_featured'] = $request->is_featured;
        }
        if ($request->exclude_id) {
            $param['exclude_id'] = json_decode($request->exclude_id);
        }
        $list = SourceDataService::getArticlePaging($param, $language->id);

        $currentPage = (int) $request->get('page', 1);
        if ($currentPage > 1 && $currentPage > $list->lastPage()) {
            $query = $request->query();
            $query['page'] = $list->lastPage();
            return redirect()->route($request->route()->getName(), $request->route()->parameters() + $query);
        }

        return view($this->baseView . 'article', compact('title', 'listCategory', 'list'));
    }

    public function articleGetById(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');
        
        $data = $request->all();
        try {
            $list = [];
            $data = SourceDataService::getArticleById($data['id'], $language->id);
            for ($i = 0; $i < count($data); $i++) {
                $obj = SourceDataUtils::bindSourceDataArticleDetail($data[$i], $languageCode);
                $list[$obj['id']] = $obj;
            }

            return response()->json([
                'msg' => 'success',
                'data' => $list
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }

    public function faqIndex(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Danh sách câu hỏi thường gặp';

        \SEO::setTitle($title);

        $listGroup = AppGroupService::getAll(config('backend.groupType.faq'), $language->id)->pluck('name', 'id');

        $param = [];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }
        if ($request->group_id) {
            $param['group_id'] = $request->group_id;
        }
        if ($request->exclude_id) {
            $param['exclude_id'] = json_decode($request->exclude_id);
        }
        $list = SourceDataService::getFaqPaging($param, $language->id);

        $currentPage = (int) $request->get('page', 1);
        if ($currentPage > 1 && $currentPage > $list->lastPage()) {
            $query = $request->query();
            $query['page'] = $list->lastPage();
            return redirect()->route($request->route()->getName(), $request->route()->parameters() + $query);
        }

        return view($this->baseView . 'faq', compact('title', 'listGroup', 'list'));
    }

    public function faqGetById(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');
        
        $data = $request->all();
        try {
            $list = [];
            $data = SourceDataService::getFaqById($data['id'], $language->id);
            for ($i = 0; $i < count($data); $i++) {
                $obj = SourceDataUtils::bindSourceDataFaqDetail($data[$i], $languageCode);
                $list[$obj['id']] = $obj;
            }

            return response()->json([
                'msg' => 'success',
                'data' => $list
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }

    public function cruiseItineraryIndex(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Danh sách hành trình';

        \SEO::setTitle($title);

        $listDuration = CruiseUtils::getListDuration();

        $param = [
            'is_published' => true
        ];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }
        if ($request->duration) {
            $param['duration'] = $request->duration;
        }
        if ($request->exclude_id) {
            $param['exclude_id'] = json_decode($request->exclude_id);
        }
        $list = SourceDataService::getCruiseItineraryPaging($param, $language->id);

        $currentPage = (int) $request->get('page', 1);
        if ($currentPage > 1 && $currentPage > $list->lastPage()) {
            $query = $request->query();
            $query['page'] = $list->lastPage();
            return redirect()->route($request->route()->getName(), $request->route()->parameters() + $query);
        }

        return view($this->baseView . 'cruise-itinerary', compact('title', 'listDuration', 'list'));
    }

    public function cruiseItineraryGetById(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $data = $request->all();
        try {
            $list = [];
            $data = SourceDataService::getCruiseItineraryById($data['id'], $language->id);
            for ($i = 0; $i < count($data); $i++) {
                $obj = SourceDataUtils::bindSourceDataCruiseItineraryDetail($data[$i]);
                $list[$obj['id']] = $obj;
            }

            return response()->json([
                'msg' => 'success',
                'data' => $list
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }

    public function cruiseIndex(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Danh sách du thuyền';

        \SEO::setTitle($title);

        $param = [];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }
        if ($request->exclude_id) {
            $param['exclude_id'] = json_decode($request->exclude_id);
        }
        $list = SourceDataService::getCruisePaging($param, $language->id);

        $currentPage = (int) $request->get('page', 1);
        if ($currentPage > 1 && $currentPage > $list->lastPage()) {
            $query = $request->query();
            $query['page'] = $list->lastPage();
            return redirect()->route($request->route()->getName(), $request->route()->parameters() + $query);
        }

        return view($this->baseView . 'cruise', compact('title', 'list'));
    }

    public function cruiseGetById(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');
        
        $data = $request->all();
        try {
            $list = [];
            $data = SourceDataService::getCruiseById($data['id'], $language->id);
            for ($i = 0; $i < count($data); $i++) {
                $obj = SourceDataUtils::bindSourceDataCruiseDetail($data[$i], $languageCode);
                $list[$obj['id']] = $obj;
            }

            return response()->json([
                'msg' => 'success',
                'data' => $list
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }

    public function cabinIndex(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Danh sách cabin';

        \SEO::setTitle($title);

        $listCruise = AppCruiseService::getAll($language->id)->pluck('name', 'id');

        $param = [];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }
        if ($request->cruise_id) {
            $param['cruise_id'] = $request->cruise_id;
        }
        if ($request->exclude_id) {
            $param['exclude_id'] = json_decode($request->exclude_id);
        }
        $list = SourceDataService::getCabinPaging($param, $language->id);
        
        $currentPage = (int) $request->get('page', 1);
        if ($currentPage > 1 && $currentPage > $list->lastPage()) {
            $query = $request->query();
            $query['page'] = $list->lastPage();
            return redirect()->route($request->route()->getName(), $request->route()->parameters() + $query);
        }

        $listId = $list->pluck('id')->toArray();
        $listPrice = SourceDataService::getCabinMinPriceById($listId, $language->id)->pluck('min_price', 'cabin_id')->toArray();

        return view($this->baseView . 'cabin', compact('title', 'listCruise','list', 'listPrice'));
    }

    public function cabinGetById(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');
        
        $data = $request->all();
        try {
            $list = [];
            $data = SourceDataService::getCabinById($data['id'], $language->id);
            for ($i = 0; $i < count($data); $i++) {
                $obj = SourceDataUtils::bindSourceDataCabinDetail($data[$i], $languageCode);
                $list[$obj['id']] = $obj;
            }

            return response()->json([
                'msg' => 'success',
                'data' => $list
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }

    public function serviceIndex(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Danh sách dịch vụ';

        \SEO::setTitle($title);

        $param = [];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }
        if ($request->group_id) {
            $param['group_id'] = $request->group_id;
        }
        if ($request->exclude_id) {
            $param['exclude_id'] = json_decode($request->exclude_id);
        }
        $list = SourceDataService::getServicePaging($param, $language->id);

        $currentPage = (int) $request->get('page', 1);
        if ($currentPage > 1 && $currentPage > $list->lastPage()) {
            $query = $request->query();
            $query['page'] = $list->lastPage();
            return redirect()->route($request->route()->getName(), $request->route()->parameters() + $query);
        }

        return view($this->baseView . 'service', compact('title', 'list'));
    }

    public function serviceGetById(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');
        
        $data = $request->all();
        try {
            $list = [];
            $data = SourceDataService::getServiceById($data['id'], $language->id);
            for ($i = 0; $i < count($data); $i++) {
                $obj = SourceDataUtils::bindSourceDataServiceDetail($data[$i], $languageCode);
                $list[$obj['id']] = $obj;
            }

            return response()->json([
                'msg' => 'success',
                'data' => $list
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }

    public function amenityIndex(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Danh sách tiện ích';

        \SEO::setTitle($title);

        $param = [];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }
        if ($request->exclude_id) {
            $param['exclude_id'] = json_decode($request->exclude_id);
        }
        $list = SourceDataService::getAmenityPaging($param, $language->id);

        $currentPage = (int) $request->get('page', 1);
        if ($currentPage > 1 && $currentPage > $list->lastPage()) {
            $query = $request->query();
            $query['page'] = $list->lastPage();
            return redirect()->route($request->route()->getName(), $request->route()->parameters() + $query);
        }

        return view($this->baseView . 'amenity', compact('title', 'list'));
    }

    public function amenityGetById(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');
        
        $data = $request->all();
        try {
            $list = [];
            $data = SourceDataService::getAmenityById($data['id'], $language->id);
            for ($i = 0; $i < count($data); $i++) {
                $obj = SourceDataUtils::bindSourceDataAmenityDetail($data[$i], $languageCode);
                $list[$obj['id']] = $obj;
            }

            return response()->json([
                'msg' => 'success',
                'data' => $list
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }

    public function expActivityIndex(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        
        $title = 'Danh sách hoạt động trải nghiệm';

        \SEO::setTitle($title);

        $param = [];
        if ($request->keyword) {
            $param['keyword'] = $request->keyword;
        }
        if ($request->group_id) {
            $param['group_id'] = $request->group_id;
        }
        if ($request->exclude_id) {
            $param['exclude_id'] = json_decode($request->exclude_id);
        }
        $list = SourceDataService::getExpActivityPaging($param, $language->id);

        $currentPage = (int) $request->get('page', 1);
        if ($currentPage > 1 && $currentPage > $list->lastPage()) {
            $query = $request->query();
            $query['page'] = $list->lastPage();
            return redirect()->route($request->route()->getName(), $request->route()->parameters() + $query);
        }

        return view($this->baseView . 'exp-activity', compact('title', 'list'));
    }

    public function expActivityGetById(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');
        
        $data = $request->all();
        try {
            $list = [];
            $data = SourceDataService::getExpActivityById($data['id'], $language->id);
            for ($i = 0; $i < count($data); $i++) {
                $obj = SourceDataUtils::bindSourceDataExpActivityDetail($data[$i], $languageCode);
                $list[$obj['id']] = $obj;
            }

            return response()->json([
                'msg' => 'success',
                'data' => $list
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }
}
