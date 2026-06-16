<?php

namespace Modules\FrontEnd\Http\Controllers;

use App\Mail\SendMail;
use Illuminate\Http\Request;
use Mail;
use Modules\BackEnd\Helpers\Logging;
use Modules\FrontEnd\Helpers\FeUtils;
use Modules\FrontEnd\Constants\PageCodeConsts;
use Modules\FrontEnd\Http\Requests\Contact\ContactRequest;
use Modules\FrontEnd\Helpers\FeLanguageUtils;
use Modules\BackEnd\Helpers\Utilities;

class ContactController
{
    private string $baseView = "frontend::contact.";
    public function index(Request $request){
        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $config = Utilities::getAllConfig($language);
        $canonicalUrl = FeUtils::frontendRoute('frontend.contact.index', [], $languageCode);
        $seo = FeUtils::resolveHubSeo(
            PageCodeConsts::CONTACT,
            $language,
            fn () => FeUtils::bindWebsiteTitle($config['website-name'], $config['website-slogan']),
            $config['website-description']
        );
        FeUtils::applyHubSeoMeta($seo, $canonicalUrl, $config);

        return view($this->baseView.__FUNCTION__, compact('config'));
    }

    public function request(ContactRequest $request){
        try {
            Mail::to(config('mail.admin.address'))
                ->send(new SendMail(__('frontend::contact.subject'),$request->only('name', 'phone', 'email', 'request_content')));

            return response()->json([
                'status' => 'success',
                'message' => __('frontend::contact.request-success')
            ]);

        } catch (\Exception $e) {
            Logging::logError($e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('frontend::contact.request-error')
            ], 500);
        }
    }
}
