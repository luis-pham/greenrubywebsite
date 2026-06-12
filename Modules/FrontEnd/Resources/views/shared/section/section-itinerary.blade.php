@php
    $listBay = [
        1 => __('frontend::common.ha_long_bay'),
        2 => __('frontend::common.lan_ha_bay'),
    ];
    $class = isset($class) ? $class : '';
    $title = isset($title) ? $title : '';
    $subTitle = isset($subTitle) ? $subTitle : '';
    $list = isset($list) && count($list) > 0 ? $list : [];
    $backgroundImage = isset($backgroundImage) ? $backgroundImage : null;
    $backgroundBottom = isset($backgroundBottom) ? $backgroundBottom : '#fff';
    $tagHeading = isset($tagHeading) ? $tagHeading : 'p';
    $titleClass = $titleClass ?? 'section-title';
    $itineraryRedesign = $itineraryRedesign ?? false;

    $languageCode = Route::current()->parameter('languageCode');
@endphp

@if ($itineraryRedesign)
    <div class="gallery-filter-sticky">
        <div class="container-fluid px-0">
            <div class="gallery-filter-inner">
                <div class="container">
                    <div class="gallery-filter-bar list-filter">
                        <button type="button" data-bay="" class="item gallery-filter-tab active">
                            {{ __('frontend::common.all') }}
                        </button>
                        @foreach ($listBay as $key => $value)
                            <button type="button" data-bay="{{ $key }}" class="item gallery-filter-tab">{{ $value }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<section class="{{ $class }} section-itinerary bg">
    @if ($itineraryRedesign)
        <div class="container-fluid px-0">
            @if ($title || $subTitle)
                <div class="container">
                    @if ($title)
                        <{{ $tagHeading }} class="{{ $titleClass }}">{{ $title }}</{{ $tagHeading }}>
                    @endif
                    @if ($subTitle)
                        <p class="section-description font-heading">{!! safe_html($subTitle) !!}</p>
                    @endif
                </div>
            @endif

            <div class="itin-compare">
                <div class="container">
                    <div class="itin-compare-label">2D1N vs 3D2N — What's the difference?</div>
                    <div class="itin-compare-grid">
                        <div class="itin-cmp-card">
                            <div class="itin-cmp-dur">2 Days · 1 Night</div>
                            <div class="itin-cmp-tag">Quick Escape</div>
                            <ul class="itin-cmp-list">
                                <li>Arrive afternoon, depart before lunch next day</li>
                                <li>Luon Cave kayak + Titop Island (Ha Long) or Dark &amp; Bright Cave + Ao Ech Lagoon (Lan Ha)</li>
                                <li>Sunset happy hour, squid fishing, cooking class</li>
                                <li>Ideal for tight schedules</li>
                            </ul>
                        </div>
                        <div class="itin-cmp-card itin-cmp-card--popular">
                            <div class="itin-cmp-popular-badge">Most popular</div>
                            <div class="itin-cmp-dur">3 Days · 2 Nights</div>
                            <div class="itin-cmp-tag">Full Discovery</div>
                            <ul class="itin-cmp-list">
                                <li>Full extra day on the water</li>
                                <li>Fishing village visit + white sand beach swimming</li>
                                <li>Private romantic dinner (sundeck / poolside / cabin)</li>
                                <li>Best for first-time visitors &amp; honeymoon</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            @if (count($list) > 0)
                <div class="container">
                    <div class="slide-1">
                        <div class="list-itinerary-cruise owl-carousel owl-theme">
                            @for ($i = 0; $i < count($list); $i++)
                                @php
                                    $destination = array_key_exists($list[$i]->bay, $listBay) ? $listBay[$list[$i]->bay] : '';
                                    $durationBadge = $list[$i]->duration . __('frontend::common.day_short');
                                    if ($list[$i]->duration > 1) {
                                        $durationBadge .= ' · ' . ($list[$i]->duration - 1) . __('frontend::common.night_short');
                                    }
                                    $cruiseId = isset($list[$i]->cruise_id) ? $list[$i]->cruise_id : 0;
                                    $itineraryId = 0;
                                    if (isset($list[$i]->id)) {
                                        $itineraryId = $list[$i]->id;
                                    } else if (isset($list[$i]->itinerary_id)) {
                                        $itineraryId = $list[$i]->itinerary_id;
                                    }
                                    $slug = Utilities::convertToAlias($list[$i]->name);
                                    $url = route(Utilities::getRouteName('frontend.itinerary.show'), ['languageCode' => $languageCode, 'slug' => $slug, 'cruise_id' => $cruiseId, 'itinerary_id' => $itineraryId]);
                                @endphp
                                <div class="item d-flex h-100" data-itinerary-id="{{ $list[$i]->id }}" data-cruise-id="{{ $list[$i]->cruise_id }}" data-bay="{{ $list[$i]->bay }}">
                                    <div class="item-wrapper d-flex flex-column w-100 bg-white">
                                        <div class="item-header itin-card-image-wrap position-relative">
                                            <a href="{{ $url }}">
                                                @include('frontend::shared.image-wrapper', [
                                                    'link' => $list[$i]->image_link,
                                                    'alt' => $list[$i]->name,
                                                    'imageConfig' => ['w' => 545, 'h' => 673]
                                                ])
                                            </a>
                                            <span class="itin-badge-bay">{{ $destination ?: __('frontend::common.ha_long_bay') }}</span>
                                        <span class="itin-badge-duration">{{ $durationBadge }}</span>
                                    </div>
                                    <div class="item-body">
                                        <p class="itin-eyebrow mb-0">
                                            {{ $list[$i]->cruise_name }} · {{ $destination ?: __('frontend::common.ha_long_bay') }}
                                        </p>
                                            <h3 class="itin-card-title title mb-2 font-heading give-ellipsis after-2-lines">
                                                <a href="{{ $url }}" class="text-reset">{{ $list[$i]->name }}</a>
                                            </h3>
                                            <div class="itin-highlights">
                                                <span class="itin-chip">Cave Exploration</span>
                                                <span class="itin-chip">Kayaking</span>
                                                <span class="itin-chip">All meals</span>
                                            </div>
                                            @if ($list[$i]->price)
                                                <div class="itin-price">
                                                    <span class="itin-price-from">{{ __('frontend::cruiseDetail.from') }}</span>
                                                    <span class="itin-price-val">{{ FeUtils::formatDisplayCurrency($list[$i]->price) }}</span>
                                                    <span class="itin-price-unit">/ {{ __('frontend::cruiseDetail.person') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="item-footer">
                                            <div class="itin-card-actions d-flex justify-content-between align-items-center">
                                                <div class="justify-content-start">
                                                    <a href="{{ $url }}" class="itin-btn-view btn-view-details d-block">{{ __('frontend::common.button_view_details') }}</a>
                                                </div>
                                                @if ($list[$i]->price)
                                                    <div class="justify-content-end">
                                                        <a href="{{ route(Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode, 'cruise_id' => $list[$i]->cruise_id, 'itinerary_id' => $list[$i]->id]) }}" class="itin-btn-book btn-book-now btn btn-sm btn-warning">
                                                            {{ __('frontend::common.book_now') }}
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
                </div>
            @endif
        </div>
    @else
    <div class="container-fluid px-0" @if ($backgroundImage) style="background-image: linear-gradient(to bottom, #00000080 50%, {{ $backgroundBottom }} 50%), url('{{ FeUtils::getImageLink($backgroundImage) }}');" @endif>
            <div class="container">
                @if ($title)
                    <{{ $tagHeading }} class="{{ $titleClass }}">{{ $title }}</{{ $tagHeading }}>
                @endif
                @if ($subTitle)
                    <p class="section-description font-heading {{ $backgroundImage ? 'text-white' : '' }}">{!! safe_html($subTitle) !!}</p>
                @endif
                <div class="tab-filter">
                    <div class="list-button d-flex flex-wrap justify-content-center {{ $backgroundImage ? 'transparent' : '' }}">
                        <div class="item">
                            <button type="button" class="font-weight-bold text-white border-0 active" data-bay="">{{ __('frontend::common.all') }}</button>
                        </div>
                        @foreach ($listBay as $key => $value)
                            <div class="item">
                                <button type="button" class="font-weight-bold text-white border-0" data-bay="{{ $key }}">{{ $value }}</button>
                            </div>
                        @endforeach
                    </div>
                </div>
                @if (count($list) > 0)
                    <div class="slide-1">
                        <div class="list-itinerary-cruise owl-carousel owl-theme">
                            @for ($i = 0; $i < count($list); $i++)
                                @php
                                    $destination = array_key_exists($list[$i]->bay, $listBay) ? $listBay[$list[$i]->bay] : '';
                                    $durationBadge = $list[$i]->duration . __('frontend::common.day_short');
                                    if ($list[$i]->duration > 1) {
                                        $durationBadge .= ' · ' . ($list[$i]->duration - 1) . __('frontend::common.night_short');
                                    }
                                    $cruiseId = isset($list[$i]->cruise_id) ? $list[$i]->cruise_id : 0;
                                    $itineraryId = 0;
                                    if (isset($list[$i]->id)) {
                                        $itineraryId = $list[$i]->id;
                                    } else if (isset($list[$i]->itinerary_id)) {
                                        $itineraryId = $list[$i]->itinerary_id;
                                    }
                                    $slug = Utilities::convertToAlias($list[$i]->name);
                                    $url = route(Utilities::getRouteName('frontend.itinerary.show'), ['languageCode' => $languageCode, 'slug' => $slug, 'cruise_id' => $cruiseId, 'itinerary_id' => $itineraryId]);
                                @endphp
                                <div class="item d-flex h-100" data-itinerary-id="{{ $list[$i]->id }}" data-cruise-id="{{ $list[$i]->cruise_id }}" data-bay="{{ $list[$i]->bay }}">
                                    <div class="item-wrapper d-flex flex-column w-100 bg-white">
                                        <div class="item-header itin-card-image-wrap position-relative">
                                            <a href="{{ $url }}">
                                                @include('frontend::shared.image-wrapper', [
                                                    'link' => $list[$i]->image_link,
                                                    'alt' => $list[$i]->name,
                                                    'imageConfig' => ['w' => 545, 'h' => 673]
                                                ])
                                            </a>
                                            <span class="itin-badge-bay">{{ $destination ?: __('frontend::common.ha_long_bay') }}</span>
                                        <span class="itin-badge-duration">{{ $durationBadge }}</span>
                                    </div>
                                    <div class="item-body">
                                        <p class="itin-eyebrow mb-0">
                                            {{ $list[$i]->cruise_name }} · {{ $destination ?: __('frontend::common.ha_long_bay') }}
                                        </p>
                                            <h3 class="itin-card-title title mb-2 font-heading give-ellipsis after-2-lines">
                                                <a href="{{ $url }}" class="text-reset">{{ $list[$i]->name }}</a>
                                            </h3>
                                            <div class="itin-highlights">
                                                <span class="itin-chip">Cave Exploration</span>
                                                <span class="itin-chip">Kayaking</span>
                                                <span class="itin-chip">All meals</span>
                                            </div>
                                            @if ($list[$i]->price)
                                                <div class="itin-price">
                                                    <span class="itin-price-from">{{ __('frontend::cruiseDetail.from') }}</span>
                                                    <span class="itin-price-val">{{ FeUtils::formatDisplayCurrency($list[$i]->price) }}</span>
                                                    <span class="itin-price-unit">/ {{ __('frontend::cruiseDetail.person') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="item-footer">
                                            <div class="itin-card-actions d-flex justify-content-between align-items-center">
                                                <div class="justify-content-start">
                                                    <a href="{{ $url }}" class="itin-btn-view btn-view-details d-block">{{ __('frontend::common.button_view_details') }}</a>
                                                </div>
                                                @if ($list[$i]->price)
                                                    <div class="justify-content-end">
                                                        <a href="{{ route(Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode, 'cruise_id' => $list[$i]->cruise_id, 'itinerary_id' => $list[$i]->id]) }}" class="itin-btn-book btn-book-now btn btn-sm btn-warning">
                                                            {{ __('frontend::common.book_now') }}
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
    @endif
</section>
