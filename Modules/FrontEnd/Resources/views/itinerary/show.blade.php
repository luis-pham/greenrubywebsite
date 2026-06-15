@php
    use Carbon\Carbon;
    use Modules\BackEnd\Helpers\Utilities;
@endphp
@extends('frontend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');

    $obj->guests = $listCabin->sum(fn($cabin) => $cabin->capacity);
    $listAccommodationCabin = $listCabin->filter(fn($cabin) => collect(config('backend.listAccommodationSlug'))->contains($cabin->slug) )->values();

    $listButton = [];
    $btn = new stdClass();
    $btn->label = __('frontend::itineraryDetail.check_availability');
    $btn->class = 'btn-warning btn-check-availability';
    $btn->url = 'javascript:';

    $listButton[] = $btn;

    $listBanner = [];
    $listBanner[0] = new stdClass();
    $listBanner[0]->title = $obj->itinerary->name;
    $listBanner[0]->description = $obj->itinerary->description;
    $listBanner[0]->link = $obj->itinerary->cover_link;
    $listBanner[0]->listButton = $listButton;

    $listCallToActionBtn = [];
    $btn = [];
    $btn['label'] = __('frontend::itineraryDetail.check_availability');
    $btn['class'] = 'btn-success btn-check-availability';
    $btn['url'] = 'javascript:';
    $listCallToActionBtn[] = $btn;

    $btn = [];
    $btn['label'] = __('frontend::common.book_now');
    $btn['class'] = 'btn-warning';
    $btn['url'] = route(Utilities::getRouteName('frontend.booking'),['languageCode' => $languageCode,'itinerary_id' => $obj->itinerary_id,'cruise_id' => $obj->cruise_id]);
    $listCallToActionBtn[] = $btn;

    $departureDate = $itineraries->map(function ($item) {
        return $item->start_at;
    })->values()->toArray();

@endphp

