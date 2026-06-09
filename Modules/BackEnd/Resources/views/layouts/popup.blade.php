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
    <link href="{{ asset('/assets/backend/themes/coreui/css/style.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/backend/plugins/font-awesome/css/pro.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/backend/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/backend/plugins/toastr/toastr.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/backend/plugins/datetimepicker/css/tempusdominus-bootstrap-4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/backend/plugins/dropzone/min/dropzone.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/backend/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/backend/css/style.css?v=1.0.1') }}" rel="stylesheet" />
    @yield('styles')
</head>
<body class="c-app d-block mb-0 {{ \Auth::user()->theme == config('backend.userTheme.dark') ? 'c-dark-theme' : '' }}">
    @csrf
    <main>
        <div class="fade-in">
            @yield('content')
        </div>
    </main>

    <div id="loading" class="loading">
        <div class="wrapper">
            <img src="{{ asset('/assets/backend/images/icon-loading.gif') }}" class="icon" alt="Loading" />
        </div>
    </div>

    <script src="{{ asset('/assets/backend/themes/coreui/vendors/@@coreui/coreui/js/coreui.bundle.min.js') }}"></script>
    <!--[if IE]><!-->
    <script src="{{ asset('/assets/backend/themes/coreui/vendors/@@coreui/icons/js/svgxuse.min.js') }}"></script>
    <!--<![endif]-->
    <script src="{{ asset('/assets/backend/plugins/jquery/jquery-3.5.1.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/bootstrap-bundle/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/datetimepicker/locales/vi.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/datetimepicker/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/checkall/checkall.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/genalias/genalias.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/dropzone/min/dropzone.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/imageselect/imageselect.js') }}"></script>
    <script src="{{ asset('/assets/backend/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.js') }}"></script>
    <script src="{{ asset('/assets/backend/js/script.js?v=1.0.1') }}"></script>
    @yield('scripts')
</body>
</html>