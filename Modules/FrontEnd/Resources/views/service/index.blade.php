@extends('frontend::layouts.master')

@php 
    $languageCode = Route::current()->parameter('languageCode');

    $listBanner = [];
    $listBanner[0] = new stdClass();
    $listBanner[0]->title = __('frontend::service.section_1_title');
    $listBanner[0]->description = __("frontend::service.section_1_description");
    $listBanner[0]->link = asset('assets/frontend/images/modules/service/service-cover.jpg');

    $listService = isset($pageConfig[PageConfigKeyConsts::SERVICE_SERVICE])
        ? $pageConfig[PageConfigKeyConsts::SERVICE_SERVICE]
        : [];

    $listCabin = isset($pageConfig[PageConfigKeyConsts::SERVICE_CABIN])
        ? $pageConfig[PageConfigKeyConsts::SERVICE_CABIN]
        : [];
    $listEvent = isset($pageConfig[PageConfigKeyConsts::SERVICE_EVENT])
        ? $pageConfig[PageConfigKeyConsts::SERVICE_EVENT]
        : [];
    $weddingTitle = isset($pageConfig[PageConfigKeyConsts::SERVICE_WEDDING_TITLE])
        ? $pageConfig[PageConfigKeyConsts::SERVICE_WEDDING_TITLE]
        : '';
    $weddingDescription = isset($pageConfig[PageConfigKeyConsts::SERVICE_WEDDING_DESCRIPTION])
        ? $pageConfig[PageConfigKeyConsts::SERVICE_WEDDING_DESCRIPTION]
        : '';
    $weddingContent = isset($pageConfig[PageConfigKeyConsts::SERVICE_WEDDING_CONTENT])
        ? $pageConfig[PageConfigKeyConsts::SERVICE_WEDDING_CONTENT]
        : '';
    $weddingFeaturedImage = isset($pageConfig[PageConfigKeyConsts::SERVICE_WEDDING_FEATURED_IMAGE])
        ? $pageConfig[PageConfigKeyConsts::SERVICE_WEDDING_FEATURED_IMAGE]
        : '';
    $listWeddingIcon = isset($pageConfig[PageConfigKeyConsts::SERVICE_WEDDING_ICON])
        ? $pageConfig[PageConfigKeyConsts::SERVICE_WEDDING_ICON]
        : [];
    $quoteRequestTitle = isset($pageConfig[PageConfigKeyConsts::SERVICE_QUOTE_REQUEST_TITLE])
        ? $pageConfig[PageConfigKeyConsts::SERVICE_QUOTE_REQUEST_TITLE]
        : '';
    $quoteRequestDescription = isset($pageConfig[PageConfigKeyConsts::SERVICE_QUOTE_REQUEST_DESCRIPTION])
        ? $pageConfig[PageConfigKeyConsts::SERVICE_QUOTE_REQUEST_DESCRIPTION]
        : '';
    $quoteRequestContent = isset($pageConfig[PageConfigKeyConsts::SERVICE_QUOTE_REQUEST_CONTENT])
        ? $pageConfig[PageConfigKeyConsts::SERVICE_QUOTE_REQUEST_CONTENT]
        : '';
    $quoteRequestHotline = isset($pageConfig[PageConfigKeyConsts::SERVICE_QUOTE_REQUEST_HOTLINE])
        ? $pageConfig[PageConfigKeyConsts::SERVICE_QUOTE_REQUEST_HOTLINE]
        : '';
    $readyToSailDescription = isset($pageConfig[PageConfigKeyConsts::SERVICE_READY_TO_SAIL_DESCRIPTION])
        ? $pageConfig[PageConfigKeyConsts::SERVICE_READY_TO_SAIL_DESCRIPTION]
        : '';
    $readyToSailContent = isset($pageConfig[PageConfigKeyConsts::SERVICE_READY_TO_SAIL_CONTENT])
        ? $pageConfig[PageConfigKeyConsts::SERVICE_READY_TO_SAIL_CONTENT]
        : '';

@endphp

