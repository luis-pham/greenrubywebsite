@extends('frontend::layouts.master')

@php
    $listArticlePopular = isset($pageConfig[PageConfigKeyConsts::ARTICLE_POPULAR])
        ? $pageConfig[PageConfigKeyConsts::ARTICLE_POPULAR]
        : [];

    $languageCode = Route::current()->parameter('languageCode');
@endphp

@section('content')
    <div id="article">
        <section class="section-1 article-hero article-detail-hero position-relative">
            <svg class="article-hero-topo" viewBox="0 0 1440 240" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                <ellipse cx="1200" cy="50" rx="300" ry="170" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="1200" cy="50" rx="225" ry="120" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="200" cy="210" rx="260" ry="150" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="200" cy="210" rx="185" ry="105" fill="none" stroke="white" stroke-width="1"/>
            </svg>
            <div class="article-hero-inner">
                @if (count($listBreadcrumb) > 0)
                    @include('frontend::shared.breadcrumb', ['listBreadcrumb' => $listBreadcrumb])
                @endif
                <h1 class="article-hero-title article-detail-title font-heading">{{ $obj->title }}</h1>
                @if ($obj->sub_title)
                    <p class="article-hero-subtitle mb-0">{{ $obj->sub_title }}</p>
                @endif
                <p class="article-detail-meta publish-date mb-0 mt-3 mt-md-2"><i class="fa-solid fa-calendar mr-2"></i>{{ Utilities::formatDisplayDateOnly($obj->publish_date) }}, {{ Utilities::formatDisplayTime($obj->publish_date) }}</p>
            </div>
        </section>

        @include('frontend::article.shared.category-filter', [
            'listCategoryChild' => $listCategoryChild ?? [],
            'category' => $category ?? null,
            'categoryParent' => $categoryParent ?? null,
            'languageCode' => $languageCode,
        ])

        <section class="section-2">
            <div class="container">
                <div class="page-wrapper">
                    <div class="main-content">
                        <article class="article-body">
                            <div class="description article-content">
                                {!! safe_html($obj->lead) !!}
                            </div>
                            <div class="content article-content">
                                {!! safe_html($obj->content) !!}
                            </div>
                        </article>
                        <div class="article-footer">
                            <div class="article-footer-left">
                                <a href="{{ route(Utilities::getRouteName('frontend.article.category'), ['languageCode' => $languageCode, 'slug' => $obj->category_slug]) }}" class="btn-back d-block d-md-inline-block btn btn-lg btn-warning">
                                    <i class="fa-solid fa-arrow-left mr-2"></i>
                                    <span>{{ sprintf(__('frontend::article.button_back'), $obj->category_name) }}</span>
                                </a>
                            </div>
                            <div class="article-footer-right">
                                <div class="d-flex align-items-center">
                                    <p class="label mb-0 mr-2 font-weight-bold">{{ __('frontend::article.share_this_article') }}:</p>
                                    @php
                                        $url = \Modules\FrontEnd\Helpers\FeArticleUtils::getShowUrl($obj, $languageCode);
                                    @endphp
                                    <div class="social d-flex align-items-center">
                                        <a href="https://www.facebook.com/sharer.php?u={{ $url }}" class="d-block text-reset" target="_blank" rel="nofollow">
                                            <div class="icon icon-facebook align-self-center">
                                                <div class="icon-wrapper d-flex align-items-center justify-content-center rounded-circle">
                                                    <i class="fa-brands fa-facebook-f"></i>
                                                </div>
                                            </div>
                                        </a>
                                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $url }}" class="d-block text-reset" target="_blank" rel="nofollow">
                                            <div class="icon icon-linkedin align-self-center">
                                                <div class="icon-wrapper d-flex align-items-center justify-content-center rounded-circle">
                                                    <i class="fa-brands fa-linkedin-in"></i>
                                                </div>
                                            </div>
                                        </a>
                                        <a href="mailto:?subject={{ $obj->title }}&body={{ $url }}" class="d-block text-reset" target="_blank" rel="nofollow">
                                            <div class="icon icon-email align-self-center">
                                                <div class="icon-wrapper d-flex align-items-center justify-content-center rounded-circle">
                                                    <i class="fa-solid fa-envelope"></i>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sidebar">
                        @include('frontend::article.shared.widget-article-popular', [
                            'list' => $listArticlePopular,
                            'tagHeadingWidget' => 'h2',
                            'tagHeadingItem' => 'h3'
                        ])
                    </div>
                </div>
            </div>
        </section>
        @if (count($listArticleRelated) > 0)
            <section class="section-3 bg">
                <div class="container-fluid text-white">
                    <div class="container">
                        <h2 class="section-title">{{ __('frontend::article.section_article_related_title') }}</h2>
                        <p class="section-description font-heading">{{ __('frontend::article.section_article_related_description') }}</p>
                        <div class="slide-1">
                            <div class="list-article owl-carousel owl-theme">
                                @for ($i = 0; $i < count($listArticleRelated); $i++)
                                    <div class="item">
                                        @include('frontend::article.shared.list-article-item', [
                                            'article' => $listArticleRelated[$i],
                                            'imageConfig' => ['w' => 545, 'h' => 307]
                                        ])
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif
        @include('frontend::shared.section.section-call-to-action', [
            'class' => 'section-4',
            'title' => '',
            'description' => __('frontend::article.section_call_to_action_2_title'),
            'content' => __('frontend::article.section_call_to_action_2_description'),
            'buttons' => [[
                'label' => __('frontend::common.book_now'),
                'class' => 'btn-warning',
                'url' => route(Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode])
            ]]
        ])
    </div>
