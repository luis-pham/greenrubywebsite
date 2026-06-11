@php
    $languageCode = Route::current()->parameter('languageCode');
    $headerLogo = !empty($config['website-logo-negative'])
        ? $config['website-logo-negative']
        : ($config['website-logo'] ?? null);
@endphp
<header id="header">
    <div class="container-fluid position-relative">
        <div class="header-desktop d-none d-md-flex align-items-center justify-content-between position-relative text-white">
            <div class="header-left d-flex flex-nowrap align-items-center justify-content-start">
                @if (count($listMenuPrimary) > 0)
                    <div class="btn-toggle-menu-ext position-relative">
                        <a href="javascript:;" class="btn-function d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-bars"></i>
                        </a>
                        <ul class="menu-ext list-unstyled mb-0 position-absolute">
                            @for ($i = 0; $i < count($listMenuPrimary); $i++)
                                <li>
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
                                <li class="position-relative {{ $hasChild ? 'has-child' : '' }} {{ $isActive ? 'active' : '' }}">
                                    <a href="{{ $listMenuPrimary[$i]->url }}" class="d-flex align-items-center font-weight-bold text-center text-uppercase" target="{{ $listMenuPrimary[$i]->target }}">
                                        @if ($listMenuPrimary[$i]->icon)
                                            <i class="{{ $listMenuPrimary[$i]->icon }}"></i>
                                        @endif
                                        <span class="{{ $hasChild ? 'mr-2' : '' }}">{{ FeUtils::formatGreenRubyMenuName($listMenuPrimary[$i]->name) }}</span>
                                        @if ($hasChild)
                                            <i class="fa-solid fa-chevron-down"></i>
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
                @if ($headerLogo)
                    @php
                        $isHome = FeUtils::isHome();
                    @endphp
                    @if ($isHome) <h1 class="mb-0"> @endif
                        <a href="{{ route(Utilities::getRouteName('frontend.index'), ['languageCode' => $languageCode]) }}" class="d-block">
                            <img src="{{ Utilities::getFileLink($headerLogo) }}" alt="{{ $config['website-name'] }}" class="img-fluid" width="117" height="93" />
                        </a>
                    @if ($isHome) </h1> @endif
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
                        <i class="fa-solid fa-calendar-check"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="header-mobile d-flex d-md-none align-items-center justify-content-between position-relative text-white">
            <div class="header-left">
                @if ($headerLogo)
                    <a href="{{ route(Utilities::getRouteName('frontend.index'), ['languageCode' => $languageCode]) }}" class="d-block">
                        <img src="{{ FeUtils::getImageLink($headerLogo) }}" alt="{{ $config['website-name'] }}" class="img-fluid" width="117" height="93" />
                    </a>
                @endif
            </div>
            <div class="header-right d-flex align-items-center">
                <a href="{{ route(Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode]) }}" class="btn-book-now d-flex align-items-center justify-content-center border-0 mr-3">
                    <span class="label font-weight-bold">{{ __('frontend::common.book_now') }}</span>
                    <i class="fa-solid fa-calendar-check"></i>
                </a>
                <a href="javascript:;" class="btn-expand-menu-mobile d-flex align-items-center justify-content-center" title="Toggle menu mobile">
                    <i class="fa-solid fa-bars"></i>
                </a>
            </div>
        </div>
    </div>
</header>
