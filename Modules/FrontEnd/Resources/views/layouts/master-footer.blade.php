@php
    $languageCode = \Modules\FrontEnd\Helpers\FeLanguageUtils::getRouteLanguageCode();
    $indexUrl = route(Utilities::getRouteName('frontend.index'), ['languageCode' => $languageCode]);
    $legalUrl = route(Utilities::getRouteName('frontend.page.legal'), ['languageCode' => $languageCode]);
    $privacyPolicyUrl = route(Utilities::getRouteName('frontend.page.privacy-policy'), ['languageCode' => $languageCode]);
    $aboutUrl = route(Utilities::getRouteName('frontend.about.index'), ['languageCode' => $languageCode]);
    $hotline = $config['hotline'] ?? '';
    $whatsapp = $config['whatsapp'] ?? '';
    $email = $config['email'] ?? '';
    $terminal = $config['address'] ?? 'Manira, Tuan Chau, Quang Ninh, Viet Nam';
    $contactPhone = $hotline ?: $whatsapp;

    $footerNavColumns = [
        [
            'title' => __('frontend::common.footer_nav_explore'),
            'links' => [
                ['label' => __('frontend::common.footer_link_our_cruises'), 'url' => $indexUrl . '#section-cruise'],
                ['label' => __('frontend::common.footer_link_our_itineraries'), 'url' => route(Utilities::getRouteName('frontend.itinerary.index'), ['languageCode' => $languageCode])],
                ['label' => __('frontend::common.footer_link_our_services'), 'url' => route(Utilities::getRouteName('frontend.service.index'), ['languageCode' => $languageCode])],
                ['label' => __('frontend::common.footer_link_our_adventures'), 'url' => route(Utilities::getRouteName('frontend.experience.index'), ['languageCode' => $languageCode])],
                ['label' => __('frontend::common.footer_link_meeting_events'), 'url' => route(Utilities::getRouteName('frontend.service.index'), ['languageCode' => $languageCode]) . '#event'],
            ],
        ],
        [
            'title' => __('frontend::common.footer_nav_about'),
            'links' => [
                ['label' => __('frontend::common.footer_link_about_us'), 'url' => route(Utilities::getRouteName('frontend.about.index'), ['languageCode' => $languageCode])],
                ['label' => __('frontend::common.footer_link_contact_us'), 'url' => route(Utilities::getRouteName('frontend.contact.index'), ['languageCode' => $languageCode])],
                ['label' => __('frontend::common.footer_link_blog'), 'url' => route(Utilities::getRouteName('frontend.article.index'), ['languageCode' => $languageCode])],
                ['label' => __('frontend::common.footer_link_gallery'), 'url' => route(Utilities::getRouteName('frontend.gallery.index'), ['languageCode' => $languageCode])],
            ],
        ],
        [
            'title' => __('frontend::common.footer_nav_support'),
            'links' => [
                ['label' => __('frontend::common.footer_link_ask_ai'), 'url' => '#chat-with-ai', 'soon' => true],
                ['label' => __('frontend::common.footer_link_faqs'), 'url' => route(Utilities::getRouteName('frontend.faq.index'), ['languageCode' => $languageCode])],
                ['label' => __('frontend::common.footer_link_safety_policies'), 'url' => $legalUrl . '#safety'],
                ['label' => __('frontend::common.footer_link_payment_methods'), 'url' => $legalUrl . '#payment'],
            ],
        ],
        [
            'title' => __('frontend::common.footer_nav_sustainability'),
            'links' => [
                ['label' => __('frontend::common.footer_link_green_technology'), 'url' => $aboutUrl . '#sustainability'],
                ['label' => __('frontend::common.footer_link_green_globe'), 'url' => $aboutUrl . '#green-globe'],
                ['label' => __('frontend::common.footer_link_eco_dashboard'), 'url' => 'javascript:;', 'soon' => true],
            ],
        ],
    ];

    $socialPlatforms = ['tripadvisor', 'facebook', 'instagram', 'tiktok', 'youtube', 'linkedin', 'twitter', 'pinterest'];
    $listSocial = [];
    foreach ($socialPlatforms as $platform) {
        if (array_key_exists($platform, $config) && $config[$platform]) {
            $listSocial[] = [
                'type' => $platform === 'twitter' ? 'x' : $platform,
                'href' => $config[$platform],
            ];
        }
    }
@endphp

