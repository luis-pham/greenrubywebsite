@php
    $languageCode = \Modules\FrontEnd\Helpers\FeLanguageUtils::getRouteLanguageCode();
    // Image hero → normal logo; solid-color hero → negative/light logo
    $headerHeroType = trim($__env->yieldContent('headerHeroType', 'image'));
    $headerLogoNormal = $config['website-logo'] ?? null;
    $headerLogoNegative = !empty($config['website-logo-negative'])
        ? $config['website-logo-negative']
        : '/upload/2026/02/06/logo-negative.png';
    $headerLogo = $headerHeroType === 'solid'
        ? ($headerLogoNegative ?: $headerLogoNormal)
        : ($headerLogoNormal ?: $headerLogoNegative);
    $headerLogoSrc = $headerLogo
        ? FeUtils::getThumbnail(['link' => $headerLogo, 'w' => 280, 'h' => 224, 'q' => 80, 'cr' => 1])
        : null;
    $primaryMenuNavKeys = [];
    for ($menuIndex = 0; $menuIndex < count($listMenuPrimary); $menuIndex++) {
        $primaryMenuNavKeys[$menuIndex] = FeUtils::resolvePrimaryMenuNavKey($listMenuPrimary[$menuIndex]);
    }
@endphp
<header id="header">
    <div class="container-fluid position-relative">
        <div class="header-desktop d-none d-md-flex align-items-center justify-content-between position-relative text-white">
            <div class="header-left d-flex flex-nowrap align-items-center justify-content-start">
                @if (count($listMenuPrimary) > 0)
                    <div class="btn-toggle-menu-ext position-relative">
                        <a href="javascript:;" class="btn-function d-flex align-items-center justify-content-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M4 6l16 0"/><path d="M4 12l16 0"/><path d="M4 18l16 0"/>
                            </svg>
                        </a>
                        <ul class="menu-ext list-unstyled mb-0 position-absolute">
                            @for ($i = 0; $i < count($listMenuPrimary); $i++)
                                <li class="nav-item nav-item--{{ $primaryMenuNavKeys[$i] }}">
                                    <a href="{{ $listMenuPrimary[$i]->url }}" target="{{ $listMenuPrimary[$i]->target }}">{{ FeUtils::formatGreenRubyMenuName($listMenuPrimary[$i]->name) }}</a>
                                </li>
                            @endfor
                        </ul>
                    </div>
                    <nav class="navigation">
                        <ul class="d-flex flex-nowrap mb-0 list-unstyled">
                            @for ($i = 0; $i < count($listMenuPrimary); $i++)
                                @php
                                    $hasChild = count($listMenuPrimary[$i]->child) > 0;
                                    $isActive = FeUtils::isMenuItemActive($menuUrlActive ?? null, $listMenuPrimary[$i]->url);
                                    if (!$isActive && $hasChild) {
                                        foreach ($listMenuPrimary[$i]->child as $childMenuItem) {
                                            if (FeUtils::isMenuItemActive($menuUrlActive ?? null, $childMenuItem->url)) {
                                                $isActive = true;
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                <li class="position-relative nav-item nav-item--{{ $primaryMenuNavKeys[$i] }} {{ $hasChild ? 'has-child' : '' }} {{ $isActive ? 'active' : '' }}">
                                    <a href="{{ $listMenuPrimary[$i]->url }}" class="d-flex align-items-center font-weight-bold text-center text-uppercase" target="{{ $listMenuPrimary[$i]->target }}">
                                        @if ($listMenuPrimary[$i]->icon)
                                            <i class="{{ $listMenuPrimary[$i]->icon }}"></i>
                                        @endif
                                        <span class="{{ $hasChild ? 'mr-2' : '' }}">{{ FeUtils::formatGreenRubyMenuName($listMenuPrimary[$i]->name) }}</span>
                                        @if ($hasChild)
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M6 9l6 6l6 -6"/>
                                            </svg>
                                        @endif
                                    </a>
                                    @if ($hasChild)
                                        <ul class="menu-ext list-unstyled mb-0 position-absolute">
                                            @for ($j = 0; $j < count($listMenuPrimary[$i]->child); $j++)
                                                <li>
                                                    <a href="{{ $listMenuPrimary[$i]->child[$j]->url }}" target="{{ $listMenuPrimary[$i]->child[$j]->target }}">{{ FeUtils::formatGreenRubyMenuName($listMenuPrimary[$i]->child[$j]->name) }}</a>
                                                </li>
                                            @endfor
                                        </ul>
                                    @endif
                                </li>
                            @endfor
                        </ul>
                    </nav>
                @endif
                @if (count($listLanguage) > 0)
                    <div class="language">
                        <ul class="list-item d-flex flex-nowrap mb-0 list-unstyled font-weight-bold">
                            @for ($i = 0; $i < count($listLanguage); $i++)
                                <?php
                                    $isActive = isset($currentLanguage) && $listLanguage[$i]->id == $currentLanguage->id;
                                ?>
                                <li class="float-left {{ $isActive ? 'active' : '' }}">
                                    @if (isset($currentLanguage) && $listLanguage[$i]->id == $currentLanguage->id)
                                        {{ $listLanguage[$i]->short_name }}
                                    @else
                                        @php
                                            $url = FeUtils::getUrlMultiLanguage($listLanguage[$i]);
                                        @endphp
                                        <a href="{{ $url }}">{{ $listLanguage[$i]->short_name }}</a>
                                    @endif
                                </li>
                            @endfor
                        </ul>
                    </div>
                @endif
            </div>
            <div class="header-center justify-content-center">
                @if ($headerLogoSrc)
                    <a href="{{ route(Utilities::getRouteName('frontend.index'), ['languageCode' => $languageCode]) }}" class="d-block">
                        <img src="{{ $headerLogoSrc }}" alt="{{ $config['website-name'] }}" class="img-fluid" width="117" height="93" />
                    </a>
                @endif
            </div>
            <div class="header-right justify-content-end">
                <div class="d-flex align-items-center justify-content-end">
                    @php
                        $listContact = [];
                        if (array_key_exists('hotline', $config) && $config['hotline']) {
                            $listContact[] = [
                                'label' => __('frontend::common.hotline'),
                                'value' => $config['hotline'],
                                'href' => 'tel:' . $config['hotline']
                            ];
                        }
                        if (array_key_exists('email', $config) && $config['email']) {
                            $listContact[] = [
                                'label' => __('frontend::common.email'),
                                'value' => $config['email'],
                                'href' => 'mailto:' . $config['email']
                            ];
                        }
                    @endphp
                    @if (count($listContact) > 0)
                        <div class="contact d-none d-lg-block">
                            <div class="list-item d-flex">
                                @for ($i = 0; $i < count($listContact); $i++)
                                    <a href="{{ $listContact[$i]['href'] }}" class="item d-flex align-items-center" rel="nofollow">
                                        <div class="text">
                                            <span class="label">{{ $listContact[$i]['label'] }}:</span>
                                            <span class="value font-weight-bold">{{ $listContact[$i]['value'] }}</span>
                                        </div>
                                    </a>
                                @endfor
                            </div>
                        </div>
                    @endif
                    <a href="{{ route(Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode]) }}" class="btn-book-now d-flex align-items-center justify-content-center border-0">
                        <span class="label font-weight-bold">{{ __('frontend::common.book_now') }}</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="header-mobile d-flex d-md-none align-items-center justify-content-between position-relative text-white">
            <div class="header-left d-flex align-items-center">
                <a href="javascript:;" class="btn-expand-menu-mobile d-flex align-items-center justify-content-center" title="Toggle menu mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M4 6l16 0"/><path d="M4 12l16 0"/><path d="M4 18l16 0"/>
                    </svg>
                </a>
            </div>
            <div class="header-center">
                @if ($headerLogoSrc)
                    <a href="{{ route(Utilities::getRouteName('frontend.index'), ['languageCode' => $languageCode]) }}" class="d-block">
                        <img src="{{ $headerLogoSrc }}" alt="{{ $config['website-name'] }}" class="img-fluid" width="140" height="112" />
                    </a>
                @endif
            </div>
            <div class="header-right d-flex align-items-center justify-content-end">
                <a href="{{ route(Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode]) }}" class="btn-book-now d-flex align-items-center justify-content-center border-0">
                    <span class="label font-weight-bold">{{ __('frontend::common.book_now') }}</span>
                </a>
            </div>
        </div>
    </div>
</header>
