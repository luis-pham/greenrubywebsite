@php
    $list = isset($list) ? $list : [];
    $tagHeadingWidget = isset($tagHeadingWidget) ? $tagHeadingWidget : 'p';
    $tagHeadingItem = isset($tagHeadingItem) ? $tagHeadingItem : 'p';
@endphp

<div class="widget">
    <{{ $tagHeadingWidget }} class="width-title font-weight-bold">{{ __('frontend::article.article_popular') }}</{{ $tagHeadingWidget }}>
    <div class="widget-body">
        @include('frontend::article.shared.list-article-popular', [
            'list' => $list,
            'tagHeading' => $tagHeadingItem
        ])
    </div>
</div>