@section('content')
    <div id="itinerary-detail">
        @include('frontend::shared.section.section-cover',[
           'class' => 'section-cover-sm',
           'tagHeading' => 'h1',
           'list' => $listBanner,
           'listBreadCrumb' => $listBreadCrumb,
           'imageConfig' => ['w' => 1920, 'h' => 466]
       ])
        <section class="section-info bg bg-tender-white">
            <div class="container-fluid">
                <div class="container">
                    <div class="container-info">
                        <div class="info-bar-detail">
                            <div class="info-bar-detail-wrapper">

                                <div class="info-item-detail">
                                    <div class="info-wrapper-detail">
                                        <div class="icon-wrap-detail">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#C8A84B" stroke-width="1.25" stroke-linecap="round" aria-hidden="true">
                                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                                <line x1="3" y1="9" x2="21" y2="9"/>
                                                <line x1="8" y1="2" x2="8" y2="6"/>
                                                <line x1="16" y1="2" x2="16" y2="6"/>
                                            </svg>
                                        </div>
                                        <div class="info-right-detail">
                                            <span class="detail-label">{{ __('frontend::itineraryDetail.section-info-duration') }}</span>
                                            <span class="detail-title">{{ FeCruiseUtils::formatDisplayDurationName($obj->itinerary->duration) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="info-item-detail">
                                    <div class="info-wrapper-detail">
                                        <div class="icon-wrap-detail">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#C8A84B" stroke-width="1.25" stroke-linecap="round" aria-hidden="true">
                                                <path d="M12 2C8 2 4 5.5 4 10c0 6 8 12 8 12s8-6 8-12c0-4.5-4-8-8-8z"/>
                                                <circle cx="12" cy="10" r="2"/>
                                            </svg>
                                        </div>
                                        <div class="info-right-detail">
                                            <span class="detail-label">{{ __('frontend::itineraryDetail.section-info-departure') }}</span>
                                            <span class="detail-title">{{ FeCruiseUtils::getItineraryDeparture($obj->itinerary->destination) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="info-item-detail">
                                    <div class="info-wrapper-detail">
                                        <div class="icon-wrap-detail">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#C8A84B" stroke-width="1.25" stroke-linecap="round" aria-hidden="true">
                                                <path d="M3 17h18M5 17V9a2 2 0 012-2h10a2 2 0 012 2v8"/>
                                                <path d="M9 17V12h6v5"/>
                                                <path d="M12 7V5"/>
                                            </svg>
                                        </div>
                                        <div class="info-right-detail">
                                            <span class="detail-label">{{ __('frontend::itineraryDetail.section-info-cruise') }}</span>
                                            <span class="detail-title">{{ $obj->cruise->name }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="info-item-detail info-item-detail--price">
                                    <div class="info-wrapper-detail">
                                        <div class="info-right-detail">
                                            <span class="detail-label">{{ __('frontend::itineraryDetail.from') }}</span>
                                            <span class="detail-title detail-price">{{ FeUtils::formatDisplayCurrency($obj->price) }}<span class="detail-per"> / {{ __('frontend::itineraryDetail.person') }}</span></span>
                                        </div>
                                    </div>
                                    <a href="{{ route(Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode, 'itinerary_id' => $obj->itinerary_id, 'cruise_id' => $obj->cruise_id]) }}"
                                       class="btn-book-detail">
                                        {{ __('frontend::common.book_now') }}
                                        <i class="ml-2 fas fa-calendar-check" aria-hidden="true"></i>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="slide-1">
                        <div class="service-important-note-container owl-carousel owl-theme">
                            <div class="list-container list-inclusive-service-container">
                                <div class="header m-0 header--included">
                                    <i class="ti ti-circle-check" aria-hidden="true"></i>
                                    <span>{{__('frontend::itineraryDetail.section-info-inclusion')}}</span>
                                </div>
                                <div class="body">
                                    <ul class="list">
                                        @foreach(__('frontend::itineraryDetail.inclusion_items') as $item)
                                            <li class="item item--text">
                                                <p>{{ $item }}</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <div class="list-container list-exclusive-service-container">
                                <div class="header m-0 header--excluded">
                                    <i class="ti ti-circle-minus" aria-hidden="true"></i>
                                    <span>{{ __('frontend::itineraryDetail.section-info-exclusion') }}</span>
                                </div>
                                <div class="body">
                                    <ul class="list">
                                        @foreach(__('frontend::itineraryDetail.exclusion_items') as $item)
                                            <li class="item item--text">
                                                <p>{{ $item }}</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <div class="list-container list-activity-container">
                                <div class="header m-0 header--notes">
                                    <i class="ti ti-info-circle" aria-hidden="true"></i>
                                    <span>{{ __('frontend::itineraryDetail.section-info-important-notes') }}</span>
                                </div>
                                <div class="body">
                                    <ul class="list m-0">
                                        @foreach(__('frontend::itineraryDetail.important_note_items') as $item)
                                            <li class="item item--text">
                                                <p>{{ $item }}</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="section-detail bg">
            <div class="container-fluid">
                <div class="container">
                    <p class="section-eyebrow section-eyebrow--gold">{{ __('frontend::itineraryDetail.section-details-title') }}</p>
                    <p class="section-description font-heading">{{$obj->itinerary->name}}</p>
                    <div class="list-detail">
                        @foreach($obj->itinerary->itineraryDays as $day)
                            @include('frontend::itinerary.partials.itineraryDayDetails',[
                                'day' => $day,
                                'listDetail' => $day->itineraryDayDetails,
                                'expanded' => $loop->first,
                            ])
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        @include('frontend::shared.section.section-cabin',[
            'title' => __('frontend::itineraryDetail.section-cabin-title'),
            'description' => __('frontend::itineraryDetail.section-cabin-description'),
            'list' => $listAccommodationCabin,
            'suitesGrid' => true,
            'cruiseId' => $obj->cruise_id,
            'itineraryId' => $obj->itinerary_id,
        ])

        @include('frontend::shared.section.section-gallery',[
            'title' => __('frontend::itineraryDetail.section-gallery-title'),
            'description' =>__('frontend::itineraryDetail.section-gallery-description'),
            'list' => $obj->itinerary->galleryImages,
            'titleClass' => 'section-eyebrow section-eyebrow--gold',
            'tagHeading' => 'p',
        ])

        @include('frontend::shared.section.section-testimonial',[
            'list' => $listTestimonial
        ])

        @include('frontend::shared.section.section-call-to-action',[
            'description' => __('frontend::itineraryDetail.section-booking-and-availability-title'),
            'content' => FeCruiseUtils::getItineraryDeparture($obj->itinerary->destination) . ' · ' . $obj->cruise->name . ' · ' . __('frontend::itineraryDetail.section-booking-departing_from', ['port' => 'Tuần Châu']),
            'buttons' => $listCallToActionBtn
        ])

        <section class="section-policy bg">
            <div class="container-fluid">
                <div class="container">
                    <div
                        class="grid-policy d-flex justify-content-start justify-content-md-around p-md-3 p-4 flex-wrap flex-column flex-md-row">
                        <div class="item guarantee">
                            <div class="icon">
                                <i class="fas fa-dollar-sign fa-lg"></i>
                            </div>
                            <span>{{__('frontend::itineraryDetail.section-booking-and-availability-guarantee')}}</span>
                        </div>
                        <div class="item cancellation">
                            <div class="icon">
                                <i class="fas fa-xmark fa-lg"></i>
                            </div>
                            <span>{{__('frontend::itineraryDetail.section-booking-and-availability-cancellation')}}</span>
                        </div>
                        <div class="item support">
                            <div class="icon">
                                <i class="fas fa-headset fa-lg"></i>
                            </div>
                            <span>{{__('frontend::itineraryDetail.section-booking-and-availability-support')}}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @php
        $departureRoutes = $itineraries
            ->filter(fn ($item) => filled($item->start_at))
            ->mapWithKeys(function ($item) use ($languageCode) {
                return [
                    $item->start_at => route(Utilities::getRouteName('frontend.booking'), [
                        'languageCode' => $languageCode,
                        'cruise_id' => $item->cruise_id,
                        'itinerary_id' => $item->itinerary_id,
                        'start_at' => Carbon::createFromFormat('Y-m-d', $item->start_at)->format('d-m-Y'),
                    ]),
                ];
            });
    @endphp
    <script>
        window.departureDate = @json($departureDate);
        window.languageCode = @json($languageCode);
        window.departureRoutes = @json($departureRoutes)
    </script>
    @php
        $itinerary = $obj->itinerary;
        $itineraryDays = $itinerary->itineraryDays;
        $destinations = json_decode($itinerary->destination, true) ?? [];
        $startDate = $obj->start_at;
        $schemaData = [
            '@context' => 'https://schema.org',
            '@type' => 'TouristTrip',
            'name' => html_entity_decode($itinerary->name, ENT_QUOTES, 'UTF-8'),
            'description' => html_entity_decode($itinerary->description, ENT_QUOTES, 'UTF-8'),
            'itinerary' => [
                '@type' => 'ItemList',
                'itemListElement' => $itineraryDays->map(function ($day) use ($obj, $destinations) {
                    $idx = $day->day - 1;
                    $dayDate = Carbon::parse($obj->start)->addDays($day->day - 1)->toDateString();
                    $location = count($destinations) > $idx ? html_entity_decode($destinations[$idx], ENT_QUOTES, 'UTF-8') : '';

                    return [
                        '@type' => 'ListItem',
                        'position' => $day->day,
                        'name' => 'Day ' . $day->day,
                        'item' => [
                            '@type' => 'Event',
                            'name' => 'Day ' . $day->day,
                            'startDate' => $dayDate,
                            'location' => [
                                '@type' => 'Place',
                                'name' => $location,
                            ],
                            'subEvent' => $day->itineraryDayDetails->map(function ($detail) use ($dayDate, $location) {
                                return [
                                    '@type' => 'Event',
                                    'name' => html_entity_decode($detail->title, ENT_QUOTES, 'UTF-8'),
                                    'description' => html_entity_decode($detail->description, ENT_QUOTES, 'UTF-8'),
                                    'startTime' => $detail->time,
                                    'startDate' => $dayDate,
                                    'location' => [
                                        '@type' => 'Place',
                                        'name' => $location,
                                    ],
                                ];
                            })->values()->all(),
                        ],
                    ];
                })->values()->all(),
            ],
            'touristDestination' => collect($destinations)->map(function ($dest) {
                return [
                    '@type' => 'TouristDestination',
                    'name' => html_entity_decode($dest, ENT_QUOTES, 'UTF-8'),
                ];
            })->values()->all(),
            'duration' => 'P' . $itinerary->duration . 'D',
            'startDate' => $startDate,
        ];
    @endphp

    <script type="application/ld+json">
        {!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
@endpush
