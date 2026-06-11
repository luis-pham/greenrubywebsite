@extends('frontend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');
@endphp

@section('content')
    <div id="article">
        @include('frontend::shared.section.section-cover', [
            'class' => 'section-1 section-cover-sm',
            'list' => [(object)[
                "title" => $pageTitle,
                "link" => asset("/assets/frontend/images/modules/article/page-cover.jpg"),
            ]],
            'tagHeading' => 'h1'
        ])
        <section class="section-2">
            <div class="container">
                <div class="page-wrapper">
                    <div class="main-content">
                        <article class="article-content">{!! safe_html($pageContent) !!}</article>
                        <div class="article-footer">
                            <div class="article-footer-left">
                                <a href="{{ route(Utilities::getRouteName('frontend.index'), ['languageCode' => $languageCode]) }}" class="btn-back d-block d-md-inline-block btn btn-lg btn-warning">
                                    <i class="fa-solid fa-arrow-left mr-2"></i>
                                    <span>{{ sprintf(__('frontend::article.button_back'), __('frontend::common.homepage')) }}</span>
                                </a>
                            </div>
                            <div class="article-footer-right">
                                <div class="d-flex align-items-center">
                                    <p class="label mb-0 mr-2 font-weight-bold">{{ __('frontend::article.share_this_article') }}:</p>
                                    <div class="social d-flex align-items-center">
                                        <a href="https://www.facebook.com/sharer.php?u={{ $pageUrl }}" class="d-block text-reset" target="_blank" rel="nofollow">
                                            <div class="icon icon-facebook align-self-center">
                                                <div class="icon-wrapper d-flex align-items-center justify-content-center rounded-circle">
                                                    <i class="fa-brands fa-facebook-f"></i>
                                                </div>
                                            </div>
                                        </a>
                                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $pageUrl }}" class="d-block text-reset" target="_blank" rel="nofollow">
                                            <div class="icon icon-linkedin align-self-center">
                                                <div class="icon-wrapper d-flex align-items-center justify-content-center rounded-circle">
                                                    <i class="fa-brands fa-linkedin-in"></i>
                                                </div>
                                            </div>
                                        </a>
                                        <a href="mailto:?subject={{ $pageTitle }}&body={{ $pageUrl }}" class="d-block text-reset" target="_blank" rel="nofollow">
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
    @include('frontend::shared.breadcrumb', ['listBreadcrumb' => $listBreadcrumb, 'isVisible' => false])
@endpush