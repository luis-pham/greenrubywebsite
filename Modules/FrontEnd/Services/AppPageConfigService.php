<?php
namespace Modules\FrontEnd\Services;

use Modules\BackEnd\Entities\AppPageConfig;

class AppPageConfigService
{
    public static function getByPageCode($pageCode, $languageId)
    {
        $query = new AppPageConfig();
        return $query->join('app_page', 'app_page.id', '=', 'app_page_config.page_id')
            ->where('app_page.code', $pageCode)
            ->where('app_page.language_id', $languageId)
            ->get();
    }
}