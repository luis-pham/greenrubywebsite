<?php

namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AdLanguageService;

class ChangeLanguageController extends Controller
{
    public function index(Request $request)
    {
        $languageId = (int)$request->get('languageId');
        $language = AdLanguageService::find($languageId);
        LanguageUtils::setCurrentLanguage($language);

        $routeName = 'backend.index';
        $routeParams = [];
        if (!$language->is_default) {
            $routeName = Utilities::bindRouteNameMultiLanguage($routeName);
            $routeParams['languageCode'] = $language->code;
        }
        $route = route($routeName, $routeParams);
        
        return redirect($route);
    }
}
