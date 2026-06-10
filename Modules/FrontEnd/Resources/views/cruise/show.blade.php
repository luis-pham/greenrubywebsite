@extends('frontend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');
    $cruiseDisplayName = FeUtils::formatGreenRubyMenuName($obj->name);
    $isGreenRuby2 = str_contains((string) ($obj->name ?? ''), '2');
    $vesselLabel = $isGreenRuby2 ? 'Vessel 02 · Lan Ha Bay' : 'Vessel 01 · Ha Long Bay';
    $shipHeroSub = $isGreenRuby2
        ? 'Where Ha Long ends, Lan Ha begins.'
        : 'Where Ha Long Bay begins.';
    $allowTitleHtml = false;
    if (preg_match('/^(Green Ruby)\s+(\d+)$/i', $cruiseDisplayName, $titleMatches)) {
        $cruiseTitleHtml = $titleMatches[1] . ' <em>' . $titleMatches[2] . '</em>';
        $allowTitleHtml = true;
    } else {
        $cruiseTitleHtml = $cruiseDisplayName;
    }

    $listBanner = [];
    $listButton = [];
    $listBtnCallToAction = [];

    $btn = new stdClass();
    $btn->label = __('frontend::cruiseDetail.section-cover.btn-book');
    $btn->url = route(\Modules\BackEnd\Helpers\Utilities::getRouteName('frontend.booking'),['cruise_id' => $obj->id,'languageCode' => $languageCode]);
    $btn->class = 'btn-warning';
    $listButton[] = $btn;

    $btn = new stdClass();
    $btn->label = __('frontend::cruiseDetail.section-cover.btn-watch');
    $btn->url = route(\Modules\BackEnd\Helpers\Utilities::getRouteName('frontend.itinerary.index'),['languageCode' => $languageCode]);
    $btn->class = 'btn-success';
    $listButton[] = $btn;

    $banner = new stdClass();
    $banner->title = $cruiseTitleHtml;
    $banner->description = $obj->description_design;
    $banner->link = $obj->cover_link;
    $banner->listButton = $listButton;
    $listBanner[] = $banner;

    $btn = [];
    $btn['label'] = __('frontend::common.book_now');
    $btn['url'] = route(\Modules\BackEnd\Helpers\Utilities::getRouteName('frontend.booking'),['cruise_id' => $obj->id,'languageCode' => $languageCode]);
    $btn['class'] = 'btn-warning';
    $listBtnCallToAction[] = $btn;

    $obj->listVr = $obj->galleryImages->filter(fn($video) => $video->is_360)->map(function($video){
        $video->link = FeUtils::getImageLink($video->link);
        return $video;
    })->slice(0,1);

    $listAccommodationCabin = $listCabin->filter(fn($cabin) => collect(config('backend.listAccommodationSlug'))->contains($cabin->slug))->values();
    $listAccommodationCabinId = $listAccommodationCabin->map(fn($c) => $c->id);
    $listOtherCabin = $listCabin->filter(fn($cabin) => !$listAccommodationCabinId->contains($cabin->id));
    $specsVesselName = $isGreenRuby2 ? 'Green Ruby 2' : 'Green Ruby 1';
    $statDescMap = [
        'length' => 'One of the longest eco-luxury vessels on Ha Long Bay',
        'cabin' => 'Including 4 cabin types from 38m² to 120m²',
        'guests' => 'Intimate capacity for a private bay experience',
        'year' => $isGreenRuby2
            ? 'Launching 2027 on Lan Ha Bay, Cát Bà Biosphere Reserve'
            : 'Launching October 2026 on UNESCO World Heritage waters',
    ];
@endphp

