<!DOCTYPE html>
<html lang="vi">
<head>
    <base href="./">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="robots" content="noindex,nofollow">
    @if (array_key_exists('website-icon', $config) && $config['website-icon'])
        <link rel="shortcut icon" href="{{ asset(Utilities::getFileLink($config['website-icon'])) }}" type="image/x-icon">
    @endif
    {!! SEOMeta::generate() !!}
    <link href="{{ asset('/assets/backend/themes/coreui/css/style.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/assets/backend/themes/coreui/vendors/@@coreui/icons/css/free.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/assets/backend/css/style.css?v=1.0.1') }}" rel="stylesheet" type="text/css" />
</head>

<body class="c-app flex-row align-items-center">
    <div id="login" class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card-group mb-3">
                    <div class="card p-4">
                        <div class="card-body">
                            <h1 class="page-title mb-4 text-center text-uppercase">Hệ thống Quản trị</h1>
                            @include('backend::shared.message')
                            {{ Form::open(['route' => ['backend.auth.login', 'lastUrl=' . urlencode(request()->query('lastUrl'))]]) }}
                                <div class="mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <svg class="c-icon">
                                                    <use xlink:href="{{ asset('/assets/backend/themes/coreui/vendors/@@coreui/icons/svg/free.svg#cil-user') }}"></use>
                                                </svg>
                                            </span>
                                        </div>
                                        <input type="text" name="username" class="form-control" maxlength="50" placeholder="Tên đăng nhập" autocomplete="off" />
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <svg class="c-icon">
                                                    <use xlink:href="{{ asset('/assets/backend/themes/coreui/vendors/@@coreui/icons/svg/free.svg#cil-lock-locked') }}"></use>
                                                </svg>
                                            </span>
                                        </div>
                                        <input type="password" name="password" class="form-control" maxlength="50" placeholder="Mật khẩu" autocomplete="off" />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        {{ Form::button('Đăng nhập', ['type' => 'submit', 'class' => 'btn btn-primary btn-block']) }}
                                    </div>
                                </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
                @if (array_key_exists('website-name', $config) && $config['website-name'])
                    <div class="text-center">
                        <p class="text-uppercase mb-0">2026 © {{ $config['website-name'] }}.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="{{ asset('/assets/backend/themes/coreui/vendors/@@coreui/coreui/js/coreui.bundle.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/themes/coreui/vendors/@@coreui/icons/js/svgxuse.min.js') }}"></script>
</body>
</html>