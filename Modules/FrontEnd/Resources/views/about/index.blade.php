@extends('frontend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');

    $listBanner = isset($pageConfig[PageConfigKeyConsts::ABOUT_US_BANNER])
        ? $pageConfig[PageConfigKeyConsts::ABOUT_US_BANNER]
        : [];

    $itineraryUrl = route(Utilities::getRouteName('frontend.itinerary.index'), ['languageCode' => $languageCode]);
    $sustainabilityUrl = route(Utilities::getRouteName('frontend.about.index'), ['languageCode' => $languageCode]) . '#sustainability';
    $contactUrl = route(Utilities::getRouteName('frontend.contact.index'), ['languageCode' => $languageCode]);

    $aboutUsEcoTitle = isset($pageConfig[PageConfigKeyConsts::ABOUT_US_ECO_TITLE])
        ? $pageConfig[PageConfigKeyConsts::ABOUT_US_ECO_TITLE]
        : '';
    $aboutUsEcoDescription = isset($pageConfig[PageConfigKeyConsts::ABOUT_US_ECO_DESCRIPTION])
        ? $pageConfig[PageConfigKeyConsts::ABOUT_US_ECO_DESCRIPTION]
        : '';
    $aboutUsEcoContent = isset($pageConfig[PageConfigKeyConsts::ABOUT_US_ECO_CONTENT])
        ? $pageConfig[PageConfigKeyConsts::ABOUT_US_ECO_CONTENT]
        : '';
    $aboutUsEcoFeaturedImage = isset($pageConfig[PageConfigKeyConsts::ABOUT_US_ECO_FEATURED_IMAGE])
        ? $pageConfig[PageConfigKeyConsts::ABOUT_US_ECO_FEATURED_IMAGE]
        : '';
    $listSustainability = isset($pageConfig[PageConfigKeyConsts::ABOUT_US_SUSTAINABILITY])
        ? $pageConfig[PageConfigKeyConsts::ABOUT_US_SUSTAINABILITY]
        : [];

    $aboutUsEnviromentTitle = isset($pageConfig[PageConfigKeyConsts::ABOUT_US_ENVIROMENT_TITLE])
        ? $pageConfig[PageConfigKeyConsts::ABOUT_US_ENVIROMENT_TITLE]
        : '';
    $aboutUsEnviromentDescription = isset($pageConfig[PageConfigKeyConsts::ABOUT_US_ENVIROMENT_DESCRIPTION])
        ? $pageConfig[PageConfigKeyConsts::ABOUT_US_ENVIROMENT_DESCRIPTION]
        : '';
    $listEnviroment = isset($pageConfig[PageConfigKeyConsts::ABOUT_US_ENVIROMENT_LIST])
        ? $pageConfig[PageConfigKeyConsts::ABOUT_US_ENVIROMENT_LIST]
        : [];

    $aboutUsStatisticTitle = isset($pageConfig[PageConfigKeyConsts::ABOUT_US_STATISTIC_TITLE])
        ? $pageConfig[PageConfigKeyConsts::ABOUT_US_STATISTIC_TITLE]
        : '';
    $aboutUsStatisticDescription = isset($pageConfig[PageConfigKeyConsts::ABOUT_US_STATISTIC_DESCRIPTION])
        ? $pageConfig[PageConfigKeyConsts::ABOUT_US_STATISTIC_DESCRIPTION]
        : '';
    $aboutUsStatisticWastewaterTreated = isset($pageConfig[PageConfigKeyConsts::ABOUT_US_STATISTIC_WASTEWATER_TREATED])
        ? $pageConfig[PageConfigKeyConsts::ABOUT_US_STATISTIC_WASTEWATER_TREATED]
        : null;
    $aboutUsStatisticReducedAnnually = isset($pageConfig[PageConfigKeyConsts::ABOUT_US_STATISTIC_REDUCED_ANNUALLY])
        ? $pageConfig[PageConfigKeyConsts::ABOUT_US_STATISTIC_REDUCED_ANNUALLY]
        : null;
    $aboutUsStatisticRenewableSolarPower = isset($pageConfig[PageConfigKeyConsts::ABOUT_US_STATISTIC_RENEWABLE_SOLAR_POWER])
        ? $pageConfig[PageConfigKeyConsts::ABOUT_US_STATISTIC_RENEWABLE_SOLAR_POWER]
        : null;

    $aboutUsPartnerTitle = isset($pageConfig[PageConfigKeyConsts::ABOUT_US_PARTNER_TITLE])
        ? $pageConfig[PageConfigKeyConsts::ABOUT_US_PARTNER_TITLE]
        : '';
    $aboutUsPartnerDescription = isset($pageConfig[PageConfigKeyConsts::ABOUT_US_PARTNER_DESCRIPTION])
        ? $pageConfig[PageConfigKeyConsts::ABOUT_US_PARTNER_DESCRIPTION]
        : '';
    $listPartner = isset($pageConfig[PageConfigKeyConsts::ABOUT_US_PARTNER_LIST])
        ? $pageConfig[PageConfigKeyConsts::ABOUT_US_PARTNER_LIST]
        : [];

    $aboutUsReadyToSailDescription = isset($pageConfig[PageConfigKeyConsts::ABOUT_US_READY_TO_SAIL_DESCRIPTION])
        ? $pageConfig[PageConfigKeyConsts::ABOUT_US_READY_TO_SAIL_DESCRIPTION]
        : '';
    $ctaImageLink = $aboutUsEcoFeaturedImage;
    if (!$ctaImageLink && count($listBanner) > 0) {
        $ctaImageLink = $listBanner[0]->link ?? '';
    }

    $storyTitleHtml = e($aboutUsEcoDescription);
    if ($storyTitleHtml) {
        $storyTitleHtml = preg_replace('/a Greener Tomorrow/i', '<em>a Greener Tomorrow</em>', $storyTitleHtml);
    }

    $communityTitleHtml = e($aboutUsEnviromentDescription);
    if ($communityTitleHtml) {
        $communityTitleHtml = preg_replace('/cherish\.?/i', '<em>cherish.</em>', $communityTitleHtml);
    }
