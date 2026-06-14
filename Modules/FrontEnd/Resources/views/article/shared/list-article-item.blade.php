@php
    $languageCode = Route::current()->parameter('languageCode');

    $url = \Modules\FrontEnd\Helpers\FeArticleUtils::getShowUrl($article, $languageCode);
    
    $imageLinkRouteParam = ['link' => $article->image_link];
    $imageConfig = $imageConfig ?? [];
    if (array_key_exists('w', $imageConfig)) {
        $imageLinkRouteParam['w'] = $imageConfig['w'];
    }
    if (array_key_exists('h', $imageConfig)) {
        $imageLinkRouteParam['h'] = $imageConfig['h'];
    }
    if (array_key_exists('cr', $imageConfig)) {
        $imageLinkRouteParam['cr'] = $imageConfig['cr'];
    }
    $imageLink = FeUtils::getThumbnail($imageLinkRouteParam);
    
    $lead = $article->lead ?? null;
    $tagHeading = isset($tagHeading) ? $tagHeading : 'p';
@endphp

<div class="item">
    <div class="image mb-3">
        <a href="{{ $url }}">
            @include('frontend::shared.image-wrapper', [
                'link' => $imageLink,
                'alt' => $article->title,
                'ratio' => '16-9'
            ])
        </a>
    </div>
    <div class="main-info">
        <div class="main-info-header d-flex justify-content-between">
            @if ($article->category_id)
                <div class="category font-weight-bold text-white">
                    <a href="{{ route(Utilities::getRouteName('frontend.article.category'), ['languageCode' => $languageCode, 'slug' => $article->category_slug]) }}" class="text-reset">{{ $article->category_name }}</a>
                </div>
            @endif
            <p class="pubhlish-date mb-0"><i class="fa-solid fa-calendar mr-2"></i>{{ Utilities::formatDisplayDateOnly($article->publish_date) }}</p>
        </div>
        <div class="main-info-body">
            <{{ $tagHeading }} class="title give-ellipsis after-2-lines"><a href="{{ $url }}" class="text-reset">{{ $article->title }}</a></{{ $tagHeading }}>
            <p class="description mb-0 give-ellipsis after-3-lines"><a href="{{ $url }}" class="text-reset">{{ strip_tags($lead) }}</a></p>
        </div>
        <div class="main-info-footer">
            <a href="{{ $url }}" class="btn-view-details d-inline-block">
                {{ __('frontend::common.button_view_details') }}
            </a>
        </div>
    </div>
</div>