@section('content')
    <div id="cruise-detail">
        @include('frontend::shared.section.section-cover',[
            'list' => $listBanner,
            'tagHeading' => 'h1',
            'imageConfig' => ['w' => 1920, 'h' => 848],
            'cruiseHero' => true,
            'allowTitleHtml' => $allowTitleHtml,
            'vesselLabel' => $vesselLabel,
            'shipHeroSub' => $shipHeroSub,
        ])

        <section class="section-info ship-specs-section">
            <svg class="ship-specs-topo" viewBox="0 0 1440 700" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                <ellipse cx="1200" cy="100" rx="320" ry="200" fill="none" stroke="#0D2B1A" stroke-width="1"/>
                <ellipse cx="1200" cy="100" rx="240" ry="148" fill="none" stroke="#0D2B1A" stroke-width="1"/>
                <ellipse cx="1200" cy="100" rx="160" ry="96" fill="none" stroke="#0D2B1A" stroke-width="1"/>
                <ellipse cx="200" cy="600" rx="280" ry="180" fill="none" stroke="#0D2B1A" stroke-width="1"/>
                <ellipse cx="200" cy="600" rx="200" ry="130" fill="none" stroke="#0D2B1A" stroke-width="1"/>
            </svg>
            <div class="container position-relative">
                <div class="combined-header">
                    <div class="combined-eyebrow">
                        <span class="combined-ey-line"></span>
                        Ship Specifications & Eco Technology
                    </div>
                    <h2 class="combined-title">
                        The Numbers Behind
                        <em>{{ $specsVesselName }}</em>
                    </h2>
                </div>
                <div class="grid-info ship-stats-grid">
                    <div class="item ship-stat-card">
                        <p class="ship-stat-label">Length</p>
                        <p class="length ship-stat-number">{{ $obj->dimension_length }}</p>
                        <p class="ship-stat-desc">{{ $statDescMap['length'] }}</p>
                    </div>
                    <div class="item ship-stat-card">
                        <p class="ship-stat-label">Private Cabins</p>
                        <p class="length ship-stat-number">46</p>
                        <p class="ship-stat-desc">{{ $statDescMap['cabin'] }}</p>
                    </div>
                    <div class="item ship-stat-card">
                        <p class="ship-stat-label">Max Guests</p>
                        <p class="length ship-stat-number">{{ $obj->capacity }}</p>
                        <p class="ship-stat-desc">{{ $statDescMap['guests'] }}</p>
                    </div>
                    <div class="item ship-stat-card">
                        <p class="ship-stat-label">Launch Year</p>
                        <p class="length ship-stat-number">{{ $obj->year_built }}</p>
                        <p class="ship-stat-desc">{{ $statDescMap['year'] }}</p>
                    </div>
                </div>
                <div class="combined-divider">
                    <div class="combined-divider-line"></div>
                    <span class="combined-divider-text">Eco Technology</span>
                    <div class="combined-divider-line"></div>
                </div>
                <div class="ship-eco-slide slide-1">
                    <div class="ship-eco-grid owl-carousel owl-theme">
                        <div class="ship-eco-card">
                            <div class="eco-card-num-row">
                                <div class="eco-card-stat">−40%</div>
                            </div>
                            <div class="eco-card-stat-label">CO₂ vs diesel fleet</div>
                            <div class="eco-card-divider"></div>
                            <div class="eco-card-icon">
                                @include('frontend::shared.eco-icon', ['variant' => 0])
                            </div>
                            <div class="eco-card-title">Solar Power System</div>
                            <p class="eco-card-desc">Rooftop solar panels generate clean energy for lighting, water heating, and onboard systems.</p>
                        </div>
                        <div class="ship-eco-card">
                            <div class="eco-card-num-row">
                                <div class="eco-card-stat">0</div>
                            </div>
                            <div class="eco-card-stat-label">untreated discharge</div>
                            <div class="eco-card-divider"></div>
                            <div class="eco-card-icon">
                                @include('frontend::shared.eco-icon', ['variant' => 3])
                            </div>
                            <div class="eco-card-title">Wastewater Treatment</div>
                            <p class="eco-card-desc">Advanced onboard system ensures zero untreated discharge into the bay ecosystem.</p>
                        </div>
                        <div class="ship-eco-card">
                            <div class="eco-card-num-row">
                                <div class="eco-card-stat">−40%</div>
                            </div>
                            <div class="eco-card-stat-label">cooling energy saved</div>
                            <div class="eco-card-divider"></div>
                            <div class="eco-card-icon">
                                @include('frontend::shared.eco-icon', ['variant' => 2])
                            </div>
                            <div class="eco-card-title">Seawater Chiller</div>
                            <p class="eco-card-desc">Deep cold seawater replaces compressors for cabin cooling — unique to Green Ruby vessels.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @include('frontend::shared.section.section-cabin',[
            'eyebrow' => __('frontend::cruiseDetail.section-cabin.eyebrow'),
            'titleHtml' => __('frontend::cruiseDetail.section-cabin.title_html'),
            'list' => $listAccommodationCabin,
            'suitesGrid' => true,
            'isShowBookNow' => false
        ])

        <section class="section-itinerary bg">
            <div class="container-fluid">
                 <div class="container">
                     <h2 class="section-title">{{__('frontend::cruiseDetail.section-itinerary.title')}}</h2>
                     <p class="section-description font-heading">{{__('frontend::cruiseDetail.section-itinerary.description')}}</p>
                     <div class="list-filter">
                         @foreach($listDurationFilter as $idx => $duration)
                             <a href="javascript:" class="item {{$idx === 0 ? 'active' : ''}}" data-duration="{{$duration}}">
                                 {{\Modules\FrontEnd\Helpers\FeCruiseUtils::formatDisplayDurationName($duration)}}
                             </a>
                         @endforeach
                     </div>
                     @foreach($groupItinerary as $itinerary)
                         <div class="itinerary-detail {{ $loop->index !== 0 ? 'd-none' : '' }}" data-duration="{{$itinerary->duration}}">
                             <div class="body">
                                 <p class="name">{{$itinerary->name}}</p>
                                 <div class="image-wrapper position-relative">
                                     <img src="{{FeUtils::getImageLink($itinerary->cover_link)}}" alt="{{$itinerary->name}}" class="position-absolute w-100 h-100"/>
                                 </div>
                                 <div class="list-destination-outer">
                                     <div class="list-destination-inner">
                                         @php
                                             $destinations = json_decode($itinerary->destination);
                                         @endphp
                                         @foreach($destinations as $d)
                                             <span class="item">{{$d}}</span>
                                         @endforeach
                                     </div>
                                 </div>
                                 <div class="list-activity-container">
                                     <p class="label">{{__('frontend::cruiseDetail.section-itinerary.list-activity-container.key-features')}}</p>
                                     <div class="grid-activity">
                                         @foreach($itinerary->itineraryActivities as $a)
                                             <div class="item"><i class="fas fa-check "></i>{{$a->name}}</div>
                                         @endforeach
                                     </div>
                                 </div>
                             </div>
                             <div class="footer">
                                 <div class="d-flex align-items-center align-items-md-baseline justify-content-center flex-column flex-md-row" style="gap:1.5rem">
                                     <p class="mb-0">
                                         {{__('frontend::cruiseDetail.from')}}<b class="price"> {{FeUtils::formatDisplayCurrency($itinerary->min_price)}}/ </b>{{__('frontend::cruiseDetail.person')}}
                                     </p>
                                     <a
                                         class="btn btn-warning"
                                         href="{{   route(\Modules\BackEnd\Helpers\Utilities::getRouteName('frontend.booking'),['itinerary_id' => $itinerary->id,'languageCode' => $languageCode]) }}"
                                     >
                                         {{__('frontend::common.book_now')}}
                                         <i class="fas fa-calendar-check ml-2"></i>
                                     </a>
                                 </div>
                             </div>
                         </div>
                     @endforeach
                 </div>
            </div>
        </section>

        <section class="section-cabin-and-service bg">
            <div class="container-fluid p-0">
                <div class="row no-gutters">
                    <div class="col-12 col-lg-6 block-1">
                        <div class="cabin-container accordion-item">
                            <div class="header">
                                <span class="title">{{__('frontend::cruiseDetail.section-cabin-and-service.cabin-container-title')}}</span>
                                <div class="icon-wrapper">
                                    <i class="icon fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="body">
                                <div class="item-grid grid-cabin">
                                    @foreach($listOtherCabin as $cabin)
                                        <div class="item {{$loop->index === 0 ? 'active-1 active-2' : ''}}" data-id="{{$cabin->id}}">
                                            <span class="name">{{$cabin->name}}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="cabin-gallery-container position-relative d-lg-none"></div>
                            </div>
                        </div>
                        <div class="service-container accordion-item item-collapse">
                            <div class="header">
                                <span class="title">{{__('frontend::cruiseDetail.section-cabin-and-service.service-container-title')}}</span>
                                <div class="icon-wrapper">
                                    <i class="icon fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="body">
                                <div class="item-grid grid-service">
                                    @foreach($listInclusiveService as $service)
                                        <div class="item {{$loop->index === 0 ? 'active-1' : ''}}" data-id="{{$service->id}}">
                                            <span class="name">{{$service->name}}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="service-gallery-container d-lg-none"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6 block-2 d-none d-lg-block">
                        <div class="cabin-gallery-container service-gallery-container"></div>
                    </div>
                </div>
            </div>
        </section>

        @include('frontend::shared.section.section-vr-360',[
            'list' => $obj->listVr,
            'title' => __('frontend::cruiseDetail.section-vr-360.title'),
            'description' => __('frontend::cruiseDetail.section-vr-360.description')
        ])

        @include('frontend::shared.section.section-testimonial',[
            'list' => $listTestimonial
        ])

        @include('frontend::shared.section.section-call-to-action',[
            'description' => __('frontend::cruiseDetail.section-call-to-action.description'),
            'content' => __('frontend::cruiseDetail.section-call-to-action.content'),
            'buttons' => $listBtnCallToAction
        ])

    </div>
