@php
    $class = isset($class) ? $class : '';
    $title = isset($title) ? $title : '';
    $subTitle = isset($subTitle) ? $subTitle : '';
    $list = isset($list) && count($list) > 0 ? $list : [];
    $tabButtons = isset($tabButtons) ? $tabButtons : [];
    $backgroundImage = isset($backgroundImage) ? $backgroundImage : null;
    $tagHeading = isset($tagHeading) ? $tagHeading : 'p';
    $titleClass = $titleClass ?? 'section-title';
    $languageCode = Route::current()->parameter('languageCode');
@endphp

<section class="{{ $class }} section-experience bg {{ !$backgroundImage ? 'bg-tender-white' : '' }}">
    <div class="container-fluid px-0"
         @if ($backgroundImage)
             style="background-image: linear-gradient(to bottom, #00000080 50%, white 50%), url('{{ FeUtils::getImageLink($backgroundImage) }}');"
         @endif
    >
        <div class="container"> 
            @if ($title)
                <{{ $tagHeading }} class="{{ $titleClass }}">{{ $title }}</{{ $tagHeading }}>
            @endif

            @if ($subTitle)
                <p class="section-description font-heading {{ $backgroundImage ? 'text-white' : '' }}">
                    {!! safe_html($subTitle) !!}
                </p>
            @endif

            @if (count($tabButtons))
                <div class="filter">
                    <div class="list-item d-flex justify-content-center">
                        @foreach ($tabButtons as $tab)
                            <div class="item">
                                <button type="button"
                                        class="font-weight-bold text-white border-0 {{ $loop->first ? 'active' : '' }}"
                                        data-groups="{{ json_encode($tab['ids']) }}">
                                    {{ $tab['name'] }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            @if (count($list) > 0)
            <div class="itinerary-cruise">
                <div class="list-itinerary-cruise list-item row d-none d-lg-flex justify-content-center">
                    @foreach ($list as $activity)
                        <div class="col-12 col-sm-6 col-lg-3 d-flex" data-group="{{ $activity->group_id ?? '' }}">
                            <div class="item d-flex h-100 bg-white">
                                <div class="item-wrapper d-flex flex-column w-100 h-100">
                                    <div class="item-header">
                                        <a href="{{ route(Utilities::getRouteName('frontend.experience.show'), ['languageCode' => $languageCode, 'slug' => Utilities::convertToAlias($activity->name), 'id' => $activity->id]) }}">
                                            @include('frontend::shared.image-wrapper', [
                                                'link' => $activity->image_link,
                                                'alt'  => $activity->name, 
                                                'imageConfig' => ['w' => 332, 'h' => 410],
                                            ])
                                        </a>
                                    </div>

                                    <div class="item-body ">
                                        <h3 class="title font-heading give-ellipsis after-1-lines">
                                            <a href="{{ route(Utilities::getRouteName('frontend.experience.show'), ['languageCode' => $languageCode, 'slug' => Utilities::convertToAlias($activity->name), 'id' => $activity->id]) }}" class="text-reset">
                                                {{ $activity->name }}
                                            </a>
                                        </h3>

                                        <div class="sub-title d-flex flex-column flex-wrap ">
                                            @if (!empty($activity->summary))
                                                <p class="description give-ellipsis after-2-lines d-block">
                                                    <span>{{ $activity->summary }}</span>
                                                </p>
                                            @endif
                                            @if (!empty($activity->note))
                                                @php
                                                    $notes = json_decode($activity->note, true);
                                                @endphp
                                                <ul class="mb-0">
                                                    @if(!empty($notes))
                                                        @foreach ($notes as $note)
                                                            <li>{{ $note }}</li>
                                                        @endforeach
                                                    @endif
                                                    @if (!empty($activity->duration))
                                                        <li>{{ $activity->duration }} {{ __('frontend::experienceDetail.time_minutes') }}</li>
                                                    @endif   
                                                </ul>
                                            @endif
                                        </div>                        
                                    </div>
                                    <div class="item-footer">
                                        <hr />
                                        <div class="d-flex justify-content-center align-items-center">
                                            <div class="">
                                                <a href="{{ route(
                                                    Utilities::getRouteName('frontend.experience.show'),
                                                    ['languageCode' => $languageCode, 'slug' => Utilities::convertToAlias($activity->name), 'id' => $activity->id]
                                                ) }}"
                                               class="btn-view-details d-block">
                                                {{ __('frontend::common.button_view_details') }}
                                            </a>
                                            </div>
                                        </div>
                                    </div>
                                </div> 
                            </div> 
                        </div> 
                    @endforeach
                </div> 
            </div> 
            <div class="slide-1 d-block d-lg-none mt-4">
                <div class="list-itinerary-cruise owl-carousel owl-theme">
                    @foreach ($list as $activity)
                        <div class="item" data-group="{{ $activity->group_id ?? '' }}">
                            <div class="d-flex h-100 bg-white">
                                <div class="item-wrapper d-flex flex-column w-100 h-100">
                                    <div class="item-header">
                                        <a href="{{ route(
                                            Utilities::getRouteName('frontend.experience.show'),
                                            ['languageCode' => $languageCode, 'slug' => Utilities::convertToAlias($activity->name), 'id' => $activity->id]
                                        ) }}">
                                            @include('frontend::shared.image-wrapper', [
                                                'link' => $activity->image_link,
                                                'alt'  => $activity->name,
                                            ])
                                        </a>
                                    </div>
                                    <div class="item-body ">
                                        <h3 class="title font-heading give-ellipsis after-1-lines">
                                            <a href="{{ route(Utilities::getRouteName('frontend.experience.show'), ['languageCode' => $languageCode, 'slug' => Utilities::convertToAlias($activity->name), 'id' => $activity->id]) }}" class="text-reset">
                                                {{ $activity->name }}
                                            </a>
                                        </h3>

                                        <div class="sub-title d-flex flex-column flex-wrap">
                                            @if (!empty($activity->summary))
                                                <p class="description give-ellipsis after-2-lines d-block">
                                                    <span>{{ $activity->summary }}</span>
                                                </p>
                                            @endif
                                                @php
                                                    $notes = json_decode($activity->note, true);
                                                @endphp
                                                <ul class="mb-0">
                                                    @if(!empty($notes))
                                                        <li>{{ is_array($notes) ? implode(', ', $notes) : '' }}</li>
                                                    @endif
                                                    @if (!empty($activity->duration))
                                                        <li>{{ $activity->duration }} {{ __('frontend::experienceDetail.time_minutes') }}</li>
                                                    @endif   
                                                </ul>
                                        </div>                        
                                    </div>
                                    <div class="item-footer">
                                        <hr />
                                        <div class="d-flex justify-content-center align-items-center">
                                            <div class="">
                                                <a href="{{ route(
                                                    Utilities::getRouteName('frontend.experience.show'),
                                                    ['languageCode' => $languageCode, 'slug' => Utilities::convertToAlias($activity->name), 'id' => $activity->id]
                                                ) }}"
                                               class="btn-view-details d-block">
                                                {{ __('frontend::common.button_view_details') }}
                                            </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</section>