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
    $aboutUsReadyToSailContent = isset($pageConfig[PageConfigKeyConsts::ABOUT_US_READY_TO_SAIL_CONTENT])
        ? $pageConfig[PageConfigKeyConsts::ABOUT_US_READY_TO_SAIL_CONTENT]
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
                    <p class="section-eyebrow section-eyebrow--gold">About Green Ruby Cruises</p>
                    <h1 class="title font-heading">Where Luxury Has <em>a Conscience.</em></h1>
                    <p class="description">{{ __('frontend::about.hero_subtitle') }}</p>
                </div>
            </div>
        </section>

        {{-- SECTION 2: STORY --}}
        <section class="section-2 bg about-story">
            <div class="container-fluid px-0">
                <div class="container about-story-grid">
                    <div class="about-story-copy">
                        <p class="section-eyebrow">Our Story</p>
                        @if ($aboutUsEcoTitle)
                            <p class="about-story-kicker">{{ $aboutUsEcoTitle }}</p>
                        @endif
                        @if ($aboutUsEcoDescription)
                            <h2 class="about-story-title">{!! $storyTitleHtml !!}</h2>
                        @endif
                        @if ($aboutUsEcoContent)
                            <div class="about-story-body">{!! $aboutUsEcoContent !!}</div>
                        @endif
                    </div>
                    @if ($aboutUsEcoFeaturedImage)
                        <div class="about-story-media">
                            <img
                                class="about-story-image"
                                src="{{ asset(FeUtils::getThumbnail(['link' => $aboutUsEcoFeaturedImage, 'w' => 1000, 'h' => 562])) }}"
                                alt="{{ $aboutUsEcoFeaturedImageAlt ?? $aboutUsEcoTitle }}"
                            />
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
                    <h2 class="about-eco-title">Technology That <em>Proves It.</em></h2>
                    @if (count($listSustainability ?? []) > 0)
                        <div class="about-eco-grid">
                            @foreach ($listSustainability as $sustainability)
                                <div class="about-eco-card">
                                    <p class="eco-card-num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} — {{ $sustainability->title }}</p>
                                    @include('frontend::shared.image-wrapper', [
                                        'link' => $sustainability->link,
                                        'alt' => $sustainability->title,
                                        'imageConfig' => ['w' => 332, 'h' => 410],
                                    ])
                                    <h3 class="about-eco-card-title">{{ $sustainability->title }}</h3>
                                    <p class="about-eco-card-desc">{{ $sustainability->description }}</p>
                                </div>
                            @endforeach
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
                            <h2 class="about-community-title">{!! $communityTitleHtml !!}</h2>
                        @endif
                    </div>
                    @if (count($listEnviroment ?? []) > 0)
                        <div class="about-community-rows">
                            @foreach ($listEnviroment as $item)
                                <div class="about-community-row">
                                    <img
                                        class="about-community-image"
                                        src="{{ asset(FeUtils::getThumbnail(['link' => $item->link, 'w' => 807, 'h' => 388])) }}"
                                        alt="{{ $item->title ?? '' }}"
                                        loading="lazy"
                                    />
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
                    <p class="section-eyebrow">Our Positive Impact</p>
                    @if ($aboutUsStatisticTitle)
                        <h2 class="about-impact-title">{{ $aboutUsStatisticTitle }}</h2>
                    @endif
                    @if ($aboutUsStatisticDescription)
                        <p class="about-impact-sub">{{ $aboutUsStatisticDescription }}</p>
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
                        <h3 class="about-cert-title">Verified <em>Standards</em></h3>
                        <p class="about-cert-sub">Credentials you can trust — not claims we invented.</p>
                    </div>
                    <div class="about-cert-badges">
                        <div class="about-cert-badge">
                            <div class="about-cert-dot"></div>
                            <span class="about-cert-text">Green Globe · In Progress</span>
                        </div>
                        <div class="about-cert-badge">
                            <div class="about-cert-dot"></div>
                            <span class="about-cert-text">EU Green Claims · Compliant</span>
                        </div>
                        <div class="about-cert-badge">
                            <div class="about-cert-dot"></div>
                            <span class="about-cert-text">GSTC · Recognised Standard</span>
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
                        <h2 class="about-cta-title">Be Part of <em>the Change.</em></h2>
                        <p class="about-cta-desc">{{ $aboutUsReadyToSailContent }}</p>
                        @if ($aboutUsReadyToSailDescription)
                            <p class="about-cta-kicker">{{ $aboutUsReadyToSailDescription }}</p>
                        @endif
                        <div class="about-cta-buttons list-button">
                            <div class="item">
                                <a href="{{ $itineraryUrl }}" class="btn-warning btn-rounded">{{ __('frontend::about.button_see_itineraries') }}</a>
                            </div>
                            <div class="item">
                                <a href="{{ $contactUrl }}" class="btn-success btn-rounded">{{ __('frontend::about.button_contact_us') }}</a>
                            </div>
                        </div>
                    </div>
                    @if ($ctaImageLink)
                        <div class="about-cta-media">
                            <img
                                class="about-cta-image"
                                src="{{ asset(FeUtils::getThumbnail(['link' => $ctaImageLink, 'w' => 900, 'h' => 600])) }}"
                                alt="{{ $aboutUsReadyToSailDescription ?: 'Green Ruby Cruises' }}"
                                loading="lazy"
                            />
                        </div>
                    @endif
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
