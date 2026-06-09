@extends('frontend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');
    $cruiseDisplayName = FeUtils::formatGreenRubyMenuName($obj->name);

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
    $banner->title = $cruiseDisplayName;
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
    $listCabinFilter = $listAccommodationCabin
                ->filter(fn($item) => $item->cabin_class !== null && $item->cabin_class !== "")
                ->map(fn($item) => $item->cabin_class)
                ->unique()
                ->values()
                ->toArray();
@endphp

@section('content')
    <div id="cruise-detail">
        @include('frontend::shared.section.section-cover',[
            'list' => $listBanner,
            'tagHeading' => 'h1',
            'imageConfig' => ['w' => 1920, 'h' => 848]
        ])

        <section class="section-info bg">
            <div class="container-fluid">
                <div class="container">
                    <h2 class="section-title">{{__('frontend::cruiseDetail.section-info.title')}}</h2>
                    <p class="section-description font-heading">{{__('frontend::cruiseDetail.section-info.description', ['name' => $cruiseDisplayName])}}</p>
                    <div class="grid-info">
                        <div class="item">
                            <p class="length">{{ $obj->dimension_length }}</p>
                            {{ __('frontend::cruiseDetail.section-info.length') }}
                        </div>
                        <div class="item">
                            <p class="length">46</p>
                            {{ __('frontend::cruiseDetail.section-info.cabin') }}
                        </div>
                        <div class="item">
                            <p class="length">{{ $obj->capacity }}</p>
                            {{ __('frontend::cruiseDetail.section-info.guests') }}
                        </div>
                        <div class="item">
                            <p class="length">{{ $obj->year_built }}</p>
                            {{ __('frontend::cruiseDetail.section-info.year-of-manufacture') }}
                        </div>
                    </div>
                    <div class="green-technology-container">
                        <img class="icon" src="{{FeUtils::getImageLink($obj->green_technology->image_link ?? null)}}" alt="Icon"/>
                        <p class="name">{{$obj->green_technology->name ?? ""}}</p>
                        <span class="description">{{$obj->green_technology->description ?? ""}}</span>
                    </div>
                </div>
            </div>
        </section>

        @include('frontend::shared.section.section-vr-360',[
            'list' => $obj->listVr,
            'title' => __('frontend::cruiseDetail.section-vr-360.title'),
            'description' => __('frontend::cruiseDetail.section-vr-360.description')
        ])

        <section class="section-cabin-and-service bg">
            <div class="container-fluid p-0">
                <div class="row no-gutters">
                    <div class="col-12 col-lg-6 block-1">
                        <div class="cabin-container accordion-item">
                            <div class="header">
                                <span class="title">{{__('frontend::cruiseDetail.section-cabin-and-service.cabin-container-title')}}</span>
                                <div class="icon-wrapper">
                                    <i class="icon fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="body">
                                <div class="item-grid grid-cabin">
                                    @foreach($listOtherCabin as $cabin)
                                        <div class="item {{$loop->index === 0 ? 'active-1 active-2' : ''}}" data-id="{{$cabin->id}}">
                                            <span class="name">{{$cabin->name}}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="cabin-gallery-container position-relative d-lg-none"></div>
                            </div>
                        </div>
                        <div class="service-container accordion-item item-collapse">
                            <div class="header">
                                <span class="title">{{__('frontend::cruiseDetail.section-cabin-and-service.service-container-title')}}</span>
                                <div class="icon-wrapper">
                                    <i class="icon fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="body">
                                <div class="item-grid grid-service">
                                    @foreach($listInclusiveService as $service)
                                        <div class="item {{$loop->index === 0 ? 'active-1' : ''}}" data-id="{{$service->id}}">
                                            <span class="name">{{$service->name}}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="service-gallery-container d-lg-none"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6 block-2 d-none d-lg-block">
                        <div class="cabin-gallery-container service-gallery-container"></div>
                    </div>
                </div>
            </div>
        </section>

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

        @include('frontend::shared.section.section-cabin',[
            'title' => __('frontend::cruiseDetail.section-cabin.title'),
            'description' => __('frontend::cruiseDetail.section-cabin.description'),
            'list' => $listAccommodationCabin,
            'filters' => $listCabinFilter,
            'isShowBookNow' => false
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
        let apiAppCabin = {
            getById: '{{ route(Utilities::getRouteName('frontend.api.cabin.getById'), ['languageCode' => $languageCode]) }}'
        };
        let apiAppService = {
            getById: "{{ route(Utilities::getRouteName('frontend.api.service.getById'), ['languageCode' => $languageCode]) }}"
        };
    </script>
@endpush