@section('content')
    <div id="service"> 
        @include('frontend::shared.section.section-cover', [
            'class' => 'section-1 section-cover-sm',
            'list' => $listBanner,
            'tagHeading' => 'h1',
        ])
        <section class="section-2 bg bg-azure">
            <div class="container-fluid">
                <div class="container">
                    <p class="section-eyebrow section-eyebrow--gold">{{ __('frontend::service.section_2_title') }}</p>
                    <p class="section-description font-heading">{{ __('frontend::service.section_2_description') }}</p>
                    @if (count($listService) > 0)
                        <div class="list-itinerary-cruise row mt-4 d-none d-lg-flex {{ App::getLocale() == 'vi' ? 'is-vi' : '' }}">
                            @for ($i = 0; $i < count($listService); $i++)
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="item d-flex h-100 bg-white shadow-sm">
                                        <div class="item-wrapper d-flex flex-column w-100">
                                            <div class="item-header">
                                                <a href="javascript:;" class="btn-view-service-details" data-id="{{ $listService[$i]->id }}">
                                                    @include('frontend::shared.image-wrapper', [
                                                        'link' => $listService[$i]->image_link,
                                                        'alt' => $listService[$i]->name,
                                                        'imageConfig' => ['w' => 451, 'h' => 270]
                                                    ])
                                                </a>
                                            </div>
                                            <div class="item-body flex-grow-1">
                                                <h3 class="title mb-2 font-heading give-ellipsis after-2-lines">
                                                    <a href="javascript:;" class="text-reset btn-view-service-details" data-id="{{ $listService[$i]->id }}">{{ $listService[$i]->name }}</a>
                                                </h3>
                                                <p class="description text-break give-ellipsis after-3-lines">{{ $listService[$i]->description }}</p>             
                                            </div>
                                            <div class="item-footer">
                                                <hr />
                                                <div class="d-flex justify-content-between align-items-center item-footer-content">
                                                    <div class="justify-content-start">
                                                        @if ($listService[$i]->price !== null)
                                                            <p class="price">{!! sprintf(__('frontend::service.price_per_guest'), FeUtils::formatDisplayCurrency($listService[$i]->price)) !!}</p>
                                                        @endif
                                                    </div>
                                                    <div class="justify-content-end">
                                                        <a href="javascript:;" class="btn-view-service-details btn-view-details d-block text-center" data-id="{{ $listService[$i]->id }}">{{ __('frontend::common.button_view_details') }}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                        <div class="slide-1 d-block d-lg-none mt-4">
                            <div class="list-itinerary-cruise owl-carousel owl-theme {{ App::getLocale() == 'vi' ? 'is-vi' : '' }}">
                                @for ($i = 0; $i < count($listService); $i++)
                                    <div class="item">
                                        <div class="d-flex h-100 w-100 bg-white shadow-sm">
                                            <div class="item-wrapper d-flex flex-column w-100">
                                                <div class="item-header">
                                                    <a href="javascript:;" class="btn-view-service-details d-block" data-id="{{ $listService[$i]->id }}">
                                                        @include('frontend::shared.image-wrapper', [
                                                            'link' => $listService[$i]->image_link,
                                                            'alt' => $listService[$i]->name
                                                        ])
                                                    </a>
                                                </div>
                                                <div class="item-body flex-grow-1">
                                                    <h3 class="title mb-2 font-heading give-ellipsis after-2-lines">
                                                        <a href="javascript:;" class="text-reset btn-view-service-details" data-id="{{ $listService[$i]->id }}">{{ $listService[$i]->name }}</a>
                                                    </h3>
                                                    <p class="description text-break give-ellipsis after-3-lines">{{ $listService[$i]->description }}</p>
                                                </div>
                                                <div class="item-footer">
                                                    <hr />
                                                    <div class="item-footer-content d-flex justify-content-between align-items-center">
                                                        <div class="justify-content-start">
                                                            @if ($listService[$i]->price !== null)                                    
                                                                <p class="price">{!! sprintf(__('frontend::service.price_per_guest'), FeUtils::formatDisplayCurrency($listService[$i]->price)) !!}</p>
                                                            @endif
                                                        </div>
                                                        <div class="justify-content-end">
                                                            <a href="javascript:;" class="btn-view-service-details btn-view-details d-block text-center" data-id="{{ $listService[$i]->id }}">{{ __('frontend::common.button_view_details') }}</a>
                                                        </div>
                                                    </div>
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
        
        <section class="section-3 section-itinerary bg">
            <div class="container-fluid px-0 position-relative">
                <div class="container">
                    <p class="section-eyebrow section-eyebrow--gold">{{ __('frontend::service.section_3_title') }}</p>
                    <p class="section-description font-heading text-white">{{ __('frontend::service.section_3_description') }}</p>
                    
                    <div class="section-content-service">
                        @if (count($listCabin) > 0)
                            <div class="list-service">
                                <div class="item">
                                    <div class="item-wrapper">
                                        <div class="image">
                                            <div class="owl-carousel cabin-carousel">
                                                @foreach ($listCabin as $cabin)
                                                    <div class="cabin-slide h-100"
                                                         data-name="{{ $cabin->name }}"
                                                         data-summary="{{ $cabin->summary }}"
                                                         data-capacity="{{ $cabin->capacity }}">
                                                        <div class="image h-100"
                                                             style="background-image: url({{ asset(FeUtils::getThumbnail(['link' => $cabin->image_link, 'w' => 949, 'h' => 500])) }})">
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                    
                                        @php 
                                            $first = $listCabin[0]; 
                                        @endphp
                                        <div class="main-info d-flex flex-column w-100" id="cabin-main-info">
                                            <div class="main-info-body">
                                                <p class="description give-ellipsis after-4-lines" id="cabin-summary">
                                                    {{ $first->summary }}
                                                </p>
                                            </div>
                                            <div class="main-info-footer">
                                                <div class="d-flex flex-row info-cabin">
                                                    <div class="icon-wrapper d-flex align-items-center">
                                                        <div class="icon">
                                                            <img class="w-100 h-100" src="{{ asset('/assets/frontend/images/modules/service/users.png') }}" alt="Michelin-Standard Banquet">
                                                        </div>
                                                    </div>                                                    <div class="d-flex flex-column ml-2">
                                                        <p class="title mb-2 give-ellipsis after-1-lines" id="cabin-name">{{ $first->name }}</p>
                                                        <p class="description give-ellipsis after-2-lines mb-0" id="cabin-capacity" data-template="{{ __('frontend::service.cabin_capacity', ['capacity' => ':capacity', 'cabin_name' => ':cabin_name']) }}">
                                                            {{ __('frontend::service.cabin_capacity', ['capacity' => $first->capacity]) }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-row align-items-center info-amenity">
                                                    @php
                                                        $amenityNames = is_string($listAmenity)
                                                            ? implode(', ', array_column(json_decode($listAmenity, true) ?? [], 'name'))
                                                            : (collect($listAmenity)->pluck('name')->implode(', '));
                                                    @endphp
                                                    <div class="icon-wrapper d-flex align-items-center">
                                                        <div class="icon">
                                                            <img class="w-100 h-100" src="{{ asset('/assets/frontend/images/modules/service/display.png') }}" alt="6-Star technology">
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-column ml-2">
                                                        <p class="title mb-2">{{ __('frontend::service.lable_6_star_technology') }}</p>
                                                        <p class="description give-ellipsis after-2-lines mb-0">{{ $amenityNames }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="section-content-sustainability">
                        @if (count($listEvent) > 0)
                        <div class="slide-1">
                            <div class="list-item list-item-sustainability owl-carousel owl-theme">
                                @for ($i = 0; $i < count($listEvent); $i++)
                                    <div class="item d-flex h-100 text-center">
                                        <div class="item-wrapper d-flex flex-column align-content-between w-100 bg-white">
                                            <img src="{{ asset(FeUtils::getThumbnail(['link' => $listEvent[$i]->link, 'w' => 80, 'h' => 80])) }}" alt="{{ $listEvent[$i]->title }}" class="icon mb-4 mx-auto" />
                                            <h3 class="title mb-2 font-weight-bold give-ellipsis after-2-lines">{{ $listEvent[$i]->title }}</h3>
                                            <p class="description mb-0 text-break give-ellipsis after-3-lines">{{ $listEvent[$i]->description }}</p>
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

        <section id="event" class="section-4 p-0">
            <div class="container-fluid px-0">
                <div class="cruise row no-gutters">
                    <div class="main-info col-lg-6">
                        <div class="main-info-wrapper text-white">
                            <p class="section-eyebrow section-eyebrow--gold text-left">{{ $weddingTitle }}</p>
                            <h3 class="section-description font-heading text-left text-white">{{ $weddingDescription }}</h3>
                            <div class="">
                                <p class="description give-ellipsis after-4-lines">{{ $weddingContent }}</p>
                                <div class="specification mb-3">
                                    <div class="list-item d-flex flex-column">
                                        @foreach ($listWeddingIcon as $icon)
                                            <div class="item d-flex align-items-center"> 
                                                <div class="icon-wrapper">
                                                    <div class="image-wrapper">
                                                        <img src="{{ asset(FeUtils::getThumbnail(['link' => $icon->link, 'w' => 36, 'h' => 36])) }}" alt="{{ $icon->title }}" class="icon w-100 h-100" />
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <p class="title mb-2">{{ $icon->title }}</p>
                                                    <p class="description mb-0">{{ $icon->description }}</p>
                                                </div>
                                            </div>  
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <a href="javascript:;" class="btn btn-warning btn-chat-with-ai">
                                {{ __('frontend::service.button_get_wedding') }}
                                <i class="fa-solid fa-arrow-right-long ml-2"></i>
                            </a>
                        </div>
                    </div>
                    <div class="image col-lg-6" style="background-image: url({{ asset(FeUtils::getThumbnail(['link' => $weddingFeaturedImage, 'w' => 960, 'h' => 750])) }})"></div>
                    <div class="main-info proposal col-lg-6">
                        <div class="main-info-wrapper text-white">
                            <p class="section-eyebrow section-eyebrow--gold text-left">{{ $quoteRequestTitle }}</p>
                            <h3 class="section-description font-heading text-left text-white">{{ $quoteRequestDescription }}</h3>
                            <div class="">
                                <p class="description give-ellipsis after-4-lines">{{ $quoteRequestContent }}</p>
                                <div class="specification mb-4">
                                    <div class="d-flex flex-column">
                                        <div class="item d-flex align-items-center">                                                    
                                                <i class="fa-solid fa-phone"></i>
                                            <div class="d-flex flex-column">
                                                <p class="title mb-2">{{ __('frontend::service.title_event_hotline') }}</p>
                                                <p class="description mb-0">{{ $quoteRequestHotline }}</p>
                                            </div>
                                        </div>                                  
                                    </div>
                                </div>
                            </div>          
                        </div>
                    </div>
                    <div class="main-request col-lg-6">
                        <div class="main-request-content">
                            <form id="form-quote-request" class="w-100 row no-gutters" data-api-url="{{ url('/api/quote-request') }}" data-lang="{{ $languageCode }}" novalidate>
                                <div class="form-group col-12 col-lg-6">
                                    <label for="quote-contact-name">{{ __('frontend::service.lable_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="quote-contact-name" name="contact_name" placeholder="{{ __('frontend::service.place_holder_name') }}">
                                    <span class="quote-error" id="error-contact_name"></span>
                                </div>
                                <div class="form-group col-12 col-lg-6">
                                    <label for="quote-phone">{{ __('frontend::service.lable_phone') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="quote-phone" name="phone" placeholder="{{ __('frontend::service.place_holder_phone') }}">
                                    <span class="quote-error" id="error-phone"></span>
                                </div>
                                <div class="form-group col-12 col-lg-6">
                                    <label for="quote-event-type">{{ __('frontend::service.lable_event_type') }}</label>
                                    <select class="form-control" id="quote-event-type" name="event_type">
                                        <option value="">{{ __('frontend::service.place_holder_event_type') }}</option>
                                        <option value="meeting">{{ __('frontend::service.event_type_meeting') }}</option>
                                        <option value="business_seminar">{{ __('frontend::service.event_type_business_seminar') }}</option>
                                        <option value="wedding">{{ __('frontend::service.event_type_wedding') }}</option>
                                        <option value="birthday_party">{{ __('frontend::service.event_type_birthday_party') }}</option>
                                    
                                    </select>
                                    <span class="quote-error" id="error-event_type"></span>
                                </div>
                                <div class="form-group col-12 col-lg-6">
                                    <label for="quote-attendee">{{ __('frontend::service.lable_number') }}</label>
                                    <input type="number" class="form-control" id="quote-attendee" name="number" min="0" step="1" placeholder="{{ __('frontend::service.place_holder_number') }}">
                                    <span class="quote-error" id="error-number"></span>
                                </div>
                                <div class="form-group col-12 col-lg-6">
                                    <button type="submit" class="btn btn-warning w-100" id="btn-quote-submit">
                                        {{ __('frontend::service.button_submit_request') }}
                                    </button>
                                </div>
                                <div class="form-group col-12 d-none alert alert-dismissible fade show" id="quote-form-message" role="alert">
                                    <span id="quote-form-message-text"></span>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>       
            </div>
        </section>
        

        @include('frontend::shared.section.section-amenity', [
            'class' => 'section-7',
            'title' => __('frontend::service.section_7_title'),
            'description' => __('frontend::service.section_7_description'),
            'titleClass' => 'section-eyebrow section-eyebrow--gold',
            'tagHeading' => 'p',
        ])

        @include('frontend::shared.modal-service-detail')

        @include('frontend::shared.modal-info-popup', [
            'popupTitle'       => __('frontend::service.popup_success_title'),
            'popupDescription' => __('frontend::service.popup_success_description'),
        ])

        @include('frontend::shared.modal-info-popup', [
            'popupId'          => 'quoteInfoModal',
            'popupTitle'       => __('frontend::service.coming_soon_title'),
            'popupDescription' => __('frontend::service.coming_soon_text'),
            'popupIcon'        => 'fa-solid fa-circle-info',
        ])

        @include('frontend::shared.section.section-call-to-action', [
            'class' => 'section-6',
            'description' => $readyToSailDescription,
            'content' => $readyToSailContent,
            'buttons' => [[
                'label' => __('frontend::service.button_book_now'),
                'class' => 'btn-warning',
                'url' => route(Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode])
            ]]
        ])

        



    </div>
@endsection

@push('scripts')
    <script>    

        window.quoteMessages = {
            contact_name_required: "{{ __('frontend::service.validation_contact_name_required') }}",
            contact_name_max:      "{{ __('frontend::service.validation_contact_name_max') }}",
            phone_required:        "{{ __('frontend::service.validation_phone_required') }}",
            phone_max:             "{{ __('frontend::service.validation_phone_max') }}",
            phone_invalid:         "{{ __('frontend::service.validation_phone_invalid') }}",
            event_type_required:   "{{ __('frontend::service.validation_event_type_required') }}",
            number_required:       "{{ __('frontend::service.validation_number_required') }}",
            number_invalid:        "{{ __('frontend::service.validation_number_invalid') }}",
            success:               "{{ __('frontend::service.message_success') }}",
            error_generic:         "{{ __('frontend::service.message_error_generic') }}"
        };

        if (window.jQuery) {
            window.jQuery(function () {
                window.jQuery('.btn-coming-soon').on('click', function () {
                    window.jQuery('#quoteInfoModal').modal('show');
                });

                window.jQuery('.btn-info').on('click', function () {
                    window.jQuery('#quoteInfoModal').modal('show');
                });
            });
        }
    </script>
@endpush