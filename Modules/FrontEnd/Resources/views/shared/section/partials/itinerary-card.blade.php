@php
    use Modules\BackEnd\Helpers\Utilities;

    $listBay = [
        1 => __('frontend::common.ha_long_bay'),
        2 => __('frontend::common.lan_ha_bay'),
    ];
    $destination = array_key_exists($itinerary->bay ?? null, $listBay) ? $listBay[$itinerary->bay] : '';
    $durationBadge = $itinerary->duration . __('frontend::common.day_short');
    if ($itinerary->duration > 1) {
        $durationBadge .= ' · ' . ($itinerary->duration - 1) . __('frontend::common.night_short');
    }
    $cruiseId = $itinerary->cruise_id ?? 0;
    $itineraryId = $itinerary->id ?? 0;
    $slug = Utilities::convertToAlias($itinerary->name);
    $url = route(Utilities::getRouteName('frontend.itinerary.show'), [
        'languageCode' => $languageCode,
        'slug' => $slug,
        'cruise_id' => $cruiseId,
        'itinerary_id' => $itineraryId,
    ]);
    $price = $itinerary->price ?? $itinerary->min_price ?? 0;
    $imageLink = $itinerary->image_link ?? $itinerary->cover_link ?? null;
    $cruiseName = $itinerary->cruise_name ?? '';
    $itineraryImageConfig = $itineraryImageConfig ?? ['w' => 545, 'h' => 673];
@endphp
<div class="item d-flex h-100" data-itinerary-id="{{ $itineraryId }}" data-cruise-id="{{ $cruiseId }}" data-bay="{{ $itinerary->bay ?? '' }}">
    <div class="item-wrapper d-flex flex-column w-100 bg-white">
        <div class="item-header itin-card-image-wrap position-relative">
            <a href="{{ $url }}">
                @include('frontend::shared.image-wrapper', [
                    'link' => $imageLink,
                    'alt' => $itinerary->name,
                    'imageConfig' => $itineraryImageConfig,
                ])
            </a>
            <span class="itin-badge-bay">{{ $destination ?: __('frontend::common.ha_long_bay') }}</span>
            <span class="itin-badge-duration">{{ $durationBadge }}</span>
        </div>
        <div class="item-body">
            <p class="itin-eyebrow mb-0">
                {{ $cruiseName }} · {{ $destination ?: __('frontend::common.ha_long_bay') }}
            </p>
            <h3 class="itin-card-title title mb-2 font-heading give-ellipsis after-2-lines">
                <a href="{{ $url }}" class="text-reset">{{ $itinerary->name }}</a>
            </h3>
            <div class="itin-highlights">
                <span class="itin-chip">{{ __('frontend::itineraryIndex.chip.cave') }}</span>
                <span class="itin-chip">{{ __('frontend::itineraryIndex.chip.kayaking') }}</span>
                <span class="itin-chip">{{ __('frontend::itineraryIndex.chip.meals') }}</span>
            </div>
            @if ($price)
                <div class="itin-price">
                    <span class="itin-price-from">{{ __('frontend::cruiseDetail.from') }}</span>
                    <span class="itin-price-val">{{ FeUtils::formatDisplayCurrency($price) }}</span>
                    <span class="itin-price-unit">/ {{ __('frontend::cruiseDetail.person') }}</span>
                </div>
            @endif
        </div>
        <div class="item-footer">
            <div class="itin-card-actions d-flex justify-content-between align-items-center">
                <div class="justify-content-start">
                    <a href="{{ $url }}" class="itin-btn-view btn-view-details d-block">{{ __('frontend::common.button_view_details') }}</a>
                </div>
                @if ($price)
                    <div class="justify-content-end">
                        <a href="{{ route(Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode, 'cruise_id' => $cruiseId, 'itinerary_id' => $itineraryId]) }}" class="btn-book-now btn-ghost-gold">
                            {{ __('frontend::common.book_now') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