@endsection

@push('scripts')
    <script type="application/ld+json">
        {!!
            json_encode(array_merge([
                '@context'    => 'https://schema.org',
                '@type'       => 'Product',
                'name'        => $cruiseDisplayName,
                'description' => $obj->summary,
                'image'       => asset(FeUtils::getImageLink($obj->image_link)),
                'brand'       => [
                    '@type' => 'Brand',
                    'name'  => 'Green Ruby',
                ],
                'additionalProperty' => [
                    ['@type' => 'PropertyValue', 'name' => 'Capacity',     'value' => $obj->capacity . ' guests'],
                    ['@type' => 'PropertyValue', 'name' => 'Total Floors', 'value' => (string) $obj->total_floor],
                    ['@type' => 'PropertyValue', 'name' => 'Length',       'value' => $obj->dimension_length . ' meters'],
                    ['@type' => 'PropertyValue', 'name' => 'Year Built',   'value' => (string) $obj->year_built],
                ]
            ],
            $obj->star_rating ? [
                'aggregateRating' => [
                    '@type'       => 'AggregateRating',
                    'ratingValue' => (string) $obj->star_rating,
                    'bestRating'  => '5',
                    'worstRating' => '1',
                    'ratingCount' => (string) ($obj->review_count ?? 1),
                ]
            ] : []
            ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        !!}
    </script>
    <script type="text/javascript">
        let apiAppCabin = {
            getById: '{{ route(Utilities::getRouteName('frontend.api.cabin.getById'), ['languageCode' => $languageCode]) }}'
        };
        let apiAppService = {
            getById: "{{ route(Utilities::getRouteName('frontend.api.service.getById'), ['languageCode' => $languageCode]) }}"
        };
    </script>
@endpush
