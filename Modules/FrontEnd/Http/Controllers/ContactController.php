<?php

namespace Modules\FrontEnd\Http\Controllers;

use App\Mail\SendMail;
use Illuminate\Http\Request;
use Mail;
use Modules\BackEnd\Helpers\Logging;
use Modules\FrontEnd\Helpers\FeUtils;
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
        $title = FeUtils::bindWebsiteTitle($config['website-name'], $config['website-slogan']);

        \SEO::setTitle($title);
        \SEO::setDescription($config['website-description']);
        \SEO::setCanonical(route(Utilities::getRouteName('frontend.contact.index'), ['languageCode' => $languageCode]));

        \OpenGraph::setSiteName($config['website-name']);
        \OpenGraph::setTitle($title);
        \OpenGraph::setUrl(route(Utilities::getRouteName('frontend.contact.index'), ['languageCode' => $languageCode]));
        \OpenGraph::addImage(\URL::to('/') . config('frontend.organizationLogoSocial.url'), [
            'width' => config('frontend.organizationLogoSocial.width'),
            'height' => config('frontend.organizationLogoSocial.height'),
        ]);

        \TwitterCard::setType('summary');
        \TwitterCard::setTitle($title);
        \TwitterCard::setDescription($config['website-description']);
        \TwitterCard::setUrl(route(Utilities::getRouteName('frontend.contact.index'), ['languageCode' => $languageCode]));
        \TwitterCard::setImage(\URL::to('/') . config('frontend.organizationLogoSocial.url'));

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
