<?php
namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AppPageService;
use Modules\BackEnd\Services\AppPageConfigService;
use Modules\BackEnd\Services\AppPageSectionService;

class PageConfigController extends Controller
{
    private $baseView = 'backend::page-config.';

    public function index(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        $pageCode = $request->route('pageCode');
        $page = AppPageService::getByCode($pageCode, $language->id);
        if (!$page) {
            return abort(404);
        }

        $pageName = $this->getPageName($page->title);
        $title = 'Cấu hình ' . $pageName;

        \SEO::setTitle($title);

        $listSection = AppPageSectionService::getByPageId($page->id);
        $listConfig = [];
        $dataConfig = AppPageConfigService::getByPageId($page->id);
        for ($i = 0; $i < count($dataConfig); $i++) {
            $key = $dataConfig[$i]->section_id;
            if (!array_key_exists($key, $listConfig)) {
                $listConfig[$key] = [];
            }
            $listConfig[$key][] = $dataConfig[$i];
        }

        return view($this->baseView . __FUNCTION__, compact('title', 'listSection', 'listConfig'));
    }

    public function update(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');
        $pageCode = $request->route('pageCode');
        $page = AppPageService::getByCode($pageCode, $language->id);
        if (!$page) {
            return abort(404);
        }

        $pageName = $this->getPageName($page->title);

        try {
            $data = $request->all();

            foreach ($data as $key => $value) {
                if ($key == '_token') {
                    continue;
                }

                $obj = AppPageConfigService::getByPageIdAndKey($page->id, $key);
                if ($obj) {
                    $data = [
                        'id' => $obj->id,
                        'value' => $value ?: ''
                    ];
                    AppPageConfigService::update($data);
                }
            }

            Logging::LogInfo('Sửa cấu hình ' . $pageName . ' thành công.');

            return redirect(route(Utilities::getRouteName('backend.page-config.index'), ['languageCode' => $languageCode, 'pageCode' => $pageCode]))->with('flash-message', 'Sửa cấu hình ' . $pageName . ' thành công!');
        } catch (\Exception $e) {
            Logging::LogError('Sửa cấu hình ' . $pageName . ' lỗi.', 'Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Sửa cấu hình ' . $pageName . ' lỗi!');
        }
    }

    private function getPageName($title)
    {
        return Str::startsWith($title, 'Trang') ? $title : 'Trang ' . $title;
    }
}
