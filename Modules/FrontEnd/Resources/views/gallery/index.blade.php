@extends('frontend::layouts.master')
@php
    $languageCode = Route::current()->parameter('languageCode');
    $slug = Route::current()->parameter('slug');

    $listCallToActionBtn = [];

    $btn = [];
    $btn['label'] = __('frontend::common.book_now');
    $btn['class'] = 'btn-warning';
    $btn['url'] = route(\Modules\BackEnd\Helpers\Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode]);
    $listCallToActionBtn[] = $btn;

    $btn = [];
    $btn['label'] = __('frontend::gallery.section-cover.see-itineraries');
    $btn['class'] = 'btn-success';
    $btn['url'] = route(\Modules\BackEnd\Helpers\Utilities::getRouteName('frontend.itinerary.index'), ['languageCode' => $languageCode]);
    $listCallToActionBtn[] = $btn;

    $obj = $paginated->items();
    $totalPage = $paginated->lastPage();
    $currentPage = (int) Request::route('page') ?: 1;

    $regularGalleries = collect($obj)->filter(fn($item) => !$item->is_360)->values()->toArray();
    $vrGalleries = collect($obj)->filter(fn($item) => $item->is_360)->values()->toArray();

    $paginationBaseUrl = $slug
        ? route(Utilities::getRouteName('frontend.gallery.category'), ['languageCode' => $languageCode, 'slug' => $slug])
        : route(Utilities::getRouteName('frontend.gallery.index'), ['languageCode' => $languageCode]);

    $loadMoreUrl = null;
    if ($currentPage < $totalPage) {
        $pagePrefix = '/' . strtolower(__('frontend::common.page'));
        $loadMoreUrl = asset(Utilities::setQueryStringToUrl($paginationBaseUrl . $pagePrefix . '-' . ($currentPage + 1), Request::all()));
    }
