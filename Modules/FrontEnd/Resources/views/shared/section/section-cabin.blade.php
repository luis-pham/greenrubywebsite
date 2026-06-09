@php
$class = isset($class) ? $class : '';
$title = isset($title) ? $title : '';
$description = isset($description) ? $description : '';
$list = isset($list) ? $list : [];
$filters = isset($filters) && count($filters) > 0 ? $filters : [];
$languageCode = Route::current()->parameter('languageCode');
$isShowBookNow = $isShowBookNow ?? true;
$suitesGrid = $suitesGrid ?? false;
@endphp

<section class="{{ $class }} section-cabin bg bg-azure">
    <div class="container-fluid">
        <div class="container">
            @if ($title)
                <h2 class="section-title{{ $suitesGrid ? ' suite-section-eyebrow' : '' }}">{{ $title }}</h2>
            @endif
            @if ($description)
                <p class="section-description font-heading{{ $suitesGrid ? ' suite-section-title' : '' }}">{{ $description }}</p>
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
                                $listSpecification = [];
                                $room = '';
                                // if (isset($list[$i]->room_count) && count($list[$i]->room_count) > 0) {
                                //     for ($j = 0; $j < count($list[$i]->room_count); $j++) {
                                //         $room .= $list[$i]->room_count[$j]->count_room . ' ' . $list[$i]->room_count[$j]->title;
                                //         if ($j < count($list[$i]->room_count) - 1) {
                                //             $room .= ', ';
                                //         }
                                //     }
                                // }
                                if (isset($list[$i]->room) && count($list[$i]->room) > 0) {
                                    $room = $list[$i]->room->implode('title', ', ');
                                }
                                if ($room) {
                                    $listSpecification[] = [
                                        'icon' => 'fa-solid fa-inbox',
                                        'title' => $room
                                    ];
                                }
                                if ($list[$i]->view) {
                                    $listSpecification[] = [
                                        'icon' => 'fa-solid fa-eye',
                                        'title' => $list[$i]->view
                                    ];
                                }
                                if ($list[$i]->area) {
                                    $listSpecification[] = [
                                        'icon' => 'fa-solid fa-arrows-up-down-left-right',
                                        'title' => $list[$i]->area . 'm²'
                                    ];
                                }
                                if ($list[$i]->capacity) {
                                    $listSpecification[] = [
                                        'icon' => 'fa-solid fa-user',
                                        'title' => $list[$i]->capacity . ' ' . (__($list[$i]->capacity <= 1 ? 'frontend::common.guest' : 'frontend::common.guest_plural'))
                                    ];
                                }
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
                                        @if ($suitesGrid && $list[$i]->area)
                                            <span class="suite-size-badge">{{ $list[$i]->area }}m²</span>
                                        @endif
                                    </div>
                                    <div class="item-body{{ $suitesGrid ? ' suite-card-content' : '' }}">
                                        <h3 class="title mb-2 font-heading give-ellipsis after-2-lines{{ $suitesGrid ? ' suite-card-name' : '' }}">
                                            <a href="javascript:;" class="btn-view-cabin-details text-reset" data-id="{{ $list[$i]->id }}">{{ $list[$i]->name }}</a>
                                        </h3>
                                        <p class="description text-break give-ellipsis after-2-lines{{ $suitesGrid ? ' suite-card-desc' : '' }}">{{ $list[$i]->summary }}</p>
                                        @if (count($listSpecification) > 0)
                                            <div class="list-specification mb-3{{ $suitesGrid ? ' suite-specs' : '' }}">
                                                @for ($j = 0; $j < count($listSpecification); $j++)
                                                    <div class="item-specification media{{ $suitesGrid ? ' suite-spec-item' : '' }}">
                                                        <div class="icon mr-2">
                                                            <i class="{{ $listSpecification[$j]['icon'] }}"></i>
                                                        </div>
                                                        <div class="media-body">
                                                            <p class="mb-0 suite-spec-text">{{ $listSpecification[$j]['title'] }}</p>
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
                                        @endif
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
