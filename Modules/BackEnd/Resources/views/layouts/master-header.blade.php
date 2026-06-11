<header class="c-header c-header-light c-header-fixed c-header-with-subheader d-flex justify-content-between">
    <div class="d-flex justify-content-start align-items-center">
        <button class="c-header-toggler c-class-toggler d-lg-none mfe-auto" type="button" data-target="#sidebar" data-class="c-sidebar-show">
            <svg class="c-icon c-icon-lg">
                <use xlink:href="/assets/backend/themes/coreui/vendors/@@coreui/icons/svg/free.svg#cil-menu"></use>
            </svg>
        </button>
        <button class="c-header-toggler c-class-toggler mfs-3 d-md-down-none" type="button" data-target="#sidebar" data-class="c-sidebar-lg-show" responsive="true">
            <svg class="c-icon c-icon-lg">
                <use xlink:href="/assets/backend/themes/coreui/vendors/@@coreui/icons/svg/free.svg#cil-menu"></use>
            </svg>
        </button>
        @if (count($listLanguage) > 0)
            <ul class="c-header-nav">
                @if (count($listLanguage) > 1)
                    <li class="c-header-nav-item dropdown">
                        <a href="#" class="c-header-nav-link px-0 py-3 px-sm-2" id="btn-change-language" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img src="{{ asset($currentLanguage->image_link) }}" alt="{{ $currentLanguage->code }}" class="img-fluid" />
                            <span class="ml-2">{{ mb_strlen($currentLanguage->name) < 12 ? $currentLanguage->name : mb_substr($currentLanguage->name, 0, 12) . '...' }}</span> <span class="ml-2"><i class="fas fa-caret-down"></i></span>
                        </a>
                        <div class="dropdown-menu py-0" aria-labelledby="btn-change-language">
                            @for ($i = 0; $i < count($listLanguage); $i++)
                                @php
                                    if ($listLanguage[$i]->id != $currentLanguage->id) {
                                        $url = route('backend.changeLanguage', ['languageId' => $listLanguage[$i]->id]);
                                    } else {
                                        $url = 'javascript:;';
                                    }
                                @endphp
                                <a href="{{ $url }}" class="dropdown-item px-3 py-2">{{ $listLanguage[$i]->name }}</a>
                            @endfor
                        </div>
                    </li>
                @else
                    <li class="c-header-nav-item">
                        <a href="javascript:;" class="c-header-nav-link px-0 py-3 px-sm-2">
                            <img src="{{ asset($currentLanguage->image_link) }}" alt="{{ $currentLanguage->code }}" class="img-fluid" />
                            {{ mb_strlen($currentLanguage->name) < 12 ? $currentLanguage->name : mb_substr($currentLanguage->name, 0, 12) . '...' }}</span>
                        </a>
                    </li>
                @endif
            </ul>
        @endif
    </div>
    <ul class="c-header-nav ml-auto mr-4">
        <li class="c-header-nav-item d-md-down-none mx-2">
            <button class="c-class-toggler c-header-nav-btn" type="button" id="btn-update-theme" data-toggle="c-tooltip" data-placement="bottom" title="Đổi giao diện" data-ajax-url="{{ route('backend.personal.updateTheme') }}">
                <svg class="c-icon c-d-dark-none">
                    <use xlink:href="/assets/backend/themes/coreui/vendors/@@coreui/icons/svg/free.svg#cil-moon"></use>
                </svg>
                <svg class="c-icon c-d-default-none">
                    <use xlink:href="/assets/backend/themes/coreui/vendors/@@coreui/icons/svg/free.svg#cil-sun"></use>
                </svg>
            </button>
        </li>
        <li class="c-header-nav-item d-md-down-none mx-2">
            <a class="c-header-nav-link" href="{{ route('backend.personal.edit') }}" title="Thông tin cá nhân">
                <svg class="c-icon">
                    <use xlink:href="/assets/backend/themes/coreui/vendors/@@coreui/icons/svg/free.svg#cil-user"></use>
                </svg>
            </a>
        </li>
        <li class="c-header-nav-item d-md-down-none mx-2">
            <a class="c-header-nav-link" href="{{ route('backend.personal.changePasswordEdit') }}" title="Đổi mật khẩu">
                <svg class="c-icon">
                    <use xlink:href="/assets/backend/themes/coreui/vendors/@@coreui/icons/svg/free.svg#cil-lock-locked"></use>
                </svg>
            </a>
        </li>
        <li class="c-header-nav-item dropdown">
            <a class="c-header-nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                <div class="c-avatar">
                    <img class="c-avatar-img" src="{{ Utilities::getAvatar(\Auth::user()->avatar) }}" alt="Avatar">
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-right pt-0">
                <div class="dropdown-header bg-light py-2"><strong>Thông tin tài khoản</strong></div>
                <a class="dropdown-item" href="{{ route('backend.personal.edit') }}">
                    <div class="c-icon mr-2">
                        <i class="fas fa-user"></i>
                    </div>
                    Thông tin cá nhân
                </a>
                <a class="dropdown-item" href="{{ route('backend.personal.changePasswordEdit') }}">
                    <div class="c-icon mr-2">
                        <i class="fas fa-key"></i>
                    </div>
                    Đổi mật khẩu
                </a>
                <form method="POST" action="{{ route('backend.auth.logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item border-0 bg-transparent w-100 text-left">
                        <div class="c-icon mr-2">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                        Đăng xuất
                    </button>
                </form>
            </div>
        </li>
    </ul>
</header>