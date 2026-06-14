@php
    $list = isset($list) ? $list : [];
    $tagHeading = isset($tagHeading) ? $tagHeading : 'p';

    $languageCode = Route::current()->parameter('languageCode');
@endphp

@if (count($list) > 0)
    <div class="list-article-popular">
        @for ($i = 0; $i < count($list); $i++)
            <div class="media item">
                <div class="image">
                    <a href="{{ \Modules\FrontEnd\Helpers\FeArticleUtils::getShowUrl($list[$i], $languageCode) }}">
                        @include('frontend::shared.image-wrapper', [
                            'link' => FeUtils::getThumbnail(['link' => $list[$i]->image_link, 'w' => 60, 'h' => 60]),
                            'alt' => $list[$i]->title
                        ])
                    </a>
                </div>
                <div class="main-info">
                    <{{ $tagHeading }} class="title font-weight-bold give-ellipsis after-3-lines">
                        <a href="{{ \Modules\FrontEnd\Helpers\FeArticleUtils::getShowUrl($list[$i], $languageCode) }}">{{ $list[$i]->title }}</a>
                    </{{ $tagHeading }}>
                    @if ($list[$i]->category_id)
                        <div class="category">
                            <a href="{{ route(Utilities::getRouteName('frontend.article.category'), ['languageCode' => $languageCode, 'slug' => $list[$i]->category_slug]) }}" class="text-reset">{{ $list[$i]->category_name }}</a>
                        </div>
                    @endif
                </div>
            </div>
        @endfor
    </div>
@endif