@extends('frontend::layouts.master')

@php
    $languageCode = \Modules\FrontEnd\Helpers\FeLanguageUtils::getRouteLanguageCode();

    $safetyPoliciesContent = isset($pageConfig[PageConfigKeyConsts::LEGAL_SAFETY_POLICIES_CONTENT])
        ? $pageConfig[PageConfigKeyConsts::LEGAL_SAFETY_POLICIES_CONTENT]
        : '';
    $termsAndConditionsContent = isset($pageConfig[PageConfigKeyConsts::LEGAL_TERMS_AND_CONDITIONS_CONTENT])
        ? $pageConfig[PageConfigKeyConsts::LEGAL_TERMS_AND_CONDITIONS_CONTENT]
        : '';
    $privacyPoliciesContent = isset($pageConfig[PageConfigKeyConsts::LEGAL_PRIVACY_POLICIES_CONTENT])
        ? $pageConfig[PageConfigKeyConsts::LEGAL_PRIVACY_POLICIES_CONTENT]
        : '';
    $paymentMethodsContent = isset($pageConfig[PageConfigKeyConsts::LEGAL_PAYMENT_METHODS_CONTENT])
        ? $pageConfig[PageConfigKeyConsts::LEGAL_PAYMENT_METHODS_CONTENT]
        : '';

    $legalAccordionItems = [];
    if ($safetyPoliciesContent) {
        $legalAccordionItems[] = [
            'id' => 'safety',
            'num' => '01',
            'title' => __('frontend::page.page_safety_policies_title'),
            'content' => $safetyPoliciesContent,
            'open' => true,
        ];
    }
    if ($termsAndConditionsContent) {
        $legalAccordionItems[] = [
            'id' => 'terms',
            'num' => '02',
            'title' => __('frontend::page.page_terms_and_conditions_title'),
            'content' => $termsAndConditionsContent,
            'open' => false,
        ];
    }
    if ($privacyPoliciesContent) {
        $legalAccordionItems[] = [
            'id' => 'privacy',
            'num' => '03',
            'title' => __('frontend::page.page_privacy_policies_title'),
            'content' => $privacyPoliciesContent,
            'open' => false,
        ];
    }
    if ($paymentMethodsContent) {
        $legalAccordionItems[] = [
            'id' => 'payment',
            'num' => '04',
            'title' => __('frontend::page.page_payment_methods_title'),
            'content' => $paymentMethodsContent,
            'open' => false,
        ];
    }
@endphp

@section('content')
    <div id="article" class="page-legal">
        <section class="section-1 legal-hero position-relative">
            <svg class="legal-hero-topo" viewBox="0 0 1440 240" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                <ellipse cx="1200" cy="50" rx="300" ry="170" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="1200" cy="50" rx="225" ry="120" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="200" cy="210" rx="260" ry="150" fill="none" stroke="white" stroke-width="1"/>
                <ellipse cx="200" cy="210" rx="185" ry="105" fill="none" stroke="white" stroke-width="1"/>
            </svg>
            <div class="legal-hero-inner">
                <div class="section-eyebrow section-eyebrow--gold">
                    <span class="eyebrow-line"></span>
                    {{ __('frontend::page.page_legal_title') }}
                    <span class="eyebrow-line"></span>
                </div>
                <h1 class="legal-hero-title font-heading">{!! __('frontend::page.page_legal_hero_title') !!}</h1>
                <p class="legal-hero-subtitle mb-0">{{ __('frontend::page.page_legal_hero_subtitle') }}</p>
            </div>
        </section>

        <section class="section-2 legal-body bg bg-tender-white">
            <div class="container-fluid">
                <div class="container">
                    <div class="legal-meta-bar">
                        <div class="legal-meta-chip">{{ __('frontend::page.page_legal_last_updated') }}</div>
                        <div class="legal-meta-dot" aria-hidden="true"></div>
                        <div class="legal-meta-chip">{{ __('frontend::page.page_legal_applies_to') }}</div>
                        <div class="legal-meta-dot" aria-hidden="true"></div>
                        <div class="legal-meta-chip">{{ __('frontend::page.page_legal_compliance') }}</div>
                    </div>

                    @if (count($legalAccordionItems) > 0)
                        <ul class="list-faq list-unstyled mb-0">
                            @foreach ($legalAccordionItems as $item)
                                <li class="item {{ $item['open'] ? 'expand' : '' }}" id="{{ $item['id'] }}">
                                    <div class="item-wrapper position-relative">
                                        <div class="group-name d-inline-block mb-3 mb-xl-2 text-uppercase">{{ $item['num'] }}</div>
                                        <div class="question article-content">{{ $item['title'] }}</div>
                                        <div class="answer article-content">{!! safe_html($item['content']) !!}</div>
                                        <button type="button" class="btn-toggle border-0 rounded-circle" title="Toggle section">
                                            <i class="fa-solid fa-{{ $item['open'] ? 'minus' : 'plus' }}"></i>
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @include('frontend::shared.breadcrumb', ['listBreadcrumb' => $listBreadcrumb, 'isVisible' => false])
    <script>
        (function () {
            function openItemFromHash() {
                var hash = window.location.hash.replace('#', '');
                if (!hash) {
                    return;
                }

                var item = document.getElementById(hash);
                if (!item || !item.classList.contains('item')) {
                    return;
                }

                if (!item.classList.contains('expand')) {
                    var btn = item.querySelector('.btn-toggle');
                    if (btn) {
                        btn.click();
                    }
                }

                window.requestAnimationFrame(function () {
                    item.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            }

            openItemFromHash();
            window.addEventListener('hashchange', openItemFromHash);
        })();
    </script>
@endpush
