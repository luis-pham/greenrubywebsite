@extends('frontend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');
    $cruiseDisplayName = FeUtils::formatGreenRubyMenuName($obj->name);
    $isGreenRuby2 = FeCruiseUtils::isGreenRuby2($obj->name ?? '');
    $vesselLabel = $isGreenRuby2
        ? __('frontend::cruiseDetail.hero.vessel_gr2')
        : __('frontend::cruiseDetail.hero.vessel_gr1');
    $shipHeroSub = $isGreenRuby2
        ? __('frontend::cruiseDetail.hero.sub_gr2')
        : __('frontend::cruiseDetail.hero.sub_gr1');
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
    $shipSpecs = FeCruiseUtils::getDisplayShipSpecs($isGreenRuby2);
    $statDescMap = [
        'length' => __('frontend::cruiseDetail.ship_specs.stat.length_desc'),
        'cabin' => __('frontend::cruiseDetail.ship_specs.stat.cabin_desc'),
        'guests' => __('frontend::cruiseDetail.ship_specs.stat.guests_desc'),
        'year' => $isGreenRuby2
            ? __('frontend::cruiseDetail.ship_specs.stat.year_desc_gr2')
            : __('frontend::cruiseDetail.ship_specs.stat.year_desc_gr1'),
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
                <ellipse cx="1200" cy="100" rx="320" ry="200" fill="none" stroke="#0c3d31" stroke-width="1"/>
                <ellipse cx="1200" cy="100" rx="240" ry="148" fill="none" stroke="#0c3d31" stroke-width="1"/>
                <ellipse cx="1200" cy="100" rx="160" ry="96" fill="none" stroke="#0c3d31" stroke-width="1"/>
                <ellipse cx="200" cy="600" rx="280" ry="180" fill="none" stroke="#0c3d31" stroke-width="1"/>
                <ellipse cx="200" cy="600" rx="200" ry="130" fill="none" stroke="#0c3d31" stroke-width="1"/>
            </svg>
            <div class="container position-relative">
                <div class="combined-header">
                    <p class="section-eyebrow section-eyebrow--gold">{{ __('frontend::cruiseDetail.ship_specs.eyebrow') }}</p>
                    <h2 class="combined-title">{!! __('frontend::cruiseDetail.ship_specs.title_html', ['name' => $specsVesselName]) !!}</h2>
                </div>
                <div class="grid-info ship-stats-grid">
                    <div class="item ship-stat-card">
                        <p class="ship-stat-label">{{ __('frontend::cruiseDetail.ship_specs.stat.length_label') }}</p>
                        <p class="length ship-stat-number">{{ $shipSpecs['length'] }}</p>
                        <p class="ship-stat-desc">{{ $statDescMap['length'] }}</p>
                    </div>
                    <div class="item ship-stat-card">
                        <p class="ship-stat-label">{{ __('frontend::cruiseDetail.ship_specs.stat.cabins_label') }}</p>
                        <p class="length ship-stat-number">{{ $shipSpecs['cabins'] }}</p>
                        <p class="ship-stat-desc">{{ $statDescMap['cabin'] }}</p>
                    </div>
                    <div class="item ship-stat-card">
                        <p class="ship-stat-label">{{ __('frontend::cruiseDetail.ship_specs.stat.guests_label') }}</p>
                        <p class="length ship-stat-number">{{ $shipSpecs['guests'] }}</p>
                        <p class="ship-stat-desc">{{ $statDescMap['guests'] }}</p>
                    </div>
                    <div class="item ship-stat-card">
                        <p class="ship-stat-label">{{ __('frontend::cruiseDetail.ship_specs.stat.year_label') }}</p>
                        <p class="length ship-stat-number">{{ $shipSpecs['year'] }}</p>
                        <p class="ship-stat-desc">{{ $statDescMap['year'] }}</p>
                    </div>
                </div>
                <div class="combined-divider">
                    <div class="combined-divider-line"></div>
                    <span class="combined-divider-text">{{ __('frontend::cruiseDetail.ship_specs.eco_divider') }}</span>
                    <div class="combined-divider-line"></div>
                </div>
                <div class="ship-eco-slide slide-1">
                    <div class="ship-eco-grid owl-carousel owl-theme">
                        <div class="ship-eco-card">
                            <div class="eco-card-num-row">
                                <div class="eco-card-stat">{{ __('frontend::cruiseDetail.ship_specs.eco.solar_stat') }}</div>
                            </div>
                            <div class="eco-card-stat-label">{{ __('frontend::cruiseDetail.ship_specs.eco.solar_stat_label') }}</div>
                            <div class="eco-card-divider"></div>
                            <div class="eco-card-icon">
                                @include('frontend::shared.eco-icon', ['variant' => 0])
                            </div>
                            <div class="eco-card-title">{{ __('frontend::cruiseDetail.ship_specs.eco.solar_title') }}</div>
                            <p class="eco-card-desc">{{ __('frontend::cruiseDetail.ship_specs.eco.solar_desc') }}</p>
                        </div>
                        <div class="ship-eco-card">
                            <div class="eco-card-num-row">
                                <div class="eco-card-stat">0</div>
                            </div>
                            <div class="eco-card-stat-label">{{ __('frontend::cruiseDetail.ship_specs.eco.discharge_label') }}</div>
                            <div class="eco-card-divider"></div>
                            <div class="eco-card-icon">
                                @include('frontend::shared.eco-icon', ['variant' => 3])
                            </div>
                            <div class="eco-card-title">{{ __('frontend::cruiseDetail.ship_specs.eco.wastewater_title') }}</div>
                            <p class="eco-card-desc">{{ __('frontend::cruiseDetail.ship_specs.eco.wastewater_desc') }}</p>
                        </div>
                        <div class="ship-eco-card">
                            <div class="eco-card-num-row">
                                <div class="eco-card-stat">−40%</div>
                            </div>
                            <div class="eco-card-stat-label">{{ __('frontend::cruiseDetail.ship_specs.eco.cooling_label') }}</div>
                            <div class="eco-card-divider"></div>
                            <div class="eco-card-icon">
                                @include('frontend::shared.eco-icon', ['variant' => 2])
                            </div>
                            <div class="eco-card-title">{{ __('frontend::cruiseDetail.ship_specs.eco.chiller_title') }}</div>
                            <p class="eco-card-desc">{{ __('frontend::cruiseDetail.ship_specs.eco.chiller_desc') }}</p>
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
            'isShowBookNow' => true,
            'cruiseId' => $obj->id,
        ])

        <section class="section-itinerary bg">
            <div class="container-fluid">
                <div class="container">
                    <p class="section-eyebrow section-eyebrow--gold">{{ __('frontend::cruiseDetail.section-itinerary.title') }}</p>
                    <p class="section-description font-heading">{!! __('frontend::cruiseDetail.section-itinerary.description') !!}</p>
                    @if ($groupItinerary->isNotEmpty())
                        <div class="slide-1 cruise-itinerary-slide">
                            <div class="list-itinerary-cruise cruise-itinerary-carousel owl-carousel owl-theme">
                                @foreach ($groupItinerary as $itinerary)
                                    @include('frontend::shared.section.partials.itinerary-card', [
                                        'itinerary' => $itinerary,
                                        'languageCode' => $languageCode,
                                    ])
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section
            class="section-cabin-and-service"
            id="onboard"
            style="background:#f8f5ef; padding:72px 0;">

            <div class="container">

                <div class="onboard-section-header">
                    <p class="section-eyebrow section-eyebrow--gold">{{ __('frontend::cruiseDetail.onboard.eyebrow') }}</p>
                    <h2 class="combined-title">{!! __('frontend::cruiseDetail.onboard.title_html', ['name' => $obj->name ?? 'Green Ruby']) !!}</h2>
                </div>

                <div class="onboard-main-tabs">
                    <button class="onboard-main-tab active" data-tab="experience">
                        {{ __('frontend::cruiseDetail.onboard.tab_experience') }}
                    </button>
                    <button class="onboard-main-tab" data-tab="services">
                        {{ __('frontend::cruiseDetail.onboard.tab_services') }}
                    </button>
                </div>

                <div id="onboard-tab-experience" class="onboard-tab-panel">
                    <div class="onboard-exp-layout">

                        <div class="onboard-exp-left">

                            @php
                                $grouped = $listOtherCabin->groupBy(fn($c) => $c->group->category_key ?? 'other');
                                $categoryLabels = [
                                    'dining'   => __('frontend::cruiseDetail.onboard.cat.dining'),
                                    'pools'    => __('frontend::cruiseDetail.onboard.cat.pools'),
                                    'wellness' => __('frontend::cruiseDetail.onboard.cat.wellness'),
                                    'events'   => __('frontend::cruiseDetail.onboard.cat.events'),
                                    'other'    => __('frontend::cruiseDetail.onboard.cat.other'),
                                ];
                                $categoryOrder = ['dining', 'pools', 'wellness', 'events', 'other'];
                                $firstItemDone = false;
                            @endphp

                            @foreach($categoryOrder as $catKey)
                                @php
                                    $items = $grouped->get($catKey, collect());
                                    $actItems = $catKey === 'wellness'
                                        ? (isset($listExpActivity)
                                            ? $listExpActivity->filter(fn($a) => $a->cruise_id !== null)
                                            : collect())
                                        : collect();
                                    $totalItems = $items->count() + $actItems->count();
                                @endphp

                                @if($totalItems > 0)
                                <div class="onboard-cat-group">
                                    <p class="onboard-cat-label">
                                        {{ $categoryLabels[$catKey] ?? $catKey }}
                                    </p>
                                    <div class="onboard-cat-items">

                                        @foreach($items as $item)
                                        <div
                                            class="onboard-cat-item{{ !$firstItemDone ? ' active' : '' }}"
                                            data-id="{{ $item->id }}"
                                            data-type="facility"
                                            data-title="{{ $item->name }}"
                                            data-desc="{{ $item->summary }}">
                                            <p class="onboard-item-name">{{ $item->name }}</p>
                                        </div>
                                        @php
                                            if (!$firstItemDone) {
                                                $firstItemDone = true;
                                            }
                                        @endphp
                                        @endforeach

                                        @foreach($actItems as $item)
                                        <div
                                            class="onboard-cat-item{{ !$firstItemDone ? ' active' : '' }}"
                                            data-id="{{ $item->id }}"
                                            data-type="activity"
                                            data-title="{{ $item->name }}"
                                            data-desc="{{ $item->summary }}">
                                            <p class="onboard-item-name">{{ $item->name }}</p>
                                        </div>
                                        @php
                                            if (!$firstItemDone) {
                                                $firstItemDone = true;
                                            }
                                        @endphp
                                        @endforeach

                                    </div>
                                </div>
                                @endif
                            @endforeach

                        </div>

                        @php
                            $firstFacility = $listOtherCabin->first();
                            $firstImgSrc = $firstFacility
                                ? FeUtils::getImageLink($firstFacility->image_link)
                                : '';
                        @endphp

                        <div class="onboard-exp-right">
                            <div class="onboard-main-img">
                                <img
                                    id="onboard-main-img-src"
                                    src="{{ $firstImgSrc }}"
                                    alt=""
                                    class="onboard-img-el"/>
                                <div class="onboard-img-overlay"></div>
                                <div class="onboard-img-counter">
                                    <span id="onboard-img-current">1</span>
                                    /
                                    <span id="onboard-img-total">1</span>
                                </div>
                                <div class="onboard-img-nav">
                                    <button class="onboard-img-btn" id="onboard-prev" type="button">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M18 15l-6-6-6 6"/>
                                        </svg>
                                    </button>
                                    <button class="onboard-img-btn" id="onboard-next" type="button">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M6 9l6 6 6-6"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="onboard-img-caption">
                                    <p class="onboard-cap-title" id="onboard-cap-title">
                                        {{ $listOtherCabin->first()?->name ?? '' }}
                                    </p>
                                    <p class="onboard-cap-desc" id="onboard-cap-desc">
                                        {{ $listOtherCabin->first()?->summary ?? '' }}
                                    </p>
                                </div>
                            </div>
                            <div class="onboard-thumbs" id="onboard-thumbs"></div>
                        </div>

                    </div>
                </div>

                <div id="onboard-tab-services" class="onboard-tab-panel" style="display:none;">
                    <div class="onboard-svc-grid">

                        <div class="onboard-svc-group">
                            <p class="onboard-svc-label">{{ __('frontend::cruiseDetail.onboard.included_label') }}</p>
                            <p class="onboard-svc-sublabel">{{ __('frontend::cruiseDetail.onboard.included_sublabel') }}</p>
                            <div class="onboard-svc-items">

                                @php
                                    $includedSvcKeys = ['welcome_drink', 'fine_dining', 'housekeeping', 'concierge', 'halal', 'safety'];
                                    $includedSvcSvg = [
                                        'welcome_drink' => '<path d="M18 8h1a4 4 0 010 8h-1"/><path d="M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/>',
                                        'fine_dining' => '<path d="M18 8h1a4 4 0 010 8h-1"/><path d="M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/>',
                                        'housekeeping' => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>',
                                        'concierge' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3"/>',
                                        'halal' => '<path d="M12 22s-8-4.5-8-11.8A8 8 0 0112 2a8 8 0 018 8.2c0 7.3-8 11.8-8 11.8z"/>',
                                        'safety' => '<path d="M12 22s-8-4.5-8-11.8A8 8 0 0112 2a8 8 0 018 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="2.5"/>',
                                    ];
                                @endphp

                                @foreach($includedSvcKeys as $svcKey)
                                @php $svcPrefix = 'frontend::cruiseDetail.onboard.services.' . $svcKey; @endphp
                                <div class="onboard-svc-item">
                                    <div class="onboard-svc-icon">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                            {!! $includedSvcSvg[$svcKey] !!}
                                        </svg>
                                    </div>
                                    <div class="onboard-svc-content">
                                        <p class="onboard-svc-name">{{ __($svcPrefix . '.name') }}</p>
                                        <p class="onboard-svc-desc">{{ __($svcPrefix . '.desc') }}</p>
                                        <span class="onboard-svc-badge onboard-svc-badge--free">{{ __('frontend::cruiseDetail.onboard.badge_complimentary') }}</span>
                                    </div>
                                </div>
                                @endforeach

                            </div>
                        </div>

                        <div class="onboard-svc-group">
                            <p class="onboard-svc-label">{{ __('frontend::cruiseDetail.onboard.suite_label') }}</p>
                            <p class="onboard-svc-sublabel">{{ __('frontend::cruiseDetail.onboard.suite_sublabel') }}</p>
                            <div class="onboard-svc-items">

                                @php
                                    $suiteSvcKeys = ['butler', 'private_dining', 'in_room_dining'];
                                    $suiteSvcSvg = [
                                        'butler' => '<circle cx="12" cy="7" r="4"/><path d="M4 21v-2a8 8 0 0116 0v2"/>',
                                        'private_dining' => '<path d="M18 8h1a4 4 0 010 8h-1"/><path d="M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/>',
                                        'in_room_dining' => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path d="M9 22V12h6v10"/>',
                                    ];
                                @endphp

                                @foreach($suiteSvcKeys as $svcKey)
                                @php $svcPrefix = 'frontend::cruiseDetail.onboard.services.' . $svcKey; @endphp
                                <div class="onboard-svc-item">
                                    <div class="onboard-svc-icon">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                            {!! $suiteSvcSvg[$svcKey] !!}
                                        </svg>
                                    </div>
                                    <div class="onboard-svc-content">
                                        <p class="onboard-svc-name">{{ __($svcPrefix . '.name') }}</p>
                                        <p class="onboard-svc-desc">{{ __($svcPrefix . '.desc') }}</p>
                                        <span class="onboard-svc-badge onboard-svc-badge--suite">{{ __($svcPrefix . '.badge') }}</span>
                                    </div>
                                </div>
                                @endforeach

                            </div>
                        </div>

                        <div class="onboard-svc-group">
                            <p class="onboard-svc-label">{{ __('frontend::cruiseDetail.onboard.paid_label') }}</p>
                            <p class="onboard-svc-sublabel">{{ __('frontend::cruiseDetail.onboard.paid_sublabel') }}</p>
                            <div class="onboard-svc-items">

                                @php
                                    $paidSvcKeys = ['spa', 'transfer', 'event', 'honeymoon'];
                                    $paidSvcSvg = [
                                        'spa' => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>',
                                        'transfer' => '<path d="M12 2C8 2 4 5.5 4 10c0 6 8 12 8 12s8-6 8-12c0-4.5-4-8-8-8z"/><circle cx="12" cy="10" r="2"/>',
                                        'event' => '<path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>',
                                        'honeymoon' => '<path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>',
                                    ];
                                @endphp

                                @foreach($paidSvcKeys as $svcKey)
                                @php $svcPrefix = 'frontend::cruiseDetail.onboard.services.' . $svcKey; @endphp
                                <div class="onboard-svc-item">
                                    <div class="onboard-svc-icon">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                            {!! $paidSvcSvg[$svcKey] !!}
                                        </svg>
                                    </div>
                                    <div class="onboard-svc-content">
                                        <p class="onboard-svc-name">{{ __($svcPrefix . '.name') }}</p>
                                        <p class="onboard-svc-desc">{{ __($svcPrefix . '.desc') }}</p>
                                        <span class="onboard-svc-badge onboard-svc-badge--paid">{{ __('frontend::cruiseDetail.onboard.badge_charged') }}</span>
                                    </div>
                                </div>
                                @endforeach

                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </section>

        @include('frontend::shared.section.section-vr-360',[
            'list' => $obj->listVr,
            'eyebrow' => __('frontend::cruiseDetail.section-vr-360.eyebrow'),
            'titleHtml' => __('frontend::cruiseDetail.section-vr-360.title_html'),
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
                    ['@type' => 'PropertyValue', 'name' => 'Capacity',     'value' => $shipSpecs['guests'] . ' guests'],
                    ['@type' => 'PropertyValue', 'name' => 'Total Floors', 'value' => (string) $obj->total_floor],
                    ['@type' => 'PropertyValue', 'name' => 'Length',       'value' => $shipSpecs['length'] . ' meters'],
                    ['@type' => 'PropertyValue', 'name' => 'Year Built',   'value' => (string) $shipSpecs['year']],
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
        window.langCode = @json($languageCode ?? '');
        let apiAppCabin = {
            getById: '{{ route(Utilities::getRouteName('frontend.api.cabin.getById'), ['languageCode' => $languageCode]) }}'
        };
        let apiAppService = {
            getById: "{{ route(Utilities::getRouteName('frontend.api.service.getById'), ['languageCode' => $languageCode]) }}"
        };
    </script>
    <script src="{{ asset('assets/frontend/js/modules/cruise/onboard.js') }}?v={{ filemtime(public_path('assets/frontend/js/modules/cruise/onboard.js')) }}" defer></script>
@endpush
