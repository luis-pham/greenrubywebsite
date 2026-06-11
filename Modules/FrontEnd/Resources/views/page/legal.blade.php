@extends('frontend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');

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
                <div class="legal-hero-eyebrow">
                    <span class="legal-hero-eyebrow-line"></span>
                    {{ __('frontend::page.page_legal_title') }}
                    <span class="legal-hero-eyebrow-line"></span>
                </div>
                <h1 class="legal-hero-title font-heading">{!! __('frontend::page.page_legal_hero_title') !!}</h1>
                <p class="legal-hero-subtitle mb-0">{{ __('frontend::page.page_legal_hero_subtitle') }}</p>
            </div>
        </section>

        <section class="section-2 legal-body">
            <div class="container">
                <div class="legal-sections-wrapper">
                    <div class="legal-accordion">
                        <div class="legal-meta-bar">
                            <div class="legal-meta-chip">{{ __('frontend::page.page_legal_last_updated') }}</div>
                            <div class="legal-meta-dot" aria-hidden="true"></div>
                            <div class="legal-meta-chip">{{ __('frontend::page.page_legal_applies_to') }}</div>
                            <div class="legal-meta-dot" aria-hidden="true"></div>
                            <div class="legal-meta-chip">{{ __('frontend::page.page_legal_compliance') }}</div>
                        </div>

                        @foreach ($legalAccordionItems as $item)
                            <div class="legal-accordion-item {{ $item['open'] ? 'open' : '' }}" id="{{ $item['id'] }}">
                                <div class="legal-accordion-header" role="button" tabindex="0" aria-expanded="{{ $item['open'] ? 'true' : 'false' }}">
                                    <div class="legal-accordion-left">
                                        <span class="legal-accordion-num">{{ $item['num'] }}</span>
                                        <span class="legal-accordion-title">{{ $item['title'] }}</span>
                                    </div>
                                    <div class="legal-accordion-toggle" aria-hidden="true">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M6 9l6 6 6-6"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="legal-accordion-body article-content">
                                    {!! safe_html($item['content']) !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @include('frontend::shared.breadcrumb', ['listBreadcrumb' => $listBreadcrumb, 'isVisible' => false])
    <script>
        (function () {
            var headers = document.querySelectorAll('#article.page-legal .legal-accordion-header');
            if (!headers.length) {
                return;
            }

            function setExpanded(header, isOpen) {
                header.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            }

            function closeAllItems() {
                document.querySelectorAll('#article.page-legal .legal-accordion-item').forEach(function (accordionItem) {
                    accordionItem.classList.remove('open');
                    var accordionHeader = accordionItem.querySelector('.legal-accordion-header');
                    if (accordionHeader) {
                        setExpanded(accordionHeader, false);
                    }
                });
            }

            function openItem(item) {
                if (!item) {
                    return;
                }

                var header = item.querySelector('.legal-accordion-header');
                closeAllItems();
                item.classList.add('open');
                if (header) {
                    setExpanded(header, true);
                }
            }

            function toggleItem(header) {
                var item = header.parentElement;
                var wasOpen = item.classList.contains('open');

                closeAllItems();

                if (!wasOpen) {
                    item.classList.add('open');
                    setExpanded(header, true);
                }
            }

            function openItemFromHash() {
                var hash = window.location.hash.replace('#', '');
                if (!hash) {
                    return;
                }

                var item = document.getElementById(hash);
                if (!item || !item.classList.contains('legal-accordion-item')) {
                    return;
                }

                openItem(item);
                window.requestAnimationFrame(function () {
                    item.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            }

            headers.forEach(function (header) {
                header.addEventListener('click', function () {
                    toggleItem(header);
                });

                header.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        toggleItem(header);
                    }
                });
            });

            openItemFromHash();
            window.addEventListener('hashchange', openItemFromHash);
        })();
    </script>
@endpush
