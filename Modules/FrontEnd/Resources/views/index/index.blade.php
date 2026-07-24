@extends('frontend::layouts.master')

@php
    $disableDefaultAppCss = true;
    $disableDefaultAppJs = true;
    $languageCode = Route::current()->parameter('languageCode');

    $listBay = FeCruiseUtils::getListBay();
    $listBayName = count($listBay) > 1 ? implode(' & ', $listBay) : $listBay[1];
    $listCruiseName = FeUtils::formatGreenRubyCruiseNames($listCruiseName);

    $listBanner = isset($pageConfig[PageConfigKeyConsts::HOMEPAGE_BANNER])
        ? $pageConfig[PageConfigKeyConsts::HOMEPAGE_BANNER]
        : [];

    for ($i = 0; $i < count($listBanner); $i++) {
        if ($i === 0) {
            $listBanner[$i]->title = __('frontend::homepage.hero_title');
        }

        $listBanner[$i]->listButton = [];

        $btn = new \stdClass();
        $btn->label = __('frontend::homepage.button_book_your_trip');
        $btn->class = 'btn-warning';
        $btn->url = route(Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode]);
        $listBanner[$i]->listButton[] = $btn;

        $btn = new \stdClass();
        $btn->label = __('frontend::homepage.button_discover_itinerary');
        $btn->class = 'btn-success';
        $btn->url = route(Utilities::getRouteName('frontend.itinerary.index'), ['languageCode' => $languageCode]);
        $listBanner[$i]->listButton[] = $btn;
    }

    // Preload hero image
    $heroPreloadDesktop = null;
    $heroPreloadMobile = null;
    if (!empty($listBanner)) {
        $firstBanner = $listBanner[0];
        $ext = strtolower(pathinfo($firstBanner->link ?? '', PATHINFO_EXTENSION));
        $videoExts = config('backend.fileTypeVideo', ['mp4', 'webm', 'ogg', 'mov']);
        if (!in_array($ext, $videoExts)) {
            $heroPreloadDesktop = FeUtils::getThumbnail([
                'link' => $firstBanner->link,
                'w' => 1920, 'h' => 848, 'q' => 80, 'cr' => 1,
            ]);
            $heroPreloadMobile = FeUtils::getThumbnail([
                'link' => $firstBanner->link,
                'w' => 560, 'h' => 420, 'q' => 50, 'cr' => 1,
            ]);
        }
    }

@endphp

@if ($heroPreloadDesktop)
@push('preload')
    <link rel="preload" as="image"
          href="{{ $heroPreloadMobile ?: $heroPreloadDesktop }}"
          imagesrcset="{{ $heroPreloadMobile }} 560w, {{ $heroPreloadDesktop }} 1920w"
          imagesizes="100vw"
          fetchpriority="high">
@endpush
@endif

@push('preload')
    {{-- Preload the font faces used above the fold. `crossorigin` is required
         so the preload can be reused by the CSS font request. --}}
    <link rel="preload"
          href="{{ asset('assets/frontend/dist/css/cormorant-garamond-300.woff2') }}"
          as="font"
          type="font/woff2"
          crossorigin>
    <link rel="preload"
          href="{{ asset('assets/frontend/dist/css/cormorant-garamond-300italic.woff2') }}"
          as="font"
          type="font/woff2"
          crossorigin>
    <link rel="preload"
          href="{{ asset('assets/frontend/dist/css/dm-sans-normal.woff2') }}"
          as="font"
          type="font/woff2"
          crossorigin>
    @if (app()->getLocale() === 'vi')
        <link rel="preload"
              href="{{ asset('assets/frontend/dist/css/cormorant-garamond-300-vietnamese.woff2') }}"
              as="font"
              type="font/woff2"
              crossorigin>
        <link rel="preload"
              href="{{ asset('assets/frontend/dist/css/cormorant-garamond-300italic-vietnamese.woff2') }}"
              as="font"
              type="font/woff2"
              crossorigin>
        <link rel="preload"
              href="{{ asset('assets/frontend/dist/css/dm-sans-normal-vietnamese.woff2') }}"
              as="font"
              type="font/woff2"
              crossorigin>
    @endif
@endpush

