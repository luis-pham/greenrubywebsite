<?php

namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackEnd\Helpers\ContentLanguages;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AdLanguageService;

/**
 * Switches the content language only (which language's rows the editor reads and writes). It never changes a
 * language's site status and never the admin UI language. Phase 1E-a: only editable languages can be chosen, and
 * the editor stays in the same admin section (`return`), falling back to the dashboard.
 */
class ChangeLanguageController extends Controller
{
    public function index(Request $request)
    {
        $language = AdLanguageService::find((int) $request->get('languageId'));
        if (!ContentLanguages::isEditable($language)) {
            return redirect()->back()->withErrors('Ngôn ngữ này không biên tập được (trạng thái "Tắt").');
        }
        LanguageUtils::setCurrentLanguage($language);

        $return = (string) $request->get('return', '');
        if ($return !== '') {
            $path = ContentLanguages::sectionPathFor($return, $language);
            try {
                app('router')->getRoutes()->match(Request::create($path, 'GET'));

                return redirect($path);
            } catch (\Throwable $e) {
                // No such page in this language (or not language-aware): dashboard below.
            }
        }

        $routeName = 'backend.index';
        $routeParams = [];
        if (!$language->is_default) {
            $routeName = Utilities::bindRouteNameMultiLanguage($routeName);
            $routeParams['languageCode'] = $language->code;
        }

        return redirect(route($routeName, $routeParams));
    }
}
