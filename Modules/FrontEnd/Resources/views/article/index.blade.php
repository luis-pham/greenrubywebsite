@extends('frontend::layouts.master')

@section('headerHeroType', 'solid')

@php
    $listArticlePopular = isset($pageConfig[PageConfigKeyConsts::ARTICLE_POPULAR])
        ? $pageConfig[PageConfigKeyConsts::ARTICLE_POPULAR]
        : [];
    
    $languageCode = Route::current()->parameter('languageCode');
    $searchUrl = !isset($category)
        ? route(Utilities::getRouteName('frontend.article.index'), ['languageCode' => $languageCode])
        : route(Utilities::getRouteName('frontend.article.category'), ['languageCode' => $languageCode, 'slug' => $category->slug]);
@endphp

@section('content')
    <div id="article-category" class="page-article">
        <section class="section-1 article-hero position-relative">
            <svg class="article-hero-topo" viewBox="0 0 1440 240" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                <ellipse cx="1200" cy="50" rx="300" ry="170" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="1200" cy="50" rx="225" ry="120" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="200" cy="210" rx="260" ry="150" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="200" cy="210" rx="185" ry="105" fill="none" stroke="white" stroke-width="1"/>
            </svg>
            <div class="article-hero-inner">
                <div class="section-eyebrow section-eyebrow--gold">
                    <span class="eyebrow-line"></span>
                    {{ __('frontend::article.hero_eyebrow') }}
                    <span class="eyebrow-line"></span>
                </div>
                <h1 class="article-hero-title font-heading">{!! __('frontend::article.hero_title') !!}</h1>
                <p class="article-hero-subtitle mb-0">{{ __('frontend::article.hero_subtitle') }}</p>
            </div>
        </section>

        @include('frontend::article.shared.category-filter', [
            'listCategoryChild' => $listCategoryChild,
            'category' => $category ?? null,
            'categoryParent' => $categoryParent ?? null,
            'languageCode' => $languageCode,
        ])

        <section class="section-2 d-block d-lg-none pb-0">
            <div class="container">
                @include('frontend::article.shared.widget-search', ['searchUrl' => $searchUrl])
            </div>
        </section>
        @if (count($listArticleFeatured) > 0)
            <section class="section-3 d-block pb-0">
                <div class="container">
                    @if (count($listArticleFeatured) > 0)
                        <div class="slide-1">
                            <div class="list-item owl-carousel owl-theme">
                                @for ($i = 0; $i < count($listArticleFeatured); $i++)
                                    @php
                                        $articleUrl = \Modules\FrontEnd\Helpers\FeArticleUtils::getShowUrl($listArticleFeatured[$i], $languageCode);
                                    @endphp
                                    <div class="item">
                                        <div class="item-wrapper">
                                            <a href="{{ $articleUrl }}" class="image d-block" style="background-image: url({{ asset(FeUtils::getImageLink($listArticleFeatured[$i]->image_link)) }})"></a>
                                            <div class="main-info d-flex flex-column w-100">
                                                <div class="main-info-body">
                                                    <h2 class="title give-ellipsis after-3-lines">
                                                        <a href="{{ $articleUrl }}" class="text-reset">{{ $listArticleFeatured[$i]->title }}</a>
                                                    </h2>
                                                    <p class="description give-ellipsis after-5-lines">
                                                        <a href="{{ $articleUrl }}" class="text-reset">{{ strip_tags($listArticleFeatured[$i]->lead) }}</a>
                                                    </p>
                                                </div>
                                                <div class="main-info-footer">
                                                    <a href="{{ $articleUrl }}" class="btn-learn-more btn btn-warning">
                                                        {{ __('frontend::article.button_learn_more') }}
                                                        <i class="fa-solid fa-arrow-right-long ml-2"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endif
        <section id="section-article" class="section-4 section-article">
            <div class="container">
                <div class="page-wrapper">
                    <div class="main-content">
                        @if (isset($category) && $category)
                            <h1 class="section-title text-lg-left">{{ $category->name }}</h1>
                        @endif
                        <div class="header">
                            <p class="section-description mb-0 font-heading text-lg-left">{{ __('frontend::article.category_default_sub_title') }}</p>
                        </div>
                        @if (count($listArticle) > 0)
                            <div class="{{ $totalPage > 1 ? 'mb-4' : '' }}">
                                <div class="list-article">
                                    <div class="row">
                                        @for ($i = 0; $i < count($listArticle); $i++)
                                            <div class="col-md-6">
                                                @include('frontend::article.shared.list-article-item', [
                                                    'article' => $listArticle[$i],
                                                    'imageConfig' => ['w' => 320, 'h' => 200],
                                                    'tagHeading' => 'h2'
                                                ])
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($totalPage > 1)
                            @include('frontend::shared.pagination', [
                                'baseUrl' => !isset($category)
                                    ? route(Utilities::getRouteName('frontend.article.index'), ['languageCode' => $languageCode])
                                    : route(Utilities::getRouteName('frontend.article.category'), ['languageCode' => $languageCode, 'slug' => $category->slug]),
                                'totalPage' => $totalPage
                            ])
                        @endif
                    </div>
                    <div class="sidebar">
                        @include('frontend::article.shared.widget-search', [
                            'searchUrl' => $searchUrl,
                            'class' => 'd-none d-lg-block'
                        ])
                        @include('frontend::article.shared.widget-article-popular', [
                            'list' => $listArticlePopular,
                            'tagHeadingWidget' => 'h2',
                            'tagHeadingItem' => 'h3'
                        ])
                    </div>
                </div>
            </div>
        </section>
        @include('frontend::shared.section.section-call-to-action', [
            'class' => 'section-5',
            'title' => '',
            'description' => __('frontend::article.section_call_to_action_title'),
            'content' => __('frontend::article.section_call_to_action_description'),
            'buttons' => [[
                'label' => __('frontend::article.section_call_to_action_button_itineraries'),
                'class' => 'btn-warning',
                'url' => route(Utilities::getRouteName('frontend.itinerary.index'), ['languageCode' => $languageCode])
            ], [
                'label' => __('frontend::article.section_call_to_action_button_contact'),
                'class' => 'btn-success',
                'url' => route(Utilities::getRouteName('frontend.contact.index'), ['languageCode' => $languageCode])
            ]]
        ])
    </div>
@endsection

@push('scripts')
    @include('frontend::shared.breadcrumb', ['listBreadcrumb' => $listBreadcrumb, 'isVisible' => false])
@endpush