@push('styles')
    {{-- Blocking intentionally: applying the complete homepage stylesheet after
         first paint caused a full-body layout shift in Lighthouse. --}}
    <link href="{{ mix('assets/frontend/dist/css/home.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div id="home" data-deferred-js="{{ mix('assets/frontend/dist/js/home-deferred.js') }}">
        @include('frontend::shared.section.section-cover', [
            'class' => 'section-1',
            'list' => $listBanner,
            'imageConfig' => ['w' => 1920, 'h' => 848, 'q' => 80, 'cr' => 1],
            'imageConfigMobile' => ['w' => 560, 'h' => 420, 'q' => 50, 'cr' => 1],
            'heroEyebrow' => __('frontend::homepage.hero_eyebrow'),
            'tagHeading' => 'h1',
            'allowTitleHtml' => true,
        ])
        <section class="section-2 bg">
            <div class="container-fluid position-relative px-0">
                <div class="container">
                    <div class="info-bar position-absolute text-white">
                        <div class="info-bar-wrapper d-flex flex-wrap justify-content-between">
                            <div class="info-item">
                                <div class="position-relative">
                                    <div class="info-wrapper d-flex flex-nowrap justify-content-start align-items-center">
                                        <div class="info-left icon-wrap">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#C8A84B" stroke-width="1.25" stroke-linecap="round" aria-hidden="true">
                                                <path d="M3 17h18M5 17V9a2 2 0 012-2h10a2 2 0 012 2v8"/>
                                                <path d="M9 17V12h6v5"/>
                                                <path d="M12 7V5"/>
                                            </svg>
                                        </div>
                                        <div class="info-right d-flex flex-column">
                                            <span class="label">{{ __('frontend::homepage.fleet') }}</span>
                                            <span class="title font-weight-bold">{{ __('frontend::homepage.2_cruise_ships') }}</span>
                                            <span class="value">{{ $listCruiseName }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="position-relative">
                                    <div class="info-wrapper d-flex flex-nowrap justify-content-start align-items-center">
                                        <div class="info-left icon-wrap">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#C8A84B" stroke-width="1.25" stroke-linecap="round" aria-hidden="true">
                                                <path d="M12 2C8 2 4 5.5 4 10c0 6 8 12 8 12s8-6 8-12c0-4.5-4-8-8-8z"/>
                                                <circle cx="12" cy="10" r="2"/>
                                            </svg>
                                        </div>
                                        <div class="info-right d-flex flex-column">
                                            <span class="label">{{ __('frontend::homepage.destinations') }}</span>
                                            <span class="title font-weight-bold">{{ __('frontend::homepage.2_heritage_bay')}}</span>
                                            <span class="value">{{ __('frontend::homepage.bay' , ['name'=>$listBayName]) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="position-relative">
                                    <div class="info-wrapper d-flex flex-nowrap justify-content-start align-items-center">
                                        <div class="info-left icon-wrap">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#C8A84B" stroke-width="1.25" stroke-linecap="round" aria-hidden="true">
                                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                                <line x1="3" y1="9" x2="21" y2="9"/>
                                                <line x1="8" y1="2" x2="8" y2="6"/>
                                                <line x1="16" y1="2" x2="16" y2="6"/>
                                            </svg>
                                        </div>
                                        <div class="info-right d-flex flex-column">
                                            <span class="label">{{ __('frontend::homepage.journeys') }}</span>
                                            <span class="title font-weight-bold">{{ __('frontend::homepage.4_itineraries') }}</span>
                                            <span class="value">{{ __('frontend::homepage.info_bar_journeys_detail') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="position-relative">
                                    <div class="info-wrapper d-flex flex-nowrap justify-content-start align-items-center">
                                        <div class="info-left icon-wrap">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#C8A84B" stroke-width="1.25" stroke-linecap="round" aria-hidden="true">
                                                <path d="M12 3a6 6 0 016 6c0 4-6 10-6 10S6 13 6 9a6 6 0 016-6z"/>
                                            </svg>
                                        </div>
                                        <div class="info-right d-flex flex-column">
                                            <span class="label">{{ __('frontend::homepage.commitment') }}</span>
                                            <span class="title font-weight-bold">{{ __('frontend::homepage.eco-luxury') }}</span>
                                            <ul class="list-value">
                                                @foreach(__('frontend::homepage.hybrid') as $item)
                                                    <li class="value">{{ $item }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div id="home-below-fold">
            @include('frontend::index.partials.below-fold')
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ mix('assets/frontend/dist/js/home-core.js') }}" defer></script>
    @include('frontend::shared.structured-data-organization', [
        'url' => route(Utilities::getRouteName('frontend.index'), ['languageCode' => $languageCode])
    ])
@endpush
