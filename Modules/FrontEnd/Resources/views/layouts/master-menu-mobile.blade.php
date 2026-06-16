@php
    $languageCode = \Modules\FrontEnd\Helpers\FeLanguageUtils::getRouteLanguageCode();
@endphp
<div id="menu-mobile" class="d-md-none">
    <div class="overlay"></div>
    <div class="menu-mobile-outer">
        <div class="menu-mobile-inner">
            <div class="header d-flex align-items-center justify-content-between">
                @if (array_key_exists('website-logo', $config) && $config['website-logo'])
                    <a href="{{ route(Utilities::getRouteName('frontend.index'), ['languageCode' => $languageCode]) }}" class="logo d-block">
                        <img src="{{ FeUtils::getImageLink($config['website-logo']) }}" alt="{{ $config['website-name'] }}" class="img-fluid" width="117" height="93" />
                    </a>
                @endif
                <a class="btn-close-menu" href="javascript:;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M18 6l-12 12"/><path d="M6 6l12 12"/>
                    </svg>
                </a>
            </div>
            <div class="body">
                @if (count($listMenuPrimary) > 0)
                    <nav class="navigation">
                        <ul class="list-unstyled list-item">
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
                                <li class="item {{ $hasChild ? 'has-child' : '' }} {{ $isActive ? 'active' : '' }}">
                                    <a href="{{ $listMenuPrimary[$i]->url }}" class="item-body d-flex align-items-center text-uppercase justify-content-between" target="{{ $listMenuPrimary[$i]->target }}">
{{--                                        @if ($listMenuPrimary[$i]->icon)--}}
{{--                                            <i class="{{ $listMenuPrimary[$i]->icon }}"></i>--}}
{{--                                        @endif--}}
                                        <span>{{ FeUtils::formatGreenRubyMenuName($listMenuPrimary[$i]->name) }}</span>
                                        @if ($hasChild)
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M9 6l6 6l-6 6"/>
                                            </svg>
                                        @endif
                                    </a>
                                    @if ($hasChild)
                                        <div class="menu-child-wrapper">
                                            <ul class="list-item menu-child menu-lv1 list-unstyled">
                                                @for ($j = 0; $j < count($listMenuPrimary[$i]->child); $j++)
                                                    @php
                                                        $isChildActive = FeUtils::isMenuItemActive($menuUrlActive ?? null, $listMenuPrimary[$i]->child[$j]->url);
                                                    @endphp
                                                    <li class="item {{ $isChildActive ? 'active' : '' }}">
                                                        <a class="item-body d-flex align-items-center justify-content-between" href="{{ $listMenuPrimary[$i]->child[$j]->url }}" target="{{ $listMenuPrimary[$i]->child[$j]->target }}">
                                                            <span>{{ FeUtils::formatGreenRubyMenuName($listMenuPrimary[$i]->child[$j]->name) }}</span>
                                                        </a>
                                                    </li>
                                                @endfor
                                            </ul>
                                        </div>
                                    @endif
                                </li>
                            @endfor
                        </ul>
                    </nav>
                @endif
            </div>
            <div class="footer px-3">
                <div class="footer-inner py-3 d-flex align-items-center justify-content-between">
                    <a href="{{ route(Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode]) }}" class="btn btn-warning btn-book-now d-flex align-items-center justify-content-center">
                        <span class="label font-weight-bold mr-3">{{ __('frontend::common.book_now') }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M11.5 21h-5.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v6"/>
                            <path d="M16 3v4"/><path d="M8 3v4"/><path d="M4 11h16"/><path d="M15 19l2 2l4 -4"/>
                        </svg>
                    </a>
                    <div class="list-language">
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
                </div>
            </div>
        </div>
    </div>
</div>
