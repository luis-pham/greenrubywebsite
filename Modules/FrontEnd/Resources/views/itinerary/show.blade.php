@php
    use Carbon\Carbon;
    use Modules\BackEnd\Helpers\Utilities;
@endphp
@extends('frontend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');

    $obj->inclusiveServices = $obj->itinerary->itineraryServices->filter(fn($s) => $s->type == config('backend.appServiceType.inclusive'));
    $obj->exclusiveServices = $obj->itinerary->itineraryServices->filter(fn($s) => $s->type == config('backend.appServiceType.exclusive'));
    $obj->importantNote = $obj->itinerary->important_note;
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
                        <div class="cruise-info">
                            <div class="box">
                                <p class="label">{{ __('frontend::itineraryDetail.section-info-duration') }}</p>
                                <p class="value">{{FeCruiseUtils::formatDisplayDurationName($obj->itinerary->duration)}}</p>
                            </div>
                            <div class="box">
                                <p class="label">{{ __('frontend::itineraryDetail.section-info-departure') }}</p>
                                <p class="value">{{FeCruiseUtils::getItineraryDeparture($obj->itinerary->destination)}}</p>
                            </div>
                            <div class="box">
                                <p class="label">{{ __('frontend::itineraryDetail.section-info-cruise') }}</p>
                                <p class="value">{{$obj->cruise->name}}</p>
                            </div>
                            <div class="box">
                                <p class="label">{{ __('frontend::itineraryDetail.section-info-guests') }}</p>
                                <p class="value">{{__('frontend::itineraryDetail.max')}} {{$obj->guests}}</p>
                            </div>
                        </div>
                        <div class="price-box d-flex justify-content-center align-items-center flex-column flex-xl-row">
                            <p class="text mb-0 mb-xl-2">
                                {{ __('frontend::itineraryDetail.from') }} <b
                                    class="price">{{FeUtils::formatDisplayCurrency($obj->price)}}
                                    /</b>{{ __('frontend::itineraryDetail.person') }}
                            </p>
                            <a
                                href="{{route(Utilities::getRouteName('frontend.booking'),['languageCode' => $languageCode,'itinerary_id' => $obj->itinerary_id,'cruise_id' => $obj->cruise_id])}}"
                                class="btn btn-warning px-3 py-2">
                                {{ __('frontend::common.book_now') }} <i class="ml-2 fas fa-calendar-check"></i>
                            </a>
                        </div>
                    </div>
                    <div class="slide-1">
                        <div class="service-important-note-container owl-carousel owl-theme">
                            <div class="list-container list-inclusive-service-container">
                                <div class="header m-0">{{__('frontend::itineraryDetail.section-info-inclusion')}}</div>
                                <div class="body">
                                    <ul class="list">
                                        @foreach($obj->inclusiveServices as $service)
                                            <li class="item">
                                                <img src="{{FeUtils::getThumbnail(['link' => $service->image_link,'w' => 24,'h' => 24])}}"/>
                                                <p>{{$service->name}}</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <div class="list-container list-exclusive-service-container">
                                <div class="header m-0">{{__('frontend::itineraryDetail.section-info-exclusion')}}</div>
                                <div class="body">
                                    <ul class="list">
                                        @foreach($obj->exclusiveServices as $service)
                                            <li class="item">
                                                <img
                                                    src="{{FeUtils::getThumbnail(['link' => $service->image_link,'w' => 24,'h' => 24])}}"/>
                                                <p>{{$service->name}}</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <div class="list-container list-activity-container">
                                <div
                                    class="header m-0">{{__('frontend::itineraryDetail.section-info-important-notes')}}</div>
                                <div class="body">
                                    <ul class="list m-0">
                                        @foreach($obj->importantNote ?? [] as $note)
                                            @php
                                                $note = (object) $note
                                            @endphp
                                            <li class="item">
                                                <img
                                                    src="{{FeUtils::getThumbnail(['link' => $note->image_link,'w' => 24,'h' => 24])}}"/>
                                                {!! $note->content !!}
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
                    <h2 class="section-title">{{ __('frontend::itineraryDetail.section-details-title') }}</h2>
                    <p class="section-description font-heading">{{$obj->itinerary->name}}</p>
                    <div class="list-detail">
                        @foreach($obj->itinerary->itineraryDays as $day)
                            @include('frontend::itinerary.partials.itineraryDayDetails',[
                                'day' => $day,
                                'listDetail' => $day->itineraryDayDetails
                            ])
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        @include('frontend::shared.section.section-cabin',[
            'title' => 'Private Sanctuaries',
            'description' => 'Luxury Suites',
            'list' => $listAccommodationCabin,
            'isShowBookNow' => false
        ])

        @include('frontend::shared.section.section-gallery',[
            'title' => __('frontend::itineraryDetail.section-gallery-title'),
            'description' =>__('frontend::itineraryDetail.section-gallery-description'),
            'list' => $obj->itinerary->galleryImages
        ])

        @include('frontend::shared.section.section-testimonial',[
            'list' => $listTestimonial
        ])

        @include('frontend::shared.section.section-call-to-action',[
            'description' => __('frontend::itineraryDetail.section-booking-and-availability-title',['name' => FeCruiseUtils::formatDisplayItineraryBay($obj->itinerary->bay)]),
            'content' => __('frontend::itineraryDetail.section-booking-and-availability-description'),
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
        $startDate = Carbon::parse($itinerary->created_at)->toDateString();
        $name = html_entity_decode($itinerary->name, ENT_QUOTES, 'UTF-8');
        $description = html_entity_decode($itinerary->description, ENT_QUOTES, 'UTF-8');

        $startDate = $obj->start_at;
    @endphp

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "TouristTrip",
        "name": "{!! $name !!}",
        "description": "{!! $description !!}",
        "itinerary": {
            "@type": "ItemList",
            "itemListElement": [
                @foreach($itineraryDays as $idx => $day)
                    @php
                        $dayDate = Carbon::parse($obj->start)->addDays($day->day - 1)->toDateString();
                        $location = count($destinations) > $idx ? $destinations[$idx] : "";
                    @endphp
                {
                    "@type": "ListItem",
                    "position": {{ $day->day }},
                    "name": "Day {{ $day->day }}",
                    "item": {
                        "@type": "Event",
                        "name": "Day {{ $day->day }}",
                        "startDate": "{{$dayDate}}",
                        "location": {
                            "@type": "Place",
                            "name": "{!! html_entity_decode($location, ENT_QUOTES, 'UTF-8') !!}"
                        },
                        "subEvent": [
                            @foreach($day->itineraryDayDetails as $detail)
                            {
                                "@type": "Event",
                                "name": "{!! html_entity_decode($detail->title, ENT_QUOTES, 'UTF-8') !!}",
                                "description": "{!! html_entity_decode($detail->description, ENT_QUOTES, 'UTF-8') !!}",
                                "startTime": "{{ $detail->time }}",
                                "startDate": "{{$dayDate}}",
                                "location": {
                                    "@type": "Place",
                                    "name": "{!! html_entity_decode($location, ENT_QUOTES, 'UTF-8') !!}"
                                }
                            }{{ !$loop->last ? ',' : '' }}
                            @endforeach
                        ]
                    }
                }{{ !$loop->last ? ',' : '' }}
                @endforeach
            ]
        },
        "touristDestination": [
            @foreach($destinations as $dest)
            {
                "@type": "TouristDestination",
                "name": "{!! html_entity_decode($dest, ENT_QUOTES, 'UTF-8') !!}"
            }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ],
        "duration": "P{{ $itinerary->duration }}D",
        "startDate": "{{ $startDate }}"
    }
    </script>
@endpush