@endphp
@section('content')
    <div id="gallery">
        {{-- SECTION 1: HERO --}}
        <section class="gallery-hero section-cover position-relative">
            <svg class="gallery-topo" viewBox="0 0 1440 280" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                <ellipse cx="1200" cy="60" rx="320" ry="180" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="1200" cy="60" rx="240" ry="130" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="200" cy="240" rx="280" ry="160" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="200" cy="240" rx="200" ry="115" fill="none" stroke="white" stroke-width="1"/>
            </svg>
            <div class="container hero-content">
                <div class="main-info mx-auto text-white text-center">
                    <div class="gallery-hero-eyebrow">
                        <span class="gallery-eyebrow-line"></span>
                        Visual Journey
                        <span class="gallery-eyebrow-line"></span>
                    </div>
                    <h1 class="title font-heading">Life Aboard <em>Green Ruby.</em></h1>
                    <p class="description">Discover moments aboard and beyond — in photos, films, and 360° views of Ha Long &amp; Lan Ha Bay.</p>
                </div>
            </div>
        </section>

        {{-- SECTION 2: FILTER + GRID --}}
        <section class="section-grid-gallery">
            @if(count($galleryFilters) > 1)
                <div class="gallery-filter-sticky">
                    <div class="container-fluid px-0">
                        <div class="gallery-filter-inner">
                            <div class="container">
                                <div class="gallery-filter-bar list-filter">
                                    <a href="{{ route(Utilities::getRouteName('frontend.gallery.index'), ['languageCode' => $languageCode]) }}" class="item gallery-filter-tab {{ !$slug ? 'active' : '' }}">All</a>
                                    @foreach($galleryFilters as $slugKey => $name)
                                        <a
                                            href="{{ route(\Modules\BackEnd\Helpers\Utilities::getRouteName('frontend.gallery.category'), ['languageCode' => $languageCode, 'slug' => $slugKey]) }}"
                                            class="item gallery-filter-tab {{ $slug && $slugKey === $slug ? 'active' : '' }}"
                                        >{{ $name }}</a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="gallery-wrapper container-fluid px-0">
                <div class="container">
                    <div class="slide-1">
                        <div class="grid-gallery gallery-grid-layout">
                            @foreach($regularGalleries as $media)
                                <div class="item gallery-item position-relative">
                                    <div class="gallery-item-wrap">
                                        <a class="gallery-image-wrapper" href="{{ $media->link }}" data-fancybox="gallery" data-caption="{{ $media->name }}">
                                            <img src="{{ $media->thumbnail ?? $media->link }}" alt="{{ $media->name }}"/>
                                        </a>
                                        <div class="gallery-item-overlay">
                                            <span class="gallery-item-caption">{{ $media->name }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @if ($loadMoreUrl)
                        <div class="gallery-load-more-wrap">
                            <a href="{{ $loadMoreUrl }}" class="gallery-load-more">Load More Photos</a>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- SECTION 3: VIDEO / 360° --}}
        @if(count($vrGalleries) > 0)
            <section class="gallery-video section-vr-360">
                <svg class="gallery-topo" viewBox="0 0 1440 280" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                    <ellipse cx="1200" cy="60" rx="320" ry="180" fill="none" stroke="white" stroke-width="1"/>
                    <ellipse cx="1200" cy="60" rx="240" ry="130" fill="none" stroke="white" stroke-width="1"/>
                    <ellipse cx="200" cy="240" rx="280" ry="160" fill="none" stroke="white" stroke-width="1"/>
                    <ellipse cx="200" cy="240" rx="200" ry="115" fill="none" stroke="white" stroke-width="1"/>
                </svg>
                <div class="container-fluid px-0">
                    <div class="container gallery-video-panel">
                        <p class="gallery-section-eyebrow gallery-eyebrow-gold">Immersive Experiences</p>
                        <h2 class="gallery-video-title">Step Aboard <em>Virtually.</em></h2>
                        <div class="gallery-video-grid">
                            @foreach($vrGalleries as $item)
                                <div class="gallery-video-item item">
                                    <div class="gallery-video-thumb">
                                        <video
                                            class="video-js vjs-default-skin"
                                            src="{{ $item->link }}"
                                            id="vr-player-{{ $item->id }}"
                                            preload="auto"
                                        ></video>
                                        <div class="video-play-btn" aria-hidden="true">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="#C8A84B">
                                                <polygon points="4,2 14,8 4,14"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="gallery-video-label">
                                        <p class="video-tag">360° Experience</p>
                                        <p class="gallery-video-item-title">{{ $item->name }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- SECTION 4: SIGNATURE MOMENTS --}}
        <section class="gallery-memories section-experience">
            <div class="container-fluid px-0">
                <div class="container gallery-memories-panel">
                    <p class="gallery-section-eyebrow">Signature Moments</p>
                    <h2 class="gallery-memories-title">From Sunrise Calm <em>to Sunset Glow.</em></h2>
                    <div class="gallery-memories-grid list-experience">
                        @foreach($listExperience as $e)
                            <div class="item gallery-memory-card memory-card">
                                <a href="{{ $e->url }}" class="gallery-memory-image-link">
                                    <img
                                        class="gallery-memory-image"
                                        alt="{{ $e->name }}"
                                        src="{{ \Modules\FrontEnd\Helpers\FeUtils::getImageLink($e->image_link) }}"
                                        loading="lazy"
                                    />
                                </a>
                                <div class="gallery-memory-content memory-content">
                                    <p class="memory-tag">Experience</p>
                                    <a href="{{ $e->url }}">
                                        <p class="gallery-memory-title">{{ $e->name }}</p>
                                    </a>
                                    <p class="gallery-memory-desc">{{ $e->summary }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        @include('frontend::shared.section.section-call-to-action', [
            'description' => __('frontend::gallery.section-call-to-action.description'),
            'content' => __('frontend::gallery.section-call-to-action.content'),
            'buttons' => $listCallToActionBtn,
        ])
    </div>
@endsection
