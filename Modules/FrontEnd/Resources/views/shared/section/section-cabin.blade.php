@php
$class = isset($class) ? $class : '';
$title = isset($title) ? $title : '';
$description = isset($description) ? $description : '';
$eyebrow = $eyebrow ?? null;
$titleHtml = $titleHtml ?? null;
$list = isset($list) ? $list : [];
$filters = isset($filters) && count($filters) > 0 ? $filters : [];
$languageCode = Route::current()->parameter('languageCode');
$isShowBookNow = $isShowBookNow ?? true;
$suitesGrid = $suitesGrid ?? false;
@endphp

<section class="{{ $class }} section-cabin bg bg-azure">
    <div class="container-fluid">
        <div class="container">
            @if ($eyebrow)
                <p class="section-eyebrow section-eyebrow--gold">{{ $eyebrow }}</p>
            @endif
            @if ($titleHtml)
                <h2 class="section-description font-heading">{!! $titleHtml !!}</h2>
            @else
                @if ($title)
                    @if ($suitesGrid)
                        <p class="section-eyebrow section-eyebrow--gold">{{ $title }}</p>
                    @else
                        <h2 class="section-title">{{ $title }}</h2>
                    @endif
                @endif
                @if ($description)
                    <p class="section-description font-heading{{ $suitesGrid ? ' suite-section-title' : '' }}">{{ $description }}</p>
                @endif
            @endif
            @if (count($filters) > 1)
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

                                $specMap = [
                                    'Serenity Deluxe' => [
                                        ['icon' => 'eye',      'text' => $list[$i]->view ?: 'Ocean view'],
                                        ['icon' => 'home',     'text' => 'Main Deck'],
                                        ['icon' => 'bath',     'text' => 'Bathtub'],
                                        ['icon' => 'maximize', 'text' => $cabinArea . ' m²'],
                                    ],
                                    'Ocean Breeze Premium' => [
                                        ['icon' => 'eye',      'text' => $list[$i]->view ?: 'Ocean view'],
                                        ['icon' => 'home',     'text' => 'Upper Deck'],
                                        ['icon' => 'bath',     'text' => 'Bathtub'],
                                        ['icon' => 'maximize', 'text' => $cabinArea . ' m²'],
                                    ],
                                    'Royal Romance Suite' => [
                                        ['icon' => 'eye',      'text' => $list[$i]->view ?: 'Ocean view'],
                                        ['icon' => 'home',     'text' => 'Upper Deck'],
                                        ['icon' => 'bath',     'text' => 'Jacuzzi'],
                                        ['icon' => 'bell',     'text' => 'Butler'],
                                    ],
                                    'Imperial Suite' => [
                                        ['icon' => 'eye',      'text' => $list[$i]->view ?: 'Ocean view'],
                                        ['icon' => 'bath',     'text' => 'Jacuzzi Balcony'],
                                        ['icon' => 'coffee',   'text' => 'In-room Dining'],
                                        ['icon' => 'bell',     'text' => 'Butler'],
                                    ],
                                ];

                                $specs = $specMap[$cabinName] ?? [
                                    ['icon' => 'eye',      'text' => $list[$i]->view ?: 'Ocean view'],
                                    ['icon' => 'maximize', 'text' => $cabinArea . ' m²'],
                                    ['icon' => 'users',    'text' => ($list[$i]->capacity ?? '') . ' Guests'],
                                    ['icon' => 'home',     'text' => 'On Board'],
                                ];

                                $categoryMap = [
                                    'Serenity Deluxe'        => 'Deluxe Cabin',
                                    'Ocean Breeze Premium'   => 'Premium Cabin',
                                    'Royal Romance Suite'    => 'Suite',
                                    'Imperial Suite'         => 'Signature Suite',
                                ];
                                $cabinCategory = $categoryMap[$cabinName] ?? 'Cabin';

                                $deckMap = [
                                    'Serenity Deluxe'        => 'Main Deck',
                                    'Ocean Breeze Premium'   => 'Upper Deck',
                                    'Royal Romance Suite'    => 'Upper Deck · Front',
                                    'Imperial Suite'         => 'Upper Deck · Rear',
                                ];
                                $deckLabel = $deckMap[$cabinName] ?? '';
                            @endphp
                            <div class="item d-flex h-100" data-cabin-class="{{ $list[$i]->cabin_class }}">
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
                                            AI Concierge
                                        </span>
                                        @if(str_contains($cabinName, 'Imperial') || (int) ($list[$i]->area ?? 0) === 120)
                                            <span class="suite-badge-featured">Largest Suite</span>
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
                                        <p class="description suite-card-desc text-break give-ellipsis after-2-lines">{{ Str::limit($list[$i]->summary ?? '', 110) }}</p>
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
                                                <div>
                                                    <a
                                                        href="{{ route(isset($languageCode) ? Utilities::getRouteName('frontend.booking') : 'frontend.booking', isset($languageCode) ? ['languageCode' => $languageCode, 'cabin_id' => $list[$i]->id] : ['cabin_id' => $list[$i]->id]) }}"
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
                @include('frontend::shared.modal-cabin-details')
            @endif
        </div>
    </div>
</section>
