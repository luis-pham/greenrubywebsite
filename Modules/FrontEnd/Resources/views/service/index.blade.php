@extends('frontend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');

    $listBanner = [];
    $listBanner[0] = new stdClass();
    $listBanner[0]->title = __('frontend::service.section_1_title');
    $listBanner[0]->description = __('frontend::service.section_1_description');
    $listBanner[0]->link = asset('assets/frontend/images/modules/service/service-cover.jpg');

    $listService = isset($pageConfig[PageConfigKeyConsts::SERVICE_SERVICE])
        ? $pageConfig[PageConfigKeyConsts::SERVICE_SERVICE]
        : [];

    $readyToSailDescription = isset($pageConfig[PageConfigKeyConsts::SERVICE_READY_TO_SAIL_DESCRIPTION])
        ? $pageConfig[PageConfigKeyConsts::SERVICE_READY_TO_SAIL_DESCRIPTION]
        : '';
    $readyToSailContent = isset($pageConfig[PageConfigKeyConsts::SERVICE_READY_TO_SAIL_CONTENT])
        ? $pageConfig[PageConfigKeyConsts::SERVICE_READY_TO_SAIL_CONTENT]
        : '';

    $groupedServices = collect($listService)->groupBy('group_id');
@endphp

@section('content')
    <div id="service">
        @include('frontend::shared.section.section-cover', [
            'class' => 'section-1 section-cover-sm',
            'list' => $listBanner,
            'tagHeading' => 'h1',
            'imageConfig' => ['w' => 1920, 'h' => 466],
        ])

        <section class="section-2 section-grid-service">
            <div class="gallery-filter-sticky">
                <div class="container-fluid px-0">
                    <div class="gallery-filter-inner">
                        <div class="container">
                            <div class="gallery-filter-bar list-filter">
                                <button type="button"
                                        class="item gallery-filter-tab active"
                                        data-groups="[]">
                                    {{ __('frontend::common.all') }}
                                </button>
                                @foreach ($listGroup as $groupId => $groupName)
                                    <button type="button"
                                            class="item gallery-filter-tab"
                                            data-groups='["{{ $groupId }}"]'>
                                        {{ $groupName }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (count($listService) > 0)
                <div class="gallery-wrapper container-fluid px-0">
                    <div class="container">
                        <div class="service-grid-section">
                            @foreach ($listGroup as $groupId => $groupName)
                                @if (isset($groupedServices[$groupId]) && $groupedServices[$groupId]->count())
                                    @php
                                        $groupCount = $groupedServices[$groupId]->count();
                                        $colClass = $groupCount <= 2 ? '2' : ($groupCount >= 4 ? '4' : '3');
                                    @endphp
                                    <div class="service-group" data-group="{{ $groupId }}">
                                        <p class="service-group-label">{{ $groupName }}</p>

                                        <div class="service-cards-grid service-cols-{{ $colClass }}">
                                            @foreach ($groupedServices[$groupId] as $service)
                                                <div class="service-card">
                                                    <div class="service-card-img">
                                                        @include('frontend::shared.image-wrapper', [
                                                            'link' => $service->image_link,
                                                            'alt' => $service->name,
                                                            'imageConfig' => ['w' => 600, 'h' => 400, 'cr' => 1],
                                                        ])
                                                        <span class="service-badge service-badge--{{ $service->type == 1 ? 'free' : 'premium' }}">
                                                            {{ $service->type == 1 ? 'Complimentary' : 'Premium' }}
                                                        </span>
                                                    </div>
                                                    <div class="service-card-body">
                                                        <h3 class="service-card-name font-heading">{{ $service->name }}</h3>
                                                        <p class="service-card-desc">{{ $service->description }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </section>

        @include('frontend::shared.section.section-call-to-action', [
            'class' => 'section-6',
            'description' => $readyToSailDescription,
            'content' => $readyToSailContent,
            'buttons' => [[
                'label' => __('frontend::service.button_book_now'),
                'class' => 'btn-warning',
                'url' => route(Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode]),
            ]],
        ])
    </div>
@endsection

@push('scripts')
    @include('frontend::shared.structured-data-webpage')
@endpush