@endphp

@section('content')
    <div id="about">
        {{-- SECTION 1: HERO --}}
        <section class="section-1 about-hero section-cover position-relative">
            <svg class="about-topo" viewBox="0 0 1440 320" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                <ellipse cx="1200" cy="80" rx="320" ry="200" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="1200" cy="80" rx="240" ry="145" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="200" cy="280" rx="280" ry="180" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="200" cy="280" rx="200" ry="125" fill="none" stroke="white" stroke-width="1"/>
            </svg>
            <div class="container hero-content">
                <div class="main-info mx-auto text-white text-center">
                    <p class="section-eyebrow section-eyebrow--gold">{{ __('frontend::about.hero_eyebrow') }}</p>
                    <h1 class="title font-heading">{!! __('frontend::about.hero_title_html') !!}</h1>
                    <p class="description">{{ __('frontend::about.hero_subtitle') }}</p>
                </div>
            </div>
        </section>

        {{-- SECTION 2: STORY --}}
        <section class="section-2 bg about-story">
            <div class="container-fluid px-0">
                <div class="container about-story-grid">
                    <div class="about-story-copy">
                        <p class="section-eyebrow">{{ __('frontend::about.story_eyebrow') }}</p>
                        @if ($aboutUsEcoTitle)
                            <p class="about-story-kicker">{{ $aboutUsEcoTitle }}</p>
                        @endif
                        @if ($aboutUsEcoDescription)
                            <h2 class="about-story-title">{!! safe_html($storyTitleHtml) !!}</h2>
                        @endif
                        @if ($aboutUsEcoContent)
                            <div class="about-story-body">{!! safe_html($aboutUsEcoContent) !!}</div>
                        @endif
                    </div>
                    @if (count($listStoryImages ?? []) > 0)
                        <div class="about-story-media">
                            <div class="slide-1 about-story-slide">
                                <div class="about-story-carousel owl-carousel owl-theme">
                                    @foreach ($listStoryImages as $storyImage)
                                        <div class="item">
                                            @include('frontend::shared.image-wrapper', [
                                                'link' => $storyImage->link,
                                                'alt' => $storyImage->alt ?: $aboutUsEcoTitle,
                                                'ratio' => '16-9',
                                                'imageConfig' => ['w' => 960, 'h' => 540],
                                            ])
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- SECTION 3: ECO TECH --}}
        <section id="sustainability" class="section-3 bg about-eco">
            <svg class="about-topo" viewBox="0 0 1440 320" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                <ellipse cx="1200" cy="80" rx="320" ry="200" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="1200" cy="80" rx="240" ry="145" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="200" cy="280" rx="280" ry="180" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="200" cy="280" rx="200" ry="125" fill="none" stroke="white" stroke-width="1"/>
            </svg>
            <div class="container-fluid px-0">
                <div class="container about-eco-panel">
                    <p class="section-eyebrow section-eyebrow--gold">{{ __('frontend::about.section_3_title') }}</p>
                    <h2 class="about-eco-title">{!! __('frontend::about.eco_title_html') !!}</h2>
                    @if (count($listSustainability ?? []) > 0)
                        <div class="about-eco-grid-desktop">
                            @foreach ($listSustainability as $sustainability)
                                <div class="about-eco-card h-100">
                                    <p class="eco-card-num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} — {{ $sustainability->title }}</p>
                                    @include('frontend::shared.image-wrapper', [
                                        'link' => $sustainability->link,
                                        'alt' => $sustainability->title,
                                        'ratio' => '3-2',
                                        'imageConfig' => ['w' => 498, 'h' => 332],
                                    ])
                                    <h3 class="about-eco-card-title">{{ $sustainability->title }}</h3>
                                    <p class="about-eco-card-desc">{{ $sustainability->description }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="slide-1 d-lg-none">
                            <div class="about-eco-grid-mobile owl-carousel owl-theme">
                                @foreach ($listSustainability as $sustainability)
                                    <div class="item">
                                        <div class="about-eco-card h-100">
                                            <p class="eco-card-num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} — {{ $sustainability->title }}</p>
                                            @include('frontend::shared.image-wrapper', [
                                                'link' => $sustainability->link,
                                                'alt' => $sustainability->title,
                                                'ratio' => '3-2',
                                                'imageConfig' => ['w' => 498, 'h' => 332],
                                            ])
                                            <h3 class="about-eco-card-title">{{ $sustainability->title }}</h3>
                                            <p class="about-eco-card-desc">{{ $sustainability->description }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- SECTION 4: COMMUNITY --}}
        <section class="section-4 bg about-community">
            <div class="container-fluid px-0">
                <div class="container about-community-panel">
                    <div class="about-community-header">
                        @if ($aboutUsEnviromentTitle)
                            <p class="section-eyebrow">{{ $aboutUsEnviromentTitle }}</p>
                        @endif
                        @if ($aboutUsEnviromentDescription)
                            <h2 class="about-community-title">{!! safe_html($communityTitleHtml) !!}</h2>
                        @endif
                    </div>
                    @if (count($listEnviroment ?? []) > 0)
                        <div class="about-community-rows">
                            @foreach ($listEnviroment as $item)
                                <div class="about-community-row">
                                    <div class="about-community-media">
                                        <img
                                            class="about-community-image"
                                            src="{{ asset(FeUtils::getThumbnail(['link' => $item->link, 'w' => 807, 'h' => 388])) }}"
                                            alt="{{ $item->title ?? '' }}"
                                            loading="lazy"
                                        />
                                    </div>
                                    <div class="about-community-content">
                                        <h3 class="about-community-item-title">{{ $item->title ?? '' }}</h3>
                                        <div class="about-community-item-desc">{!! nl2br(e($item->description ?? '')) !!}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- SECTION 5: IMPACT STATS --}}
        <section class="section-5 bg bg-azure about-impact">
            <div class="container-fluid px-0">
                <div class="container about-impact-panel">
                    <p class="section-eyebrow">{{ __('frontend::about.impact_eyebrow') }}</p>
                    @if ($aboutUsStatisticTitle)
                        <h2 class="about-impact-title">{{ $aboutUsStatisticTitle }}</h2>
                    @endif
                    <div class="about-impact-grid">
                        @if ($aboutUsStatisticWastewaterTreated)
                            <div class="about-impact-stat">
                                <p class="about-impact-stat-value">{{ $aboutUsStatisticWastewaterTreated }}%</p>
                                <p class="about-impact-stat-label">{{ __('frontend::about.wastewater_treated') }}</p>
                            </div>
                        @endif
                        @if ($aboutUsStatisticReducedAnnually)
                            <div class="about-impact-stat">
                                <p class="about-impact-stat-value">{{ $aboutUsStatisticReducedAnnually }}+</p>
                                <p class="about-impact-stat-label">{{ __('frontend::about.tons_of_co2_reduced_annually') }}</p>
                            </div>
                        @endif
                        @if ($aboutUsStatisticRenewableSolarPower)
                            <div class="about-impact-stat">
                                <p class="about-impact-stat-value">{{ $aboutUsStatisticRenewableSolarPower }}kW</p>
                                <p class="about-impact-stat-label">{{ __('frontend::about.renewable_solar_power') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- SECTION 7: CERTIFICATION BAR (hidden) --}}
        <section id="green-globe" class="about-cert-bar d-none" hidden aria-hidden="true">
            <div class="container-fluid px-0">
                <div class="container about-cert-panel">
                    <div class="about-cert-left">
                        <h3 class="about-cert-title">{!! __('frontend::about.cert_title_html') !!}</h3>
                        <p class="about-cert-sub">{{ __('frontend::about.cert_sub') }}</p>
                    </div>
                    <div class="about-cert-badges">
                        <div class="about-cert-badge">
                            <div class="about-cert-dot"></div>
                            <span class="about-cert-text">{{ __('frontend::about.cert_green_globe') }}</span>
                        </div>
                        <div class="about-cert-badge">
                            <div class="about-cert-dot"></div>
                            <span class="about-cert-text">{{ __('frontend::about.cert_eu_green') }}</span>
                        </div>
                        <div class="about-cert-badge">
                            <div class="about-cert-dot"></div>
                            <span class="about-cert-text">{{ __('frontend::about.cert_gstc') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- SECTION 8: CTA --}}
        <section class="section-7 about-cta bg">
            <div class="container-fluid px-0">
                <div class="container about-cta-grid">
                    <div class="about-cta-copy">
                        <h2 class="about-cta-title">{!! __('frontend::about.cta_title_html') !!}</h2>
                        <p class="about-cta-desc">{{ __('frontend::about.ready_to_sail_content') }}</p>
                        @if ($aboutUsReadyToSailDescription)
                            <p class="about-cta-kicker">{{ $aboutUsReadyToSailDescription }}</p>
                        @endif
                        <div class="about-cta-buttons list-button d-flex flex-wrap justify-content-center">
                            <div class="item">
                                <a href="{{ $itineraryUrl }}" class="btn-warning btn-rounded">{{ __('frontend::about.button_see_itineraries') }}</a>
                            </div>
                            <div class="item">
                                <a href="{{ $contactUrl }}" class="btn-success btn-rounded">{{ __('frontend::about.button_contact_us') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @include('frontend::shared.structured-data-organization', [
        'url' => $url
    ])
@endpush
