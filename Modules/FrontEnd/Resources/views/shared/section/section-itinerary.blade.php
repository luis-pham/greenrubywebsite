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

    $languageCode = Route::current()->parameter('languageCode');
@endphp

<section class="{{ $class }} section-itinerary bg">
    <div class="container-fluid px-0" @if ($backgroundImage) style="background-image: linear-gradient(to bottom, #00000080 50%, {{ $backgroundBottom }} 50%), url('{{ FeUtils::getImageLink($backgroundImage) }}');" @endif>
        <div class="container">
            @if ($title)
                <{{ $tagHeading }} class="{{ $titleClass }}">{{ $title }}</{{ $tagHeading }}>
            @endif
            @if ($subTitle)
                <p class="section-description font-heading {{ $backgroundImage ? 'text-white' : '' }}">{!! $subTitle !!}</p>
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
                                        <a href="{{ route(Utilities::getRouteName('frontend.cruise.show'), ['languageCode' => $languageCode, 'slug' => Utilities::convertToAlias($list[$i]->cruise_name), 'id' => $list[$i]->cruise_id]) }}" class="btn-cruise btn btn-warning position-absolute d-block d-md-none">
                                            <i class="fa-solid fa-ship"></i>{{ $list[$i]->cruise_name }}
                                        </a>
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
</section>
