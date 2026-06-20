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

        <section id="section-faq" class="section-2 faq-body bg bg-tender-white">
            <div class="container-fluid">
                <div class="container">
                    @if (count($listFaq) > 0)
                        <ul class="list-faq list-unstyled mb-0">
                            @for ($i = 0; $i < count($listFaq); $i++)
                                <li class="item">
                                    <div class="item-wrapper position-relative">
                                        @if (!empty($listFaq[$i]->group_name))
                                            <div class="group-name d-inline-block mb-3 mb-xl-2 text-uppercase">{{ $listFaq[$i]->group_name }}</div>
                                        @endif
                                        <div class="question article-content">{!! safe_html($listFaq[$i]->question) !!}</div>
                                        <div class="answer article-content">{!! safe_html($listFaq[$i]->answer) !!}</div>
                                        <button type="button" class="btn-toggle border-0 rounded-circle" title="Toggle FAQ">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="faq-icon" aria-hidden="true">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M12 5l0 14"/>
                                                <path d="M5 12l14 0"/>
                                            </svg>
                                        </button>
                                    </div>
                                </li>
                            @endfor
                        </ul>
                        @if ($totalPage > 1)
                            <div class="faq-pagination text-center">
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
        </section>
    </div>
@endsection

@push('scripts')
    @include('frontend::shared.breadcrumb', ['listBreadcrumb' => $listBreadcrumb, 'isVisible' => false])
    @if (count($listFaq) > 0)
        @php
            $faqSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => collect($listFaq)->map(function ($faq) {
                    return [
                        '@type' => 'Question',
                        'name' => strip_tags(html_entity_decode($faq->question, ENT_QUOTES, 'UTF-8')),
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => strip_tags(html_entity_decode($faq->answer, ENT_QUOTES, 'UTF-8')),
                        ],
                    ];
                })->values()->all(),
            ];
        @endphp
        <script type="application/ld+json">
            {!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endif
@endpush
