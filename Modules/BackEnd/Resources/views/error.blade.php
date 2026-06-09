<!DOCTYPE html>
<html lang="vi">
<head>
    <base href="./">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>{{ $statusCode }}</title>
    <link href="{{ asset('/assets/backend/themes/coreui/css/style.css') }}" rel="stylesheet" />
</head>
<body class="c-app flex-row align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="clearfix">
                    <h1 class="float-left display-3 mr-4">{{ $statusCode }}</h1>
                    <h4 class="pt-3">{{ Utilities::getErrorMessageByStatusCode($statusCode) }}</h4>
                    <p class="text-muted">Bạn vui lòng bấm vào <a href="{{ route('backend.index') }}"><strong>đây</strong></a> để quay về trang chủ.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('/assets/backend/themes/coreui/vendors/@@coreui/coreui/js/coreui.bundle.min.js') }}"></script>
    <!--[if IE]><!-->
    <script src="{{ asset('/assets/backend/themes/coreui/vendors/@@coreui/icons/js/svgxuse.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/themes/coreui/js/tooltips.js') }}"></script>
    <!--<![endif]-->
</body>
</html>