@php
    $languageCode = $languageCode ?? Route::current()->parameter('languageCode');

    $listCruiseItinerary = isset($pageConfig[PageConfigKeyConsts::HOMEPAGE_CRUISE_ITINERARY])
        ? $pageConfig[PageConfigKeyConsts::HOMEPAGE_CRUISE_ITINERARY]
        : [];

    $listCruise = isset($pageConfig[PageConfigKeyConsts::HOMEPAGE_CRUISE])
        ? $pageConfig[PageConfigKeyConsts::HOMEPAGE_CRUISE]
        : [];
    $listCabin = isset($pageConfig[PageConfigKeyConsts::HOMEPAGE_CABIN])
        ? $pageConfig[PageConfigKeyConsts::HOMEPAGE_CABIN]
        : [];

    $listService = isset($pageConfig[PageConfigKeyConsts::HOMEPAGE_SERVICE])
        ? $pageConfig[PageConfigKeyConsts::HOMEPAGE_SERVICE]
        : [];

    $listFaq = isset($pageConfig[PageConfigKeyConsts::HOMEPAGE_FAQ])
        ? $pageConfig[PageConfigKeyConsts::HOMEPAGE_FAQ]
        : [];

    $listExpActivity = isset($pageConfig[PageConfigKeyConsts::HOMEPAGE_EXP_ACTIVITY])
        ? $pageConfig[PageConfigKeyConsts::HOMEPAGE_EXP_ACTIVITY]
        : [];

    $listExpActivityFilter = [];
    for ($i = 0; $i < count($listExpActivity); $i++) {
        if (!isset($listExpActivityFilter[$listExpActivity[$i]->group_id])) {
            $listExpActivityFilter[$listExpActivity[$i]->group_id] = $listExpActivity[$i]->group_name;
        }
    }

    $readyToSailDescription = isset($pageConfig[PageConfigKeyConsts::HOMEPAGE_READY_TO_SAIL_DESCRIPTION])
        ? $pageConfig[PageConfigKeyConsts::HOMEPAGE_READY_TO_SAIL_DESCRIPTION]
        : '';

    $guestRating = isset($pageConfig[PageConfigKeyConsts::HOMEPAGE_GUEST_RATING])
        ? $pageConfig[PageConfigKeyConsts::HOMEPAGE_GUEST_RATING]
        : '';

    $wouldRecommend = isset($pageConfig[PageConfigKeyConsts::HOMEPAGE_WOULD_RECOMMEND])
        ? $pageConfig[PageConfigKeyConsts::HOMEPAGE_WOULD_RECOMMEND]
        : '';

    $happyGuests = isset($pageConfig[PageConfigKeyConsts::HOMEPAGE_HAPPY_GUESTS])
        ? $pageConfig[PageConfigKeyConsts::HOMEPAGE_HAPPY_GUESTS]
        : '';

    $support = isset($pageConfig[PageConfigKeyConsts::HOMEPAGE_SUPPORT])
        ? $pageConfig[PageConfigKeyConsts::HOMEPAGE_SUPPORT]
        : '';

    $listAward = isset($pageConfig[PageConfigKeyConsts::HOMEPAGE_AWARD])
        ? $pageConfig[PageConfigKeyConsts::HOMEPAGE_AWARD]
        : [];
