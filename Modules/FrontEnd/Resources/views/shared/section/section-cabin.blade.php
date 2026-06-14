@php
$class = isset($class) ? $class : '';
$title = isset($title) ? $title : '';
$description = isset($description) ? $description : '';
$eyebrow = $eyebrow ?? null;
$titleHtml = $titleHtml ?? null;
$list = isset($list) ? $list : [];
$filters = isset($filters) && count($filters) > 0 ? $filters : [];
$cruiseFilters = isset($cruiseFilters) && count($cruiseFilters) > 0 ? $cruiseFilters : [];
$languageCode = Route::current()->parameter('languageCode');
$isShowBookNow = $isShowBookNow ?? true;
$suitesGrid = $suitesGrid ?? false;
$cruiseId = $cruiseId ?? null;
$itineraryId = $itineraryId ?? null;
@endphp

<section class="{{ $class }} section-cabin bg bg-azure">
    <div class="container-fluid">
        <div class="container">
            @if ($eyebrow)
                <p class="section-eyebrow section-eyebrow--gold">{{ $eyebrow }}</p>
            @endif
            @if ($titleHtml)
                <h2 class="section-description font-heading">{!! safe_html($titleHtml) !!}</h2>
            @else
                @if ($title)
                    @if ($suitesGrid)
                        <p class="section-eyebrow section-eyebrow--gold">{{ $title }}</p>
                    @else
                        <h2 class="section-title">{{ $title }}</h2>
                    @endif
                @endif
                @if ($description)
                    <p class="section-description font-heading{{ $suitesGrid ? ' suite-section-title' : '' }}">{!! safe_html($description) !!}</p>
                @endif
            @endif
            @if (count($cruiseFilters) > 1)
                <div class="tab-filter">
                    <div class="list-button d-flex flex-wrap justify-content-center">
                        @for ($i = 0; $i < count($cruiseFilters); $i++)
                            <div class="item">
                                <button type="button" class="font-weight-bold text-white border-0{{ $i === 0 ? ' active' : '' }}" data-cruise-id="{{ $cruiseFilters[$i]['id'] }}">{{ $cruiseFilters[$i]['label'] }}</button>
                            </div>
                        @endfor
                    </div>
                </div>
            @elseif (count($filters) > 1)
                <div class="tab-filter">
                    <div class="list-button d-flex flex-wrap justify-content-center">
                        <div class="item">
                            <button type="button" class="font-weight-bold text-white border-0 active" data-cabin-class="">{{ __('frontend::common.all') }}</button>
                        </div>
                        @for ($i = 0; $i < count($filters); $i++)
                            <div class="item">
                                <button type="button" class="font-weight-bold text-white border-0" data-cabin-class="{{ $filters[$i]}}">{{ $filters[$i]}}</button>
                            </div>
                        @endfor
                    </div>
                </div>
            @endif
            @if (count($list) > 0)
                <div class="slide-1">
                    <div class="list-itinerary-cruise owl-carousel owl-theme{{ $suitesGrid ? ' suites-grid' : '' }}">
                        @for ($i = 0; $i < count($list); $i++)
                            @php
                                $isSuiteFeatured = stripos((string) $list[$i]->name, 'Opera House') !== false
                                    || (int) ($list[$i]->area ?? 0) === 120;

                                $cabinName = $list[$i]->name ?? '';
                                $cabinArea = $list[$i]->area ?? '';

                                $cabinSlugMap = [
                                    'Serenity Deluxe' => 'serenity_deluxe',
                                    'Ocean Breeze Premium' => 'ocean_breeze_premium',
                                    'Royal Romance Suite' => 'royal_romance_suite',
                                    'Imperial Suite' => 'imperial_suite',
                                ];
                                $cabinSlug = $cabinSlugMap[$cabinName] ?? null;
                                $allSpecSets = trans('frontend::sectionCabin.spec_sets');
                                $specSet = ($cabinSlug && is_array($allSpecSets)) ? ($allSpecSets[$cabinSlug] ?? null) : null;

                                if (is_array($specSet) && count($specSet) > 0) {
                                    $specs = [];
                                    foreach ($specSet as $row) {
                                        if (($row['label'] ?? '') === 'area') {
                                            $specs[] = ['icon' => $row['icon'], 'text' => $cabinArea . ' m²'];
                                        } elseif (($row['label'] ?? '') === 'ocean_view') {
                                            $specs[] = ['icon' => $row['icon'], 'text' => $list[$i]->view ?: __('frontend::sectionCabin.labels.ocean_view')];
                                        } else {
                                            $specs[] = ['icon' => $row['icon'], 'text' => __('frontend::sectionCabin.labels.' . $row['label'])];
                                        }
                                    }
                                } else {
                                    $specs = [
                                        ['icon' => 'eye', 'text' => $list[$i]->view ?: __('frontend::sectionCabin.labels.ocean_view')],
                                        ['icon' => 'maximize', 'text' => $cabinArea . ' m²'],
                                        ['icon' => 'users', 'text' => __('frontend::sectionCabin.labels.guests', ['count' => $list[$i]->capacity ?? ''])],
                                        ['icon' => 'home', 'text' => __('frontend::sectionCabin.labels.on_board')],
                                    ];
                                }

                                $allCategories = trans('frontend::sectionCabin.categories');
                                $cabinCategory = ($cabinSlug && is_array($allCategories) && isset($allCategories[$cabinSlug]))
                                    ? $allCategories[$cabinSlug]
                                    : __('frontend::sectionCabin.labels.cabin');

                                $allDeckBadges = trans('frontend::sectionCabin.deck_badges');
                                $deckLabelKey = ($cabinSlug && is_array($allDeckBadges)) ? ($allDeckBadges[$cabinSlug] ?? null) : null;
                                $deckLabel = $deckLabelKey ? __('frontend::sectionCabin.labels.' . $deckLabelKey) : '';

                                $allSummaries = trans('frontend::sectionCabin.summaries');
                                $suiteSummary = ($suitesGrid && $cabinSlug && is_array($allSummaries) && isset($allSummaries[$cabinSlug]))
                                    ? $allSummaries[$cabinSlug]
                                    : ($suitesGrid ? '' : Str::limit($list[$i]->summary ?? '', 110));
                                if ($suitesGrid && !$suiteSummary) {
                                    $suiteSummary = Str::limit($list[$i]->summary ?? '', 110);
                                }
                            @endphp
                            <div class="item d-flex h-100" data-cabin-class="{{ $list[$i]->cabin_class }}" data-cruise-id="{{ $list[$i]->cruise_id ?? '' }}">
                                <div class="item-wrapper d-flex flex-column w-100 bg-white{{ $suitesGrid ? ' suite-card' : '' }}{{ $suitesGrid && $isSuiteFeatured ? ' suite-featured' : '' }}">
                                    <div class="item-header{{ $suitesGrid ? ' suite-card-image-wrap' : '' }}">
                                        <a href="javascript:;" class="btn-view-cabin-details" data-id="{{ $list[$i]->id }}">
                                            @include('frontend::shared.image-wrapper', [
                                                'link' => $list[$i]->image_link,
                                                'alt' => $list[$i]->name,
                                                'imageConfig' => $suitesGrid ? ['w' => 600, 'h' => 400] : ['w' => 545, 'h' => 404],
                                                'ratio' => $suitesGrid ? '3-2' : null,
                                            ])
                                        </a>
                                        @if($deckLabel)
                                            <span class="suite-badge-deck">{{ $deckLabel }}</span>
                                        @endif
                                        <span class="suite-badge-ai">
                                            <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <circle cx="12" cy="12" r="3"/>
                                                <path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12"/>
                                            </svg>
                                            {{ __('frontend::sectionCabin.badges.ai_concierge') }}
                                        </span>
                                        @if(str_contains($cabinName, 'Imperial') || (int) ($list[$i]->area ?? 0) === 120)
                                            <span class="suite-badge-featured">{{ __('frontend::sectionCabin.badges.largest_suite') }}</span>
                                        @endif
                                        @if ($suitesGrid && $list[$i]->area)
                                            <span class="suite-size-badge">{{ $list[$i]->area }}m²</span>
                                        @endif
                                    </div>
                                    <div class="item-body{{ $suitesGrid ? ' suite-card-content' : '' }}">
                                        <p class="suite-card-category">{{ $cabinCategory }}</p>
                                        <h3 class="title mb-2 font-heading give-ellipsis after-2-lines{{ $suitesGrid ? ' suite-card-name' : '' }}">
                                            <a href="javascript:;" class="btn-view-cabin-details text-reset" data-id="{{ $list[$i]->id }}">{{ $list[$i]->name }}</a>
                                        </h3>
                                        <p class="description suite-card-desc text-break">{{ $suitesGrid ? $suiteSummary : Str::limit($list[$i]->summary ?? '', 110) }}</p>
                                        <div class="list-specification suite-specs">
                                            @foreach($specs as $spec)
                                                <div class="item-specification suite-spec-item">
                                                    @if($spec['icon'] === 'eye')
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                            <circle cx="12" cy="12" r="3"/>
                                                        </svg>
                                                    @elseif($spec['icon'] === 'home')
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                                        </svg>
                                                    @elseif($spec['icon'] === 'bath')
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                            <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
                                                        </svg>
                                                    @elseif($spec['icon'] === 'bell')
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                                            <path d="M13.73 21a2 2 0 01-3.46 0"/>
                                                        </svg>
                                                    @elseif($spec['icon'] === 'coffee')
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                            <path d="M18 8h1a4 4 0 010 8h-1"/>
                                                            <path d="M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/>
                                                        </svg>
                                                    @elseif($spec['icon'] === 'maximize')
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                            <polyline points="15 3 21 3 21 9"/>
                                                            <polyline points="9 21 3 21 3 15"/>
                                                            <line x1="21" y1="3" x2="14" y2="10"/>
                                                            <line x1="3" y1="21" x2="10" y2="14"/>
                                                        </svg>
                                                    @elseif($spec['icon'] === 'users')
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                                                            <circle cx="9" cy="7" r="4"/>
                                                            <path d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"/>
                                                        </svg>
                                                    @endif
                                                    <div class="media-body">
                                                        <p class="suite-spec-text">{{ $spec['text'] }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="item-footer">
                                        <hr />
                                        <div class="d-flex align-items-center {{$isShowBookNow ? "justify-content-between" : 'justify-content-center'}}{{ $suitesGrid ? ' suite-card-actions' : '' }}">
                                            <div>
                                                <a href="javascript:;" class="btn-view-cabin-details btn-view-details d-block" data-id="{{ $list[$i]->id }}">{{ __('frontend::common.button_view_cabin') }}</a>
                                            </div>
                                            @if($isShowBookNow)
                                                @php
                                                    $bookingParams = array_filter([
                                                        'languageCode' => $languageCode,
                                                        'cruise_id' => $cruiseId,
                                                        'itinerary_id' => $itineraryId,
                                                        'cabin_id' => $list[$i]->id,
                                                    ], fn ($value) => $value !== null && $value !== '');
                                                @endphp
                                                <div>
                                                    <a
                                                        href="{{ route(isset($languageCode) ? Utilities::getRouteName('frontend.booking') : 'frontend.booking', $bookingParams) }}"
                                                        class="btn-book-now btn btn-sm btn-warning"
                                                    >
                                                        {{ __('frontend::common.book_now') }} <i class="fa-solid fa-calendar-check ml-2"></i>
                                                    </a>
                                                </div>
                                            @endif
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
