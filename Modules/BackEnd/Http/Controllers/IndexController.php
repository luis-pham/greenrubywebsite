<?php

namespace Modules\BackEnd\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Helpers\Utilities;

class IndexController extends Controller
{
    private $baseView = 'backend::index.';

    public function index()
    {
        $language = LanguageUtils::getCurrentLanguage();

        $title = 'Trang chủ';

        \SEO::setTitle($title);

        $config = Utilities::getAllConfig($language);

        return view($this->baseView . __FUNCTION__, compact('config'));
    }
}
