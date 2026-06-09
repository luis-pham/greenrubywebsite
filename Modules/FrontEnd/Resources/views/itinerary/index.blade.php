@extends('frontend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');

    $listBanner = [];
    $listBanner[0] = new stdClass();
    $listBanner[0]->title = __('frontend::itineraryIndex.section_cover_title');
    $listBanner[0]->description = __("frontend::itineraryIndex.section_cover_description");
    $listBanner[0]->link = asset('assets/frontend/images/modules/itinerary/banner.png');

@endphp

@section('content')
    <div id="itinerary">
        @include('frontend::shared.section.section-cover',[
            'class' => 'section-cover-sm',
            'list' => $listBanner,
            'tagHeading' => 'h1'
        ])
        @include('frontend::shared.section.section-itinerary',[
            'class' => 'bg-tender-white',
            'title' => __('frontend::itineraryIndex.section_itinerary_title'),
            'subTitle' => __('frontend::itineraryIndex.section_itinerary_description'),
            'list' => $listItinerary,
            'tagHeading' => 'h2'
        ])
        @include('frontend::shared.section.section-amenity',[
            'class' => 'section-service',
            'list' => $listInclusiveService,
            'title' => __('frontend::itineraryIndex.section_service_title'),
            'description' => __('frontend::itineraryIndex.section_service_description')
        ])
        <section class="section-faq bg bg-tender-white">
            <div class="container-fluid">
                <div class="container">
                    <h2 class="section-title">{{ __('frontend::itineraryIndex.section_faq_title') }}</h2>
                    <p class="section-description font-heading">{{ __('frontend::itineraryIndex.section_faq_description') }}</p>
                    <div class="slide-1">
                        <div class="grid-faq-outer">
                            <div class="grid-faq-inner owl-carousel owl-theme">
                                @foreach( $listFaq as $faq)
                                    <div class="item">
                                        <div class="question">{!! $faq->question !!}</div>
                                        <div class="answer">{!! $faq->answer !!}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="section-contact bg">
            <div class="container-fluid">
                <div class="container">
                    <p class="section-title font-heading">{{ __('frontend::itineraryIndex.section_contact_title') }}</p>
                    <p class="section-description">{{ __('frontend::itineraryIndex.section_contact_description') }}</p>
                    <div class="d-flex justify-content-center align-items-center list-btn">
                        <a class="btn-rounded btn-warning btn-chat-with-ai" href="javascript:">{{__('frontend::itineraryIndex.section_contact_button_chat_with_us')}}</a>
                        <a class="btn-rounded btn-success" href="{{route(\Modules\BackEnd\Helpers\Utilities::getRouteName('frontend.contact.index'),['languageCode' => $languageCode])}}">{{__('frontend::itineraryIndex.section_contact_button_contact_sales')}}</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
