<!DOCTYPE html>
<html lang="vi" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="robots" content="noindex,nofollow">
    <link rel="shortcut icon" href="{{ asset('/favicon.png') }}" type="image/x-icon">
    {!! SEOMeta::generate() !!}
    <link href="{{ asset('/assets/frontend/plugins/font-awesome/css/all.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/frontend/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/frontend/css/style.css?v=0.0.9') }}" rel="stylesheet" />
</head>
<body class="d-flex align-items-center h-100 p-4">
    <p class="h4 w-100 mb-0 font-weight-normal text-center text-danger"><i class="fas fa-exclamation-triangle"></i> {{ $statusCode }}, {{ Utilities::getErrorMessageByStatusCode($statusCode) }}</p>
</body>
</html>