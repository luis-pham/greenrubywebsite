@php
use Modules\FrontEnd\Helpers\FeLanguageUtils;
use Modules\BackEnd\Services\AppAmenityService;

$class = isset($class) ? $class : '';
$title = isset($title) ? $title : '';
$description = isset($description) ? $description : '';
$titleClass = $titleClass ?? 'section-title';
$tagHeading = $tagHeading ?? 'h2';
if (!isset($list)) {
    $currentLanguage = FeLanguageUtils::getCurrentLanguage();
    $list = AppAmenityService::getAll($currentLanguage->id);
}
@endphp

<section class="{{ $class }} section-amenity bg bg-tender-white">
    <div class="container-fluid">
        <div class="container">
            @if ($title)
                <{{ $tagHeading }} class="{{ $titleClass }}">{{ $title }}</{{ $tagHeading }}>
            @endif
            @if ($description)
                <p class="section-description font-heading">{!! safe_html($description) !!}</p>
            @endif
            @if (count($list) > 0)
                @php
                    $maxItem = 6;
                    $showButtonViewAll = count($list) > $maxItem;
                @endphp
                <div class="d-none d-lg-block">
                    <div class="list-item">
                        @for ($i = 0; $i < count($list); $i++)
                            <div class="item {{ $i + 1 > $maxItem ? 'd-none' : '' }}">
                                <div class="item-wrapper p-3 h-100">
                                    <div class="media">
                                        @include('frontend::shared.image-wrapper', [
                                            'link' => $list[$i]->icon,
                                            'alt' => $list[$i]->name,
                                            'imageConfig' => ['w' => 60, 'h' => 60]
                                        ])
                                        <div class="media-body">
                                            <p class="title mb-2">{{ $list[$i]->name }}</p>
                                             <p class="description give-ellipsis after-2-lines mb-0">{{ $list[$i]->description }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                    @if ($showButtonViewAll)
                        <div class="btn-view-all-wrapper text-center">
                            <a href="javascript:;" class="btn-view-all btn btn-log btn-warning">
                                {{ __('frontend::common.button_view_all') }}
                                <i class="fa-solid fa-arrow-right-long ml-2"></i>
                            </a>
                        </div>
                    @endif
                </div>
                <div class="d-block d-lg-none">
                    <div class="slide-1">
                        <div class="list-item owl-carousel owl-theme">
                            @php
                                $maxItem = 3;
                                $showButtonViewAll = count($list) > $maxItem;
                            @endphp
                            @for ($i = 0; $i < count($list); $i++)
                                @if ($i % 3 == 0) <div class="item"> @endif
                                    <div class="item-wrapper p-3 h-100">
                                        <div class="media">
                                            @include('frontend::shared.image-wrapper', [
                                                'link' => $list[$i]->icon,
                                                'alt' => $list[$i]->name,
                                                'imageConfig' => ['w' => 60, 'h' => 60]
                                            ])
                                            <div class="media-body">
                                                <p class="title mb-2">{{ $list[$i]->name }}</p>
                                                <p class="description give-ellipsis after-2-lines mb-0">{{ $list[$i]->description }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @if (($i + 1) % 3 == 0 || $i == count($list) - 1) </div> @endif
                            @endfor
                        </div>
                        @if ($showButtonViewAll)
                            <div class="btn-view-all-wrapper text-center">
                                <a href="javascript:;" class="btn-view-all-mobile btn btn-log btn-warning">
                                    {{ __('frontend::common.button_view_all') }}
                                    <i class="fa-solid fa-arrow-right-long ml-2"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
