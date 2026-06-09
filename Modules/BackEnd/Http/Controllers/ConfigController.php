<?php
namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Helpers\Logging;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AdConfigService;

class ConfigController extends Controller
{
    private $baseView = 'backend::config.';

    public function index()
    {
        $language = LanguageUtils::getCurrentLanguage();

        $title = 'Cấu hình hệ thống';

        \SEO::setTitle($title);

        $list = AdConfigService::getAll($language->id);
        $listCommon = $list->where('language_id', null);
        $listByLanguage = $list->where('language_id', $language->id);

        return view($this->baseView . __FUNCTION__, compact('title', 'listCommon', 'listByLanguage'));
    }

    public function update(Request $request)
    {
        $languageCode = $request->route('languageCode');

        try {
            $data = $request->all();

            foreach ($data as $key => $value) {
                if ($key == '_token') {
                    continue;
                }

                $obj = AdConfigService::getByKey($key);
                if ($obj) {
                    $data = [
                        'id' => $obj->id,
                        'value' => $value ?: ''
                    ];
                    AdConfigService::update($data);
                }
            }

            Logging::LogInfo('Sửa cấu hình hệ thống thành công.');

            return redirect(route(Utilities::getRouteName('backend.config.index'), ['languageCode' => $languageCode]))->with('flash-message', 'Sửa cấu hình hệ thống thành công!');
        } catch (\Exception $e) {
            Logging::LogError('Sửa cấu hình hệ thống lỗi.', 'Exception = ' . $e->getMessage());

            return redirect()->back()->withErrors('Sửa cấu hình hệ thống lỗi!');
        }
    }
}
