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
            'list' => $listItinerary,
            'itineraryRedesign' => true,
        ])
        <section class="section-faq bg bg-tender-white">
            <div class="container-fluid">
                <div class="container">
                    <p class="section-eyebrow section-eyebrow--gold">{{ __('frontend::itineraryIndex.section_faq_title') }}</p>
                    <p class="section-description font-heading">{{ __('frontend::itineraryIndex.section_faq_description') }}</p>
                    @if (count($listFaq) > 0)
                        <ul class="list-faq list-unstyled">
                            @foreach ($listFaq as $faq)
                                <li class="item">
                                    <div class="item-wrapper position-relative">
                                        @if (!empty($faq->group_name))
                                            <div class="group-name d-inline-block mb-3 mb-xl-2 text-uppercase">{{ $faq->group_name }}</div>
                                        @endif
                                        <div class="question article-content">{!! safe_html($faq->question) !!}</div>
                                        <div class="answer article-content">{!! safe_html($faq->answer) !!}</div>
                                        <button type="button" class="btn-toggle border-0 rounded-circle" title="Toggle FAQ">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <div class="text-center">
                            <a href="{{ route(\Modules\BackEnd\Helpers\Utilities::getRouteName('frontend.faq.index'), ['languageCode' => $languageCode]) }}" class="btn btn-lg btn-warning">
                                {{ __('frontend::common.button_view_all') }}
                                <i class="fa-solid fa-arrow-right-long ml-2"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </section>
        <section class="section-contact bg bg-tender-white">
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