<footer id="footer">
    <div class="container-fluid px-0 text-white position-relative">
        {{-- TOP BAR --}}
        <div class="footer-bar footer-bar-top">
            <div class="container">
                <div class="footer-bar-top-inner">
                    <div class="footer-bar-contacts">
                        @if ($contactPhone)
                            <div class="footer-bar-contact-item">
                                <span class="footer-bar-contact-label">{{ __('frontend::common.footer_contact_hotline_whatsapp') }}</span>
                                <span class="footer-bar-contact-value">
                                    <a href="{{ 'tel:' . preg_replace('/\s+/', '', $contactPhone) }}" class="text-reset" rel="nofollow">{{ $contactPhone }}</a>
                                </span>
                            </div>
                        @endif
                        @if ($email)
                            <div class="footer-bar-contact-item">
                                <span class="footer-bar-contact-label">{{ __('frontend::common.footer_contact_email') }}</span>
                                <span class="footer-bar-contact-value">
                                    <a href="{{ 'mailto:' . $email }}" class="text-reset" rel="nofollow">{{ $email }}</a>
                                </span>
                            </div>
                        @endif
                        @if ($terminal)
                            <div class="footer-bar-contact-item">
                                <span class="footer-bar-contact-label">{{ __('frontend::common.footer_contact_terminal') }}</span>
                                <span class="footer-bar-contact-value">{{ $terminal }}</span>
                            </div>
                        @endif
                    </div>
                    <a href="{{ route(Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode]) }}" class="footer-bar-book-btn btn-book-now">
                        <span class="label font-weight-bold">{{ __('frontend::common.book_now') }}</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- MAIN FOOTER --}}
        <div class="footer-main">
            <div class="container">
                <div class="footer-main-grid">
                    <div class="footer-brand">
                        @if (array_key_exists('website-logo-negative', $config) && $config['website-logo-negative'])
                            <a href="{{ $indexUrl }}" class="footer-brand-logo d-block">
                                <img src="{{ asset(Utilities::getFileLink($config['website-logo-negative'])) }}" alt="{{ $config['website-name'] ?? __('frontend::common.footer_brand_name') }}" class="img-fluid" width="117" height="93" />
                            </a>
                        @endif
                        <p class="footer-brand-statement mb-0">{{ __('frontend::common.footer_brand_statement') }}</p>
                        <div class="footer-cert-badge">
                            <i class="fa-solid fa-leaf footer-cert-badge-icon" aria-hidden="true"></i>
                            <span class="footer-cert-badge-text">{{ __('frontend::common.footer_cert_badge') }}</span>
                        </div>
                        <div class="footer-social-block">
                            <p class="footer-social-label mb-0">{{ __('frontend::common.follow_us') }}</p>
                            @if (count($listSocial) > 0)
                                <div class="footer-social-icons">
                                    @foreach ($listSocial as $social)
                                        <a href="{{ $social['href'] }}" class="footer-social-icon-btn text-reset" target="_blank" rel="nofollow noopener" aria-label="{{ ucfirst($social['type']) }}">
                                            @include('frontend::layouts.shared.footer-social-icon', ['type' => $social['type']])
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="footer-nav-grid">
                        @foreach ($footerNavColumns as $column)
                            <div class="footer-nav-col">
                                <p class="footer-nav-heading mb-0">{{ $column['title'] }}</p>
                                <nav class="footer-nav" aria-label="{{ $column['title'] }}">
                                    <ul class="list-unstyled mb-0">
                                        @foreach ($column['links'] as $link)
                                            <li>
                                                <a href="{{ $link['url'] }}" class="footer-nav-link text-reset">
                                                    {{ $link['label'] }}
                                                    @if (!empty($link['soon']))
                                                        <span class="footer-soon-badge">{{ __('frontend::common.footer_soon_badge') }}</span>
                                                    @endif
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </nav>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- BOTTOM BAR --}}
        <div class="footer-bar footer-bar-bottom">
            <div class="container">
                <div class="footer-bar-bottom-inner">
                    <p class="footer-copyright mb-0">{{ __('frontend::common.footer_copyright') }}</p>
                    <nav class="footer-legal-links" aria-label="Legal">
                        <a href="{{ $privacyPolicyUrl }}" class="footer-legal-link text-reset">{{ __('frontend::common.footer_privacy') }}</a>
                        <span class="footer-legal-sep" aria-hidden="true">|</span>
                        <a href="{{ $legalUrl }}#terms" class="footer-legal-link text-reset">{{ __('frontend::common.footer_terms') }}</a>
                        <span class="footer-legal-sep" aria-hidden="true">|</span>
                        <a href="{{ $privacyPolicyUrl }}#cookies" class="footer-legal-link text-reset">{{ __('frontend::common.footer_cookies') }}</a>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</footer>
