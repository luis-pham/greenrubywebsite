@extends('frontend::layouts.master')
@php
$languageCode = Route::current()->parameter('languageCode');
$url = route(
    Utilities::getRouteName('frontend.experience.show'),
    [
        'languageCode' => $languageCode,
        'slug' => Utilities::convertToAlias($obj->slug ?? $obj->name),
        'id' => $obj->id,
    ]
);
$listBanner = [];
$listBanner[0] = new stdClass();
$listBanner[0]->title = $obj->name;
$listBanner[0]->link = asset(FeUtils::getThumbnail(['link' => $obj->cover_link, 'w' => 1920, 'h' => 400]));
$listBanner[0]->description = $obj->summary;
$pageConfig = isset($pageConfig) ? $pageConfig : [];
$readyToSailDescription = isset($pageConfig[PageConfigKeyConsts::EXPERIENCE_READY_TO_SAIL_DESCRIPTION])
    ? $pageConfig[PageConfigKeyConsts::EXPERIENCE_READY_TO_SAIL_DESCRIPTION]
    : '';
$readyToSailContent = isset($pageConfig[PageConfigKeyConsts::EXPERIENCE_READY_TO_SAIL_CONTENT])
    ? $pageConfig[PageConfigKeyConsts::EXPERIENCE_READY_TO_SAIL_CONTENT]
    : '';
@endphp

