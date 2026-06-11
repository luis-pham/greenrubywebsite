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

        <section
            class="section-cabin-and-service"
            id="onboard"
            style="background:#f2ede4; padding:72px 0;">

            <div class="container">

                <div style="margin-bottom:28px;">
                    <p class="section-title" style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                        <span style="width:20px; height:0.5px; background:#C8A84B; opacity:0.5; display:inline-block;"></span>
                        What Awaits You
                    </p>
                    <h2 style="font-family:var(--font-display); font-size:var(--text-2xl); font-weight:300; color:var(--color-forest); line-height:1.1; letter-spacing:-0.01em;">
                        Onboard
                        <em style="font-style:italic; color:var(--color-gold);">
                            {{ $obj->name ?? 'Green Ruby' }}
                        </em>
                    </h2>
                </div>

                <div class="onboard-main-tabs">
                    <button class="onboard-main-tab active" data-tab="experience">
                        Onboard Experience
                    </button>
                    <button class="onboard-main-tab" data-tab="services">
                        Services
                    </button>
                </div>

                <div id="onboard-tab-experience" class="onboard-tab-panel">
                    <div class="onboard-exp-layout">

                        <div class="onboard-exp-left">

                            @php
                                $grouped = $listOtherCabin->groupBy(fn($c) => $c->group->category_key ?? 'other');
                                $categoryLabels = [
                                    'dining'   => 'Dining & Social',
                                    'pools'    => 'Pools & Outdoors',
                                    'wellness' => 'Wellness & Activities',
                                    'events'   => 'Events',
                                    'other'    => 'More',
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
                                            <p class="onboard-item-desc">{{ Str::limit($item->summary, 45) }}</p>
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
                                            <p class="onboard-item-desc">{{ Str::limit($item->summary, 45) }}</p>
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
                            <p class="onboard-svc-label">Always Included</p>
                            <p class="onboard-svc-sublabel">Complimentary for all guests</p>
                            <div class="onboard-svc-items">

                                @php
                                    $includedSvcs = [
                                        ['name' => 'Welcome Drink', 'desc' => 'Refreshing drink & cold towel on arrival', 'svg' => '<path d="M18 8h1a4 4 0 010 8h-1"/><path d="M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/>'],
                                        ['name' => 'Fine Dining', 'desc' => 'Breakfast, lunch & dinner included', 'svg' => '<path d="M18 8h1a4 4 0 010 8h-1"/><path d="M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/>'],
                                        ['name' => 'Daily Housekeeping', 'desc' => 'Cabin cleaning & evening turndown', 'svg' => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>'],
                                        ['name' => '24h Concierge', 'desc' => 'Guest assistance throughout the journey', 'svg' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3"/>'],
                                        ['name' => 'Halal Kitchen', 'desc' => 'Dedicated Halal meals available', 'svg' => '<path d="M12 22s-8-4.5-8-11.8A8 8 0 0112 2a8 8 0 018 8.2c0 7.3-8 11.8-8 11.8z"/>'],
                                        ['name' => 'Safety & Security', 'desc' => '24h security & full safety systems', 'svg' => '<path d="M12 22s-8-4.5-8-11.8A8 8 0 0112 2a8 8 0 018 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="2.5"/>'],
                                    ];
                                @endphp

                                @foreach($includedSvcs as $s)
                                <div class="onboard-svc-item">
                                    <div class="onboard-svc-icon">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                            {!! $s['svg'] !!}
                                        </svg>
                                    </div>
                                    <div class="onboard-svc-content">
                                        <p class="onboard-svc-name">{{ $s['name'] }}</p>
                                        <p class="onboard-svc-desc">{{ $s['desc'] }}</p>
                                        <span class="onboard-svc-badge onboard-svc-badge--free">Complimentary</span>
                                    </div>
                                </div>
                                @endforeach

                            </div>
                        </div>

                        <div class="onboard-svc-group">
                            <p class="onboard-svc-label">Suite Exclusive</p>
                            <p class="onboard-svc-sublabel">Complimentary for suite guests</p>
                            <div class="onboard-svc-items">

                                @php
                                    $suiteSvcs = [
                                        ['name' => 'Butler Service', 'desc' => 'Personal assistant throughout your stay', 'badge' => 'Royal Romance + Imperial', 'svg' => '<circle cx="12" cy="7" r="4"/><path d="M4 21v-2a8 8 0 0116 0v2"/>'],
                                        ['name' => 'Private Dining', 'desc' => 'Exclusive dining for couples & families', 'badge' => 'Royal Romance + Imperial', 'svg' => '<path d="M18 8h1a4 4 0 010 8h-1"/><path d="M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/>'],
                                        ['name' => 'In-room Dining', 'desc' => 'Meals served directly to your suite', 'badge' => 'Imperial Suite only', 'svg' => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path d="M9 22V12h6v10"/>'],
                                    ];
                                @endphp

                                @foreach($suiteSvcs as $s)
                                <div class="onboard-svc-item">
                                    <div class="onboard-svc-icon">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                            {!! $s['svg'] !!}
                                        </svg>
                                    </div>
                                    <div class="onboard-svc-content">
                                        <p class="onboard-svc-name">{{ $s['name'] }}</p>
                                        <p class="onboard-svc-desc">{{ $s['desc'] }}</p>
                                        <span class="onboard-svc-badge onboard-svc-badge--suite">{{ $s['badge'] }}</span>
                                    </div>
                                </div>
                                @endforeach

                            </div>
                        </div>

                        <div class="onboard-svc-group">
                            <p class="onboard-svc-label">On Request</p>
                            <p class="onboard-svc-sublabel">Available at additional charge</p>
                            <div class="onboard-svc-items">

                                @php
                                    $paidSvcs = [
                                        ['name' => 'Spa & Wellness', 'desc' => 'Massage and wellness treatments', 'svg' => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>'],
                                        ['name' => 'Transfer Service', 'desc' => 'Airport, hotel & terminal transfers', 'svg' => '<path d="M12 2C8 2 4 5.5 4 10c0 6 8 12 8 12s8-6 8-12c0-4.5-4-8-8-8z"/><circle cx="12" cy="10" r="2"/>'],
                                        ['name' => 'Event & Celebration', 'desc' => 'Birthdays, anniversaries, proposals', 'svg' => '<path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>'],
                                        ['name' => 'Honeymoon Decoration', 'desc' => 'Romantic cabin decoration packages', 'svg' => '<path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>'],
                                    ];
                                @endphp

                                @foreach($paidSvcs as $s)
                                <div class="onboard-svc-item">
                                    <div class="onboard-svc-icon">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                            {!! $s['svg'] !!}
                                        </svg>
                                    </div>
                                    <div class="onboard-svc-content">
                                        <p class="onboard-svc-name">{{ $s['name'] }}</p>
                                        <p class="onboard-svc-desc">{{ $s['desc'] }}</p>
                                        <span class="onboard-svc-badge onboard-svc-badge--paid">Charged separately</span>
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

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/modules/cruise/onboard.css') }}">
@endpush

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
        window.langCode = @json($languageCode ?? '');
        let apiAppCabin = {
            getById: '{{ route(Utilities::getRouteName('frontend.api.cabin.getById'), ['languageCode' => $languageCode]) }}'
        };
        let apiAppService = {
            getById: "{{ route(Utilities::getRouteName('frontend.api.service.getById'), ['languageCode' => $languageCode]) }}"
        };
    </script>
    <script src="{{ asset('assets/frontend/js/modules/cruise/onboard.js') }}"></script>
@endpush
