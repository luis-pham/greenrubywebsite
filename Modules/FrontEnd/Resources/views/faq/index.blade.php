@extends('frontend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');
    $sectionFilterId = isset($group) ? 'section-filter-' . $group->slug : 'section-filter';
    $keyword = Request::get('k');
@endphp

@section('content')
    <div id="faq-category" class="page-faq">
        <section class="section-1 faq-hero position-relative">
            <svg class="faq-hero-topo" viewBox="0 0 1440 240" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                <ellipse cx="1200" cy="50" rx="300" ry="170" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="1200" cy="50" rx="225" ry="120" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="200" cy="210" rx="260" ry="150" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="200" cy="210" rx="185" ry="105" fill="none" stroke="white" stroke-width="1"/>
            </svg>
            <div class="faq-hero-inner">
                <div class="section-eyebrow section-eyebrow--gold">
                    <span class="eyebrow-line"></span>
                    {{ __('frontend::faq.hero_eyebrow') }}
                    <span class="eyebrow-line"></span>
                </div>
                <h1 class="faq-hero-title font-heading">{!! __('frontend::faq.hero_title') !!}</h1>
                <p class="faq-hero-subtitle mb-0">{{ __('frontend::faq.hero_subtitle') }}</p>
            </div>
        </section>

        @if (count($listGroup) > 0)
            <div id="{{ $sectionFilterId }}" class="gallery-filter-sticky">
                <div class="container-fluid px-0">
                    <div class="gallery-filter-inner">
                        <div class="container">
                            <nav class="gallery-filter-bar list-filter" aria-label="{{ __('frontend::faq.section_2_title') }}">
                                <a href="{{ route(Utilities::getRouteName('frontend.faq.index'), ['languageCode' => $languageCode]) }}#section-filter" class="item gallery-filter-tab {{ !isset($group) ? 'active' : '' }}">{{ __('frontend::common.all') }}</a>
                                @for ($i = 0; $i < count($listGroup); $i++)
                                    <a href="{{ route(Utilities::getRouteName('frontend.faq.category'), ['languageCode' => $languageCode, 'slug' => $listGroup[$i]->slug]) }}#section-filter-{{ $listGroup[$i]->slug }}" class="item gallery-filter-tab {{ isset($group) && $group->id == $listGroup[$i]->id ? 'active' : '' }}">{{ $listGroup[$i]->name }}</a>
                                @endfor
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <section id="section-faq" class="section-2 faq-body">
            <div class="container">
                <div class="faq-sections-wrapper">
                    <div class="faq-accordion">
                        @if (count($listFaq) > 0)
                            <ul class="list-faq list-unstyled {{ $totalPage > 1 ? 'mb-0' : 'mb-0' }}">
                                @for ($i = 0; $i < count($listFaq); $i++)
                                    <li class="item faq-accordion-item">
                                        <div class="item-wrapper">
                                            <div class="question faq-accordion-header" role="button" tabindex="0" aria-expanded="false">
                                                <div class="faq-accordion-left">
                                                    <span class="faq-accordion-category">{{ $listFaq[$i]->group_name }}</span>
                                                    <div class="faq-accordion-title article-content">{!! safe_html($listFaq[$i]->question) !!}</div>
                                                </div>
                                                <div class="faq-accordion-toggle" aria-hidden="true">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M6 9l6 6 6-6"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="answer faq-accordion-body article-content">{!! safe_html($listFaq[$i]->answer) !!}</div>
                                        </div>
                                    </li>
                                @endfor
                            </ul>
                            @if ($totalPage > 1)
                                <div class="faq-pagination">
                                    @include('frontend::shared.pagination', [
                                        'baseUrl' => !isset($group)
                                            ? route(Utilities::getRouteName('frontend.faq.index'), ['languageCode' => $languageCode])
                                            : route(Utilities::getRouteName('frontend.faq.category'), ['languageCode' => $languageCode, 'slug' => $group->slug]),
                                        'totalPage' => $totalPage
                                    ])
                                </div>
                            @endif
                        @else
                            <p class="faq-empty text-center mb-0">{{ !$keyword ? __('frontend::common.no_data') : __('frontend::common.search_result_not_found') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @include('frontend::shared.breadcrumb', ['listBreadcrumb' => $listBreadcrumb, 'isVisible' => false])
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": [
                @foreach ($listFaq as $faq)
                    {
                        "@type": "Question",
                        "name": "{!! html_entity_decode($faq->question, ENT_QUOTES, 'UTF-8') !!}",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "{!! html_entity_decode($faq->answer, ENT_QUOTES, 'UTF-8') !!}"
                        }
                    }{{ !$loop->last ? ',' : '' }}
                @endforeach
            ]
        }
    </script>
@endpush