@section('content')
    <div id="experience-detail">
        @include('frontend::shared.section.section-cover', [
            'class' => 'section-1 section-cover-sm',
            'list' => $listBanner,
            'tagHeading' => 'h1',
        ])

        <section class="section-2">
            <div class="container-fluid">
                <div class="container">
                    <div class="row no-gutters">
                        <div class="section-content-wrapper col-12 col-lg-8">
                            <div class="section-content-wrapper-inner-1">
                                <p class="section-eyebrow section-eyebrow--gold">{{ __('frontend::experienceDetail.section_2_title') }}</p>
                                <p class="section-description font-heading">{{ __('frontend::experienceDetail.section_2_description') }}</p>
                                <div class="section-content d-flex">
                                    <div class="item-duration d-flex align-items-center w-100">
                                        <i class="fas fa-clock"></i>
                                        <div class="item-duration-content">
                                            <p class="title">{{ __('frontend::experienceDetail.section_duration') }}</p>
                                            <p class="content">{{ $obj->duration }} {{ __('frontend::experienceDetail.time_minutes') }}</p>
                                        </div>
                                    </div>
                                    <div class="item-time d-flex align-items-center w-100">
                                        <i class="fa-solid fa-calendar"></i>
                                        <div class="item-time-content ml-2">
                                            <p class="title">{{ __('frontend::experienceDetail.section_time_of_event') }}</p>
                                            <p class="content">{{ Utilities::formatDisplayTime($obj->start_time) }} - {{ Utilities::formatDisplayTime($obj->end_time) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="note">
                                    <p>{{$obj->summary}}</p>
                                    <p>{{$obj->content}}</p>
                                </div>                      
                            </div>
                            <div class="section-content-wrapper-inner-2">
                                <p class="section-eyebrow section-eyebrow--gold text-left">{{ __('frontend::experienceDetail.section_3_title') }}</p>
                                <div class="section-content-gallery">
                                    @if(!empty($galleryImages) && count($galleryImages) > 0)
                                  
                                      {{-- DESKTOP: grid --}}
                                      <div class="row d-none d-lg-flex">
                                        @foreach($galleryImages as $img)
                                          @php
                                            $thumbnail = property_exists($img, 'thumbnail') ? $img->thumbnail : null;
                                            $thumbnailFull = Utilities::getFileLink(!$thumbnail ? $img->link : $thumbnail);
                                            $fullLink = Utilities::getFileLink($img->link);
                                          @endphp
                                          <div class="col-6 col-md-4 mb-3">
                                            <a href="{{ $fullLink }}" data-fancybox="experience-gallery">
                                              <img src="{{ $thumbnailFull }}" alt="{{ $img->title ?? $obj->name }}" class="img-fluid rounded" />
                                            </a>
                                          </div>
                                        @endforeach
                                      </div>
                                  
                                      {{-- MOBILE: owl-carousel --}}
                                      <div class="slide-1 d-block d-lg-none">
                                        <div class="list-gallery owl-carousel owl-theme">
                                          @foreach($galleryImages as $img)
                                            @php
                                              $thumbnail = property_exists($img, 'thumbnail') ? $img->thumbnail : null;
                                              $thumbnailFull = Utilities::getFileLink(!$thumbnail ? $img->link : $thumbnail);
                                              $fullLink = Utilities::getFileLink($img->link);
                                            @endphp
                                            <div class="item">
                                              <a href="{{ $fullLink }}" data-fancybox="experience-gallery">
                                                <img src="{{ $thumbnailFull }}" alt="{{ $img->title ?? $obj->name }}" class="img-fluid rounded" />
                                              </a>
                                            </div>
                                          @endforeach
                                        </div>
                                      </div>
                                  
                                    @endif
                                  </div>
                            </div>
                            <div class="section-content-wrapper-inner-3">
                                
                                <p class="section-eyebrow section-eyebrow--gold text-left">{{ __('frontend::experienceDetail.section_4_title') }}</p>
                                <div class="section-content-notes">
                                    @php
                                        $notes = json_decode($obj->note, true);
                                    @endphp
                                    @if(!empty($notes))
                                        @foreach($notes as $note)
                                            <div class="item-wrapper">
                                                <div class="item-note d-flex align-items-center w-100">
                                                    <i class="fa-solid fa-check"></i>
                                                    <p>{{ $note }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="section-content-wrapper-inner-4">
                                <p class="section-eyebrow section-eyebrow--gold text-left">{{ __('frontend::experienceDetail.section_5_title') }}</p>
                                <div class="section-content-suitable">
                                    <ul>
                                        @foreach($suitableAudiences as $suitableAudience)
                                            <li>{{ $suitableAudience }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="section-content-wrapper-button">
                                <div class="button-group d-flex">
                                    <a href="{{ route(Utilities::getRouteName('frontend.experience.index'), ['languageCode' => $languageCode]) }}" class="button-primary">
                                        <i class="fa-solid fa-arrow-left"></i>
                                         <span class="ml-2">{{ __('frontend::experienceDetail.button_explore_activity') }}</span>
                                    </a>
                                    <div class="button-secondary d-flex">
                                        <span>{{ __('frontend::experienceDetail.button_share_this_article') }}</span>
                                       
                                        <div class="social d-flex align-items-center">
                                            <a href="https://www.facebook.com/sharer.php?u={{ $url }}" class="d-block text-reset" target="_blank" rel="nofollow">
                                                <div class="icon icon-facebook align-self-center">
                                                    <div class="icon-wrapper d-flex align-items-center justify-content-center rounded-circle">
                                                        <i class="fa-brands fa-facebook-f"></i>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $url }}" class="d-block text-reset" target="_blank" rel="nofollow">
                                                <div class="icon icon-linkedin align-self-center">
                                                    <div class="icon-wrapper d-flex align-items-center justify-content-center rounded-circle">
                                                        <i class="fa-brands fa-linkedin-in"></i>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="mailto:?subject={{ $obj->name }}&body={{ $url }}" class="d-block text-reset" target="_blank" rel="nofollow">
                                                <div class="icon icon-email align-self-center">
                                                    <div class="icon-wrapper d-flex align-items-center justify-content-center rounded-circle">
                                                        <i class="fa-solid fa-envelope"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="section-content-exp col-12 col-lg-4">
                            <div class="section-content-exp-wrapper">
                                <p class="section-eyebrow section-eyebrow--gold text-left">{{ __('frontend::experienceDetail.section_6_title') }}</p>
                                @foreach ($listExperience as $experience)
                                    <div class="item-exp-wrapper">
                                        <div class="item-exp d-flex ">
                                            <a href="{{ route(Utilities::getRouteName('frontend.experience.show'), ['languageCode' => $languageCode, 'slug' => Utilities::convertToAlias($experience->name), 'id' => $experience->id]) }}" class="item-exp-image">
                                                <img src="{{ Utilities::getFileLink($experience->image_link) }}" alt="{{ $experience->name }}">
                                            </a>
                                            <a href="{{ route(Utilities::getRouteName('frontend.experience.show'), ['languageCode' => $languageCode, 'slug' => Utilities::convertToAlias($experience->name), 'id' => $experience->id]) }}" class="item-exp-content d-flex flex-column">
                                                <p class="title">{{ $experience->name }}</p>
                                                <ul>
                                                    @php
                                                    $notes = json_decode($experience->note, true);
                                                    @endphp
                                                    @if(!empty($notes))
                                                    <li>
                                                        {{ is_array($notes) ? implode(', ', $notes) : '' }}
                                                    </li>
                                                    @endif
                                                    @if(!empty($experience->duration))
                                                    <li>
                                                        <span>{{ $experience->duration }} minutes</span>
                                                    </li>
                                                    @endif
                                                    
                                                </ul>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="section-content-exp-wrapper-inner-2">
                                <div class="section-content-exp-wrapper-inner-2-content">
                                    <p class="section-eyebrow section-eyebrow--gold text-left">{{ __('frontend::experienceDetail.section_7_title') }}</p>
                                    <p class="description">{{ __('frontend::experienceDetail.section_7_description') }}</p>
                                    <div class="group-button d-flex flex-column justify-content-between">
                                        <a href="{{ route(Utilities::getRouteName('frontend.itinerary.index'), ['languageCode' => $languageCode]) }}" class="button-primary">
                                            {{ __('frontend::experienceDetail.button_discovery_the_journey') }}
                                        </a>
                                        <a href="javascript:;" class="button-secondary btn-chat-with-ai">
                                            {{ __('frontend::experienceDetail.button_ask_ai') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
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

        @include('frontend::shared.modal-info-popup', [
            'popupTitle'       => __('frontend::experienceDetail.coming_soon_title'),
            'popupDescription' => __('frontend::experienceDetail.coming_soon_text'),
            'popupIcon'        => 'fa-solid fa-circle-info',
        ])
    </div>
@endsection

@push('scripts')
<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Event",
        "name": "{!! html_entity_decode($obj->name, ENT_QUOTES, 'UTF-8') !!}",
        "description": "{!! html_entity_decode($obj->summary, ENT_QUOTES, 'UTF-8') !!}",
        "startDate": "{{ date('Y-m-d\TH:i:s', strtotime($obj->start_time)) }}",
        "endDate": "{{ date('Y-m-d\TH:i:s', strtotime($obj->end_time)) }}",
        "image": "{{ asset(FeUtils::getImageLink($obj->cover_link)) }}",
        "eventStatus": "https://schema.org/EventScheduled",
        "location": {
            "@type": "Place",
            "name": "Ha Long Bay",
            "address": {
                "@type": "PostalAddress",
                "addressLocality": "Quang Ninh",
                "addressCountry": "VN"
            }
        },
        "organizer": {
            "@type": "Organization",
            "name": "Green Ruby",
            "url": "{{ url('/') }}"
        }
        
    }
    </script>
    <script>
        $('.btn-coming-soon').on('click', function () {
            $('#quoteSuccessModal').modal('show');
        });
    </script>
@endpush