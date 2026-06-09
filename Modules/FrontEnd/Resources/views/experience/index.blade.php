@extends('frontend::layouts.master')
@php 
    $languageCode = Route::current()->parameter('languageCode');
    $pageConfig = isset($pageConfig) ? $pageConfig : [];

    $listBanner = [];
    $listBanner[0] = new stdClass();
    $listBanner[0]->title = __('frontend::experience.section_1_title');
    $listBanner[0]->description = __("frontend::experience.section_1_description");
    $listBanner[0]->link = asset('assets/frontend/images/modules/experience/exp-cover.jpg');

    $listSustainability = isset($pageConfig[PageConfigKeyConsts::EXPERIENCE_SUSTAINABILITY])
        ? $pageConfig[PageConfigKeyConsts::EXPERIENCE_SUSTAINABILITY]
        : [];    
    $listGallery = isset($pageConfig[PageConfigKeyConsts::EXPERIENCE_GALLERY])
        ? $pageConfig[PageConfigKeyConsts::EXPERIENCE_GALLERY]
        : [];    
    $readyToSailDescription = isset($pageConfig[PageConfigKeyConsts::EXPERIENCE_READY_TO_SAIL_DESCRIPTION])
        ? $pageConfig[PageConfigKeyConsts::EXPERIENCE_READY_TO_SAIL_DESCRIPTION]
        : '';
    $readyToSailContent = isset($pageConfig[PageConfigKeyConsts::EXPERIENCE_READY_TO_SAIL_CONTENT])
        ? $pageConfig[PageConfigKeyConsts::EXPERIENCE_READY_TO_SAIL_CONTENT]
        : '';
@endphp

@section('content')
    <div id="experience">
        @include('frontend::shared.section.section-cover', [
            'class' => 'section-1 section-cover-sm',
            'list' => $listBanner,
            'tagHeading' => 'h1',
        ])
        <section class="section-2 bg bg-azure">
            <div class="container-fluid">
                <div class="container">
                    <h2 class="section-title">{{ __('frontend::experience.section_2_title') }}</h2>
                    <p class="section-description font-heading">{{ __('frontend::experience.section_2_description') }}</p>
                    @if (count($listExperience) > 0)
                        <div class="slide-1">
                            <div class="list-itinerary-cruise owl-carousel owl-theme">
                                @for ($i = 0; $i < count($listExpFeatured); $i++)
                                    <div class="item d-flex h-100 bg-white">
                                        <div class="item-wrapper d-flex flex-column w-100 h-100">
                                            <div class="item-header">
                                                <a href="{{ route(Utilities::getRouteName('frontend.experience.show'), ['languageCode' => $languageCode, 'slug' => Utilities::convertToAlias($listExpFeatured[$i]->name), 'id' => $listExpFeatured[$i]->id]) }}">
                                                    @include('frontend::shared.image-wrapper', [
                                                        'link' => $listExpFeatured[$i]->image_link,
                                                        'alt' => $listExpFeatured[$i]->name,
                                                        'imageConfig' => ['w' => 451, 'h' => 335],
                                                    ])
                                                </a>
                                            </div>
                                            <div class="item-body">
                                                <div class="item-content-wrapper">
                                                    <h3 class="title font-heading give-ellipsis after-1-lines">
                                                        <a href="{{ route(Utilities::getRouteName('frontend.experience.show'), ['languageCode' => $languageCode, 'slug' => Utilities::convertToAlias($listExpFeatured[$i]->name), 'id' => $listExpFeatured[$i]->id]) }}" class="text-reset">{{ $listExpFeatured[$i]->name }}</a>
                                                    </h3>
                                                    <p class="description give-ellipsis after-3-lines">{{ $listExpFeatured[$i]->summary }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
        
        @include('frontend::shared.section.section-experience', [
            'class' => 'section-3',
            'title' => __('frontend::experience.section_3_title'),
            'subTitle' => __('frontend::experience.section_3_description'),
            'list' => $listExperience,
            'tabButtons' => $tabButtons,
            'tagHeading' => 'h2',
        ])

        @include('frontend::shared.section.section-gallery',[
            'title' => __('frontend::experience.section_gallery_title'),
            'description' =>__('frontend::experience.section_gallery_description'),
            'list' => $listGallery,
            'bgClass' => 'bg-white'
        ])


        @include('frontend::shared.section.section-testimonial', ['class' => 'section-4'])


        <section class="section-5 bg bg-azure">
            <div class="container-fluid">
                <div class="container">
                    <h2 class="section-title">{{ __('frontend::experience.section_5_title') }}</h2>
                    <p class="section-description font-heading">{{ __('frontend::experience.section_5_description') }}</p>
                    <div class="section-content-sustainability">
                        @if (count($listSustainability) > 0)
                        <div class="slide-1">
                            <div class="list-item owl-carousel owl-theme">
                                @for ($i = 0; $i < count($listSustainability); $i++)
                                    <div class="item d-flex h-100 text-center">
                                        <div class="item-wrapper d-flex flex-column align-content-between w-100 bg-white">
                                            <img src="{{ asset(FeUtils::getThumbnail(['link' => $listSustainability[$i]->link, 'w' => 80, 'h' => 80])) }}" alt="{{ $listSustainability[$i]->title }}" class="icon mb-4 mx-auto" />
                                            <h3 class="title mb-2 font-weight-bold give-ellipsis after-2-lines">{{ $listSustainability[$i]->title }}</h3>
                                            <p class="description mb-0 text-break give-ellipsis after-3-lines">{{ $listSustainability[$i]->description }}</p>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </section>

        @include('frontend::shared.section.section-call-to-action', [
            'class' => 'section-6',
            'description' => $readyToSailDescription,
            'content' => $readyToSailContent,
            'buttons' => [[
                'label' => __('frontend::experience.button_book_now'),
                'class' => 'btn-warning',
                'url' => route(Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode])
            ], [
                'label' => __('frontend::experience.button_explore_itinerary'),
                'class' => 'btn-success',
                'url' => route(Utilities::getRouteName('frontend.itinerary.index'), ['languageCode' => $languageCode])
            ]]
        ])
    </div>
@endsection