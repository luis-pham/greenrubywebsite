<?php
namespace Modules\BackEnd\Helpers;

use Illuminate\Support\Facades\Route;

class SourceDataUtils
{
    public static function getSourceDataInfo($name)
    {
        $languageCode = Route::current()->parameter('languageCode');

        $info = [
            'title' => '',
            'url' => [
                'selectData' => '',
                'getData' => ''
            ]
        ];
        switch ($name) {
            case 'article':
                $info['title'] = 'Tin tức';
                $info['url']['selectData'] = route(Utilities::getRouteName('backend.sourceData.article.index'), ['languageCode' => $languageCode, 'callback' => 'selectCallBack']);
                $info['url']['getData'] = route(Utilities::getRouteName('backend.sourceData.article.getById'), ['languageCode' => $languageCode]);
                break;
            case 'faq':
                $info['title'] = 'Câu hỏi thường gặp';
                $info['url']['selectData'] = route(Utilities::getRouteName('backend.sourceData.faq.index'), ['languageCode' => $languageCode, 'callback' => 'selectCallBack']);
                $info['url']['getData'] = route(Utilities::getRouteName('backend.sourceData.faq.getById'), ['languageCode' => $languageCode]);
                break;
            case 'cruise-itinerary':
                $info['title'] = 'Hành trình';
                $info['url']['selectData'] = route(Utilities::getRouteName('backend.sourceData.cruiseItinerary.index'), ['languageCode' => $languageCode, 'callback' => 'selectCallBack']);
                $info['url']['getData'] = route(Utilities::getRouteName('backend.sourceData.cruiseItinerary.getById'), ['languageCode' => $languageCode]);
                break;
            case 'cruise':
                $info['title'] = 'Du thuyền';
                $info['url']['selectData'] = route(Utilities::getRouteName('backend.sourceData.cruise.index'), ['languageCode' => $languageCode, 'callback' => 'selectCallBack']);
                $info['url']['getData'] = route(Utilities::getRouteName('backend.sourceData.cruise.getById'), ['languageCode' => $languageCode]);
                break;
            case 'cabin':
                $info['title'] = 'Cabin';
                $info['url']['selectData'] = route(Utilities::getRouteName('backend.sourceData.cabin.index'), ['languageCode' => $languageCode, 'callback' => 'selectCallBack']);
                $info['url']['getData'] = route(Utilities::getRouteName('backend.sourceData.cabin.getById'), ['languageCode' => $languageCode]);
                break;
            case 'service':
                $info['title'] = 'Dịch vụ';
                $info['url']['selectData'] = route(Utilities::getRouteName('backend.sourceData.service.index'), ['languageCode' => $languageCode, 'callback' => 'selectCallBack']);
                $info['url']['getData'] = route(Utilities::getRouteName('backend.sourceData.service.getById'), ['languageCode' => $languageCode]);
                break;
            case 'amenity':
                $info['title'] = 'Tiện ích';
                $info['url']['selectData'] = route(Utilities::getRouteName('backend.sourceData.amenity.index'), ['languageCode' => $languageCode, 'callback' => 'selectCallBack']);
                $info['url']['getData'] = route(Utilities::getRouteName('backend.sourceData.amenity.getById'), ['languageCode' => $languageCode]);
                break;
            case 'exp-activity':
                $info['title'] = 'Hoạt động trải nghiệm';
                $info['url']['selectData'] = route(Utilities::getRouteName('backend.sourceData.expActivity.index'), ['languageCode' => $languageCode, 'callback' => 'selectCallBack']);
                $info['url']['getData'] = route(Utilities::getRouteName('backend.sourceData.expActivity.getById'), ['languageCode' => $languageCode]);
                break;
            default:
                break;
        }

        return $info;
    }

    public static function bindSourceDataArticleDetail($obj, $languageCode)
    {
        return [
            'id' => $obj->id,
            'title' => $obj->title,
            'image_link_full' => Utilities::getFileLink($obj->image_link),
            'url' => \Auth::user()->can('article-read')
                ? route(Utilities::getRouteName('backend.article.show'), ['languageCode' => $languageCode, 'id' => $obj->id])
                : null
        ];
    }

    public static function bindSourceDataFaqDetail($obj, $languageCode)
    {
        return [
            'id' => $obj->id,
            'title' => strip_tags($obj->question),
            'url' => \Auth::user()->can('faq-read')
                ? route(Utilities::getRouteName('backend.faq.show'), ['languageCode' => $languageCode, 'id' => $obj->id])
                : null
        ];
    }

    public static function bindSourceDataCruiseItineraryDetail($obj)
    {
        $id = $obj->id . '-' . $obj->cruise_id;
        return [
            'id' => $id,
            'title' => strip_tags($obj->name . ' | ' . $obj->cruise_name),
            'image_link_full' => Utilities::getFileLink($obj->image_link),
            'url' => null
        ];
    }

    public static function bindSourceDataCruiseDetail($obj, $languageCode)
    {
        return [
            'id' => $obj->id,
            'title' => $obj->name,
            'image_link_full' => Utilities::getFileLink($obj->image_link),
            'url' => /*\Auth::user()->can('cruise-read')
                ? route(Utilities::getRouteName('backend.cruise.show'), ['languageCode' => $languageCode, 'id' => $obj->id])
                :*/ null
        ];
    }

    public static function bindSourceDataCabinDetail($obj, $languageCode)
    {
        return [
            'id' => $obj->id,
            'title' => $obj->name . ' | ' . $obj->cruise_name,
            'image_link_full' => Utilities::getFileLink($obj->image_link),
            'url' => \Auth::user()->can('cabin-manager-read')
                ? route(Utilities::getRouteName('backend.cabin.show'), ['languageCode' => $languageCode, 'id' => $obj->id])
                : null
        ];
    }

    public static function bindSourceDataServiceDetail($obj, $languageCode)
    {
        return [
            'id' => $obj->id,
            'title' => $obj->name,
            'image_link_full' => Utilities::getFileLink($obj->image_link),
            'url' => /*\Auth::user()->can('service-read')
                ? route(Utilities::getRouteName('backend.service.show'), ['languageCode' => $languageCode, 'id' => $obj->id])
                :*/ null
        ];
    }

    public static function bindSourceDataAmenityDetail($obj, $languageCode)
    {
        return [
            'id' => $obj->id,
            'title' => $obj->name,
            'image_link_full' => Utilities::getFileLink($obj->icon),
            'url' => /*\Auth::user()->can('amenity-read')
                ? route(Utilities::getRouteName('backend.amenity.show'), ['languageCode' => $languageCode, 'id' => $obj->id])
                :*/ null
        ];
    }

    public static function bindSourceDataExpActivityDetail($obj, $languageCode)
    {
        return [
            'id' => $obj->id,
            'title' => $obj->name,
            'image_link_full' => Utilities::getFileLink($obj->image_link),
            'url' => /*\Auth::user()->can('exp-activity-read')
                ? route(Utilities::getRouteName('backend.expActivity.show'), ['languageCode' => $languageCode, 'id' => $obj->id])
                :*/ null
        ];
    }
}