@endsection

@push('scripts')
    @php
        $listSize = [];
        if ($obj->image_link) {
            $fileLink = Utilities::getFileLink($obj->image_link);
            $filePath = ltrim($fileLink, '/');
            if (File::exists($filePath)) {
                $imageWidth = \Image::make($filePath)->width();
                $imageHeight = \Image::make($filePath)->height();
                $listSize['1-1'] = [
                    'width' => min($imageWidth, $imageHeight),
                    'height' => min($imageWidth, $imageHeight)
                ];
                if ($imageWidth > $imageHeight) {
                    $listSize['16-9'] = [
                        'width' => (int)($imageHeight / (9 / 16)),
                        'height' => $imageHeight
                    ];
                    $listSize['4-3'] = [
                        'width' => (int)($imageHeight / (3 / 4)),
                        'height' => $imageHeight
                    ];
                } else {
                    $listSize['16-9'] = [
                        'width' => $imageWidth,
                        'height' => (int)($imageWidth * (9 / 16))
                    ];
                    $listSize['4-3'] = [
                        'width' => $imageWidth,
                        'height' => (int)($imageWidth * (3 / 4))
                    ];
                }
            }
        }

        $articleSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $obj->title,
            'datePublished' => \Carbon\Carbon::parse($obj->publish_date)->format('Y-m-d\TH:i:sP'),
            'dateModified' => \Carbon\Carbon::parse($obj->updated_at ?: $obj->created_at)->format('Y-m-d\TH:i:sP'),
            'publisher' => ['@id' => config('frontend.organizationSchemaId')],
            'author' => [
                '@type' => 'Organization',
                '@id' => config('frontend.organizationSchemaId'),
            ],
            'mainEntityOfPage' => $url,
        ];

        if (count($listSize) > 0) {
            $articleSchema['image'] = [
                FeUtils::getThumbnail(['link' => $obj->image_link, 'w' => $listSize['1-1']['width'], 'h' => $listSize['1-1']['height']]),
                FeUtils::getThumbnail(['link' => $obj->image_link, 'w' => $listSize['4-3']['width'], 'h' => $listSize['4-3']['height']]),
                FeUtils::getThumbnail(['link' => $obj->image_link, 'w' => $listSize['16-9']['width'], 'h' => $listSize['16-9']['height']]),
            ];
        }
    @endphp
    <script type="application/ld+json">
        {!! json_encode($articleSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    @include('frontend::shared.structured-data-organization')
@endpush