@endphp
        <section class="section-3 bg">
            <div class="container-fluid position-relative px-0">
                <div class="container">
                    <p class="section-eyebrow section-eyebrow--gold">{{ __('frontend::homepage.section_3_eyebrow') }}</p>
                    <h2 class="section-description font-heading">{!! __('frontend::homepage.section_3_heading') !!}</h2>
                    @php
                        $ecoCards = __('frontend::homepage.eco_cards');
                    @endphp
                    <div class="slide-1">
                        <div class="list-item owl-carousel owl-theme">
                            @for ($i = 0; $i < count($ecoCards); $i++)
                                <div class="item d-flex h-100 text-center">
                                    <div class="item-wrapper eco-card d-flex flex-column w-100 bg-white">
                                        <div class="eco-icon-wrap">
                                            @if ($i === 0)
                                                <svg width="56" height="56" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path d="M24 12C24 12 16 19 16 25C16 29.4 19.6 33 24 33C28.4 33 32 29.4 32 25C32 19 24 12 24 12Z" fill="rgba(200,168,75,0.12)" stroke="#C8A84B" stroke-width="1.5" stroke-linejoin="round"/>
                                                    <path d="M20 27C20 27 21.5 30 24 30C26.5 30 28 27 28 27" stroke="#0c3d31" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                                                    <line x1="24" y1="17" x2="24" y2="23" stroke="#0c3d31" stroke-width="1" stroke-linecap="round" opacity="0.5"/>
                                                    <line x1="21" y1="20" x2="27" y2="20" stroke="#0c3d31" stroke-width="1" stroke-linecap="round" opacity="0.5"/>
                                                </svg>
                                            @elseif ($i === 1)
                                                <svg width="56" height="56" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path d="M24 10C24 10 14 20 14 27C14 32.5 18.5 37 24 37C29.5 37 34 32.5 34 27C34 20 24 10 24 10Z" fill="rgba(200,168,75,0.12)" stroke="#C8A84B" stroke-width="1.5"/>
                                                    <path d="M18 30C18 30 20.5 34 24 34C27.5 34 30 30 30 30" stroke="#0c3d31" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                                                </svg>
                                            @elseif ($i === 2)
                                                <svg width="56" height="56" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <rect x="13" y="18" width="22" height="14" rx="1" fill="rgba(200,168,75,0.12)" stroke="#C8A84B" stroke-width="1.5"/>
                                                    <line x1="19" y1="18" x2="19" y2="32" stroke="#C8A84B" stroke-width="0.75" opacity="0.4"/>
                                                    <line x1="24" y1="18" x2="24" y2="32" stroke="#C8A84B" stroke-width="0.75" opacity="0.4"/>
                                                    <line x1="29" y1="18" x2="29" y2="32" stroke="#C8A84B" stroke-width="0.75" opacity="0.4"/>
                                                    <path d="M24 32L24 37M21 37L27 37" stroke="#0c3d31" stroke-width="1.25" stroke-linecap="round"/>
                                                    <path d="M10 25L13 25M35 25L38 25" stroke="#0c3d31" stroke-width="1.25" stroke-linecap="round" opacity="0.5"/>
                                                </svg>
                                            @else
                                                <svg width="56" height="56" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <circle cx="24" cy="30" r="8" fill="rgba(200,168,75,0.12)" stroke="#C8A84B" stroke-width="1.5"/>
                                                    <path d="M22 30L23.5 31.5L26.5 28.5" stroke="#C8A84B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M14 22C14 22 16 14 24 13C32 12 35 18 35 18" stroke="#0c3d31" stroke-width="1.25" fill="none" stroke-linecap="round" opacity="0.6"/>
                                                    <path d="M35 18L32 17M35 18L34 21" stroke="#0c3d31" stroke-width="1.25" stroke-linecap="round" opacity="0.6"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <h3 class="title mb-2 font-weight-bold">{{ $ecoCards[$i]['title'] }}</h3>
                                        <p class="description mb-0 text-break">{{ $ecoCards[$i]['description'] }}</p>
                                        <p class="eco-stat-number">{{ $ecoCards[$i]['stat_number'] }}</p>
                                        <p class="eco-stat-label">{{ $ecoCards[$i]['stat_label'] }}</p>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @include('frontend::shared.section.section-itinerary', [
            'class' => 'section-4',
            'title' => __('frontend::homepage.section_4_title'),
            'subTitle' => __('frontend::homepage.section_4_description'),
            'list' => $listCruiseItinerary,
            'tagHeading' => 'p',
            'titleClass' => 'section-eyebrow section-eyebrow--gold',
            'hideAllFilter' => true,
            'defaultBay' => 1,
            'itineraryImageConfig' => ['w' => 600, 'h' => 400],
            'viewAllUrl' => route(Utilities::getRouteName('frontend.itinerary.index'), ['languageCode' => $languageCode]),
        ])
        <section id="section-cruise" class="section-5 p-0">
            <div class="container-fluid px-0">
                @if (count($listCruise) > 0)
                    @for ($i = 0; $i < count($listCruise); $i++)
                        @php
                            $cruiseEyebrows = [
                                __('frontend::homepage.section_5_vessel_01_eyebrow'),
                                __('frontend::homepage.section_5_vessel_02_eyebrow'),
                            ];
                            $cruiseBayInfo = [
                                __('frontend::homepage.section_5_vessel_01_bay'),
                                __('frontend::homepage.section_5_vessel_02_bay'),
                            ];
                            $cruiseDefaultNames = [
                                __('frontend::homepage.section_5_vessel_01_name'),
                                __('frontend::homepage.section_5_vessel_02_name'),
                            ];
                            $cruiseButtonLabels = [
                                __('frontend::homepage.button_explore_green_ruby_1'),
                                __('frontend::homepage.button_explore_green_ruby_2'),
                            ];

                            $cruiseName = !empty($listCruise[$i]->name)
                                ? $listCruise[$i]->name
                                : ($cruiseDefaultNames[$i] ?? '');
                            $shipSpecs = FeCruiseUtils::getDisplayShipSpecs(FeCruiseUtils::isGreenRuby2($listCruise[$i]->name ?? ''));
                            $statLength = $shipSpecs['length'];
                            $statCabins = $shipSpecs['cabins'];
                            $statGuests = $shipSpecs['guests'];
                        @endphp
                        <div class="cruise ship-card row no-gutters">
                            <div class="ship-image-wrap image col-lg-6" style="background-image: url({{ FeUtils::getThumbnail(['link' => $listCruise[$i]->image_link, 'w' => 960, 'h' => 490]) }})"></div>
                            <div class="main-info ship-text col-lg-6">
                                <div class="main-info-wrapper text-white">
                                    <div class="ship-text-top">
                                        <p class="section-title ship-eyebrow text-left">{{ $cruiseEyebrows[$i] ?? '' }}</p>
                                        <h3 class="section-description ship-name mb-0 font-heading text-left text-white">{{ $cruiseName }}</h3>
                                        <p class="ship-bay">{{ $cruiseBayInfo[$i] ?? '' }}</p>
                                        @if (!empty($listCruise[$i]->summary))
                                            <p class="ship-summary description give-ellipsis after-3-lines">{{ $listCruise[$i]->summary }}</p>
                                        @endif
                                    </div>
                                    <div class="ship-stats specification">
                                        <div class="ship-stat-item">
                                            <p class="value font-heading mb-0"><span class="ship-stat-val">{{ $statLength }}<span class="ship-stat-unit">m</span></span></p>
                                            <p class="unit ship-stat-key mb-0">{{ __('frontend::homepage.section_5_length_unit') }}</p>
                                        </div>
                                        <div class="ship-stat-item">
                                            <p class="value ship-stat-val font-heading">{{ $statCabins }}</p>
                                            <p class="unit ship-stat-key mb-0">{{ __('frontend::homepage.section_5_cabins_unit') }}</p>
                                        </div>
                                        <div class="ship-stat-item">
                                            <p class="value ship-stat-val font-heading">{{ $statGuests }}</p>
                                            <p class="unit ship-stat-key mb-0">{{ __('frontend::homepage.section_5_guests_unit') }}</p>
                                        </div>
                                    </div>
                                    <div class="ship-bottom">
                                        <a href="{{ route(Utilities::getRouteName('frontend.cruise.show'), ['languageCode' => $languageCode, 'slug' => Utilities::convertToAlias($listCruise[$i]->name), 'id' => $listCruise[$i]->id]) }}" class="ship-cta-link">
                                            {{ $cruiseButtonLabels[$i] ?? __('frontend::homepage.button_explorer_the_cruise') }}
                                            <span class="ship-cta-arrow">→</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
                @endif
            </div>
        </section>
        @php
            $section6CruiseFilters = [];
            if (!empty($listAllCruise) && count($listAllCruise) >= 2) {
                $section6CruiseFilters = [
                    ['id' => $listAllCruise[0]->id, 'label' => __('frontend::homepage.section_5_vessel_01_name')],
                    ['id' => $listAllCruise[1]->id, 'label' => __('frontend::homepage.section_5_vessel_02_name')],
                ];
            }
        @endphp
        @include('frontend::shared.section.section-cabin', [
            'class' => 'section-6',
            'title' => __('frontend::homepage.section_6_title'),
            'description' => __('frontend::homepage.section_6_description'),
            'list' => $listCabin,
            'suitesGrid' => true,
            'cruiseFilters' => $section6CruiseFilters,
        ])
        <section class="section-7 bg">
            <div class="container-fluid px-0 pb-0 position-relative">
                <div class="container">
                    <p class="section-eyebrow section-eyebrow--gold">{{ __('frontend::homepage.section_7_title') }}</p>
                    <p class="section-description font-heading text-white">{!! __('frontend::homepage.section_7_description') !!}</p>
                    @if (count($listService) > 1)
                        <div class="tab-filter">
                            <div class="list-button d-flex flex-wrap justify-content-center transparent">
                                @for ($i = 0; $i < count($listService); $i++)
                                    <div class="item">
                                        <button type="button" class="font-weight-bold text-white border-0 {{ $i == 0 ? 'active' : '' }}">{{ $listService[$i]->name }}</button>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @endif
                    @if (count($listService) > 0)
                        <div class="list-item">
                            @for ($i = 0; $i < count($listService); $i++)
                                @php
                                    $cruiseGreenRuby1 = $listAllCruise[0] ?? null;
                                    $cruiseGreenRuby2 = $listAllCruise[1] ?? null;
                                    
                                    $cruiseGreenRuby1Url = $cruiseGreenRuby1
                                        ? route(Utilities::getRouteName('frontend.cruise.show'), [
                                            'languageCode' => $languageCode,
                                            'slug' => Utilities::convertToAlias($cruiseGreenRuby1->name),
                                            'id' => $cruiseGreenRuby1->id,
                                        ])
                                        : '';
                                    $cruiseGreenRuby2Url = $cruiseGreenRuby2
                                        ? route(Utilities::getRouteName('frontend.cruise.show'), [
                                            'languageCode' => $languageCode,
                                            'slug' => Utilities::convertToAlias($cruiseGreenRuby2->name),
                                            'id' => $cruiseGreenRuby2->id,
                                        ])
                                        : '';
                                    $cruiseGreenRuby1Name = $cruiseGreenRuby1
                                        ? (string) $cruiseGreenRuby1->name
                                        : '';
                                    $cruiseGreenRuby2Name = $cruiseGreenRuby2
                                        ? (string) $cruiseGreenRuby2->name
                                        : '';

                            
                                @endphp
                                <div class="item {{ $i > 0 ? 'd-none' : '' }}">
                                    <div class="item-wrapper">
                                        <div class="image" style="background-image: url({{ FeUtils::getThumbnail(['link' => $listService[$i]->image_link, 'w' => 950, 'h' => 500]) }})"></div>
                                        <div class="main-info d-flex flex-column w-100">
                                            <div class="main-info-body">
                                                <p class="title">{{ $listService[$i]->name }}</p>
                                                <p class="description give-ellipsis after-5-lines">{{ $listService[$i]->description }}</p>
                                            </div>
                                            <div class="main-info-footer">
                                                @if ($cruiseGreenRuby1Url)
                                                    <a href="{{ $cruiseGreenRuby1Url }}" class="btn-explorer btn btn-warning mb-2">
                                                        <span class="d-none d-md-inline">{{ __('frontend::homepage.button_explore_cruise', ['name' => $cruiseGreenRuby1Name]) }}</span>
                                                        <span class="d-md-none">{{ __('frontend::homepage.button_explore_cruise', ['name' => $cruiseGreenRuby1Name ?: __('frontend::homepage.section_5_vessel_01_name')]) }}</span>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                            <path d="M5 12l14 0"/>
                                                            <path d="M15 16l4 -4"/>
                                                            <path d="M15 8l4 4"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                                @if ($cruiseGreenRuby2Url)
                                                    <a href="{{ $cruiseGreenRuby2Url }}" class="btn-explorer-link">
                                                        {{ __('frontend::homepage.button_explore_cruise', ['name' => $cruiseGreenRuby2Name]) }}
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                            <path d="M5 12l14 0"/>
                                                            <path d="M15 16l4 -4"/>
                                                            <path d="M15 8l4 4"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    @endif
                </div>
            </div>
        </section>
        @include('frontend::shared.section.section-amenity', [
            'class' => 'section-8',
            'title' => __('frontend::homepage.section_8_title'),
            'description' => __('frontend::homepage.section_8_description'),
            'titleClass' => 'section-eyebrow section-eyebrow--gold',
            'tagHeading' => 'p',
            'iconTintOnLight' => true,
        ])
        <section class="section-9 bg">
            <div class="container-fluid px-0 position-relative">
                <div class="container">
                    <p class="section-eyebrow section-eyebrow--gold">{{ __('frontend::homepage.section_9_title') }}</p>
                    <p class="section-description font-heading">{!! __('frontend::homepage.section_9_description') !!}</p>
                </div>
                @if (count($listExpActivity) > 1)
                    @if (count($listExpActivityFilter) > 1)
                        <div class="tab-filter">
                            <div class="list-button d-flex flex-wrap justify-content-center">
                                <div class="item">
                                    <button type="button" class="font-weight-bold text-white border-0 active" data-id="">{{ __('frontend::common.all') }}</button>
                                </div>
                                @foreach ($listExpActivityFilter as $key => $value)
                                    @if ($value)
                                        <div class="item">
                                            <button type="button" class="font-weight-bold text-white border-0" data-id="{{ $key }}">{{ $value }}</button>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <div class="slide-1">
                        <div class="list-item owl-carousel owl-theme">
                            @for ($i = 0; $i < count($listExpActivity); $i++)
                                @php
                                    $listFile = isset($listExpActivity[$i]->file) ? $listExpActivity[$i]->file : [];
                                    if (count($listFile) == 0 && $listExpActivity[$i]->image_link) {
                                        $fileExtension = pathinfo($listExpActivity[$i]->image_link, PATHINFO_EXTENSION);
                                        if (in_array($fileExtension, config('backend.fileTypeImage'))) {
                                            $listFile[] = (object)[
                                                'link' => $listExpActivity[$i]->image_link,
                                                'extension' => $fileExtension,
                                            ];
                                        }
                                    }
                                @endphp
                                @for ($j = 0; $j < count($listFile); $j++)
                                    @php
                                        $isFileVideo = in_array($listFile[$j]->extension, config('backend.fileTypeVideo'));
                                    @endphp
                                    <div class="item text-white d-flex h-100 {{ $isFileVideo ? 'item-video' : '' }}" data-group-id="{{ $listExpActivity[$i]->group_id }}">
                                        <div class="item-wrapper d-flex flex-column w-100">
                                            <div class="image-wrapper position-relative">
                                                @if (in_array($listFile[$j]->extension, config('backend.fileTypeImage')))
                                                    <img src="{{ FeUtils::getThumbnail(['link' => $listFile[$j]->link, 'w' => 860, 'h' => 545]) }}" alt="{{ $listExpActivity[$i]->name }}" class="position-absolute w-100 h-100" loading="lazy" decoding="async" />
                                                @elseif ($isFileVideo)
                                                    <video src="{{ asset(Utilities::getFileLink($listFile[$j]->link)) }}" class="position-absolute w-100 h-100" controls preload="none"></video>
                                                @endif
                                                <div class="main-info position-absolute w-100">
                                                    <div class="main-info-wrapper position-relative">
                                                        <div class="list-button d-flex mb-2">
                                                            @if ($listExpActivity[$i]->group_name)
                                                                <button type="button" class="btn btn-warning">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                        <path d="M3 8v4.172a2 2 0 0 0 .586 1.414l4.828 4.828a2 2 0 0 0 2.828 0l4.172 -4.172a2 2 0 0 0 0 -2.828l-4.828 -4.828a2 2 0 0 0 -1.414 -.586h-4.172a1 1 0 0 0 -1 1z"/>
                                                                        <path d="M7 9l0 .01"/>
                                                                        <path d="M14 17l3.586 3.586a2 2 0 0 0 2.828 0l.172 -.172a2 2 0 0 0 0 -2.828l-5.586 -5.586"/>
                                                                    </svg>{{ $listExpActivity[$i]->group_name }}
                                                                </button>
                                                            @endif
                                                            <button type="button" class="btn btn-warning">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                    <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"/>
                                                                    <path d="M12 7v5l3 3"/>
                                                                </svg>{{ Utilities::formatDisplayTime($listExpActivity[$i]->start_time) }} - {{ Utilities::formatDisplayTime($listExpActivity[$i]->end_time) }}
                                                            </button>
                                                        </div>
                                                        <h3 class="title mb-2">
                                                            <a href="{{ route(Utilities::getRouteName('frontend.experience.show'), ['languageCode' => $languageCode, 'slug' => Utilities::convertToAlias($listExpActivity[$i]->name), 'id' => $listExpActivity[$i]->id]) }}" class="text-reset">{{ $listExpActivity[$i]->name }}</a>
                                                        </h3>
                                                        <p class="description mb-0 give-ellipsis after-2-lines">{{ $listExpActivity[$i]->summary }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            @endfor
                        </div>
                    </div>
                @endif
            </div>
        </section>
        @include('frontend::shared.section.section-testimonial', ['class' => 'section-10'])
        <section class="section-11 pb-0">
            <div class="container position-relative">
                <div class="statistic">
                    @if ($guestRating || $happyGuests || $wouldRecommend || $support)
                        <div class="list-item row">
                            @if ($guestRating)
                                <div class="item col-5 col-sm-6 col-md-3 text-center">
                                    <p class="value mb-2 font-heading">{{ $guestRating }}</p>
                                    <p class="label mb-0">{{ __('frontend::homepage.section_11_guest_rating') }}</p>
                                </div>
                            @endif
                            @if ($happyGuests)
                                <div class="item col-7 col-sm-6 col-md-3 text-center">
                                    <p class="value mb-2 font-heading">{{ $happyGuests }}</p>
                                    <p class="label mb-0">{{ __('frontend::homepage.section_11_happy_guests') }}</p>
                                </div>
                            @endif
                            @if ($wouldRecommend)
                                <div class="item col-5 col-sm-6 col-md-3 text-center">
                                    <p class="value mb-2 font-heading">{{ $wouldRecommend }}</p>
                                    <p class="label mb-0">{{ __('frontend::homepage.section_11_would_recommend') }}</p>
                                </div>
                            @endif
                            @if ($support)
                                <div class="item col-7 col-sm-6 col-md-3 text-center">
                                    <p class="value mb-2 font-heading">24/7</p>
                                    <p class="label mb-0">{{ __('frontend::homepage.section_11_support') }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
                @if (count($listAward) > 0)
                    <div class="award text-white">
                        <div class="award-header">
                            <h2 class="section-title font-weight-normal text-left">{{ __('frontend::homepage.section_11_title') }}</h2>
                            <p class="section-description text-left">{{ __('frontend::homepage.section_11_description') }}</p>
                        </div>
                        <div class="award-body">
                            <div class="list-item d-flex">
                                @for ($i = 0; $i < count($listAward); $i++)
                                    <div class="item media">
                                        <div class="image align-self-center">
                                            @include('frontend::shared.image-wrapper', [
                                                'link' => $listAward[$i]->link,
                                                'alt' => $listAward[$i]->title,
                                                'imageConfig' => ['w' => 60, 'h' => 60]
                                            ])
                                        </div>
                                        <div class="media-body align-self-center">
                                            <p class="title mb-0">{{ $listAward[$i]->title }}</p>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
        @include('frontend::shared.section.section-call-to-action', [
            'class' => 'section-12',
            'title' => __('frontend::homepage.section_12_title'),
            'description' => __('frontend::homepage.section_12_description'),
            'content' => $readyToSailDescription,
            'buttons' => [[
                'label' => __('frontend::homepage.button_book_your_cruise'),
                'class' => 'btn-warning',
                'url' => route(Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode])
            ], [
                'label' => __('frontend::homepage.button_explore_itinerary'),
                'class' => 'btn-success',
                'url' => route(Utilities::getRouteName('frontend.itinerary.index'), ['languageCode' => $languageCode])
            ]]
        ])
        <section class="section-13 bg bg-tender-white">
            <div class="container-fluid">
                <div class="container">
                    <p class="section-eyebrow section-eyebrow--gold">{{ __('frontend::homepage.section_13_title') }}</p>
                    <p class="section-description font-heading">{{ __('frontend::homepage.section_13_description') }}</p>
                    @if (count($listFaq) > 0)
                        <ul class="list-faq list-unstyled">
                            @for ($i = 0; $i < count($listFaq); $i++)
                                <li class="item">
                                    <div class="item-wrapper position-relative">
                                        <div class="group-name d-inline-block mb-3 mb-xl-2 text-uppercase">{{ $listFaq[$i]->group_name }}</div>
                                        <div class="question article-content">{!! safe_html($listFaq[$i]->question) !!}</div>
                                        <div class="answer article-content">{!! safe_html($listFaq[$i]->answer) !!}</div>
                                        <button type="button" class="btn-toggle border-0 rounded-circle" title="Toggle FAQ">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="faq-icon" aria-hidden="true">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M12 5l0 14"/>
                                                <path d="M5 12l14 0"/>
                                            </svg>
                                        </button>
                                    </div>
                                </li>
                            @endfor
                        </ul>
                        <div class="text-center">
                            <a href="{{ route(Utilities::getRouteName('frontend.faq.index'), ['languageCode' => $languageCode]) }}" class="btn-ghost-gold">
                                {{ __('frontend::common.button_view_all') }}
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M5 12l14 0"/>
                                    <path d="M15 16l4 -4"/>
                                    <path d="M15 8l4 4"/>
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </section>
