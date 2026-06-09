@php
    $config = isset($config) ? $config : [];
    $languageCode = Route::current()->parameter('languageCode');
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="theme-color" content="#083a2e">
    @if (array_key_exists('block-search-engine', $config) && ($config['block-search-engine'] ?: 'false') == 'true')
        <meta name="robots" content="noindex,nofollow">
    @endif
    @if (array_key_exists('website-icon', $config) && $config['website-icon'])
        <link rel="shortcut icon" href="{{ asset(Utilities::getFileLink($config['website-icon'])) }}" type="image/x-icon">
    @endif
    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! TwitterCard::generate() !!}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap" rel="stylesheet">
    @if (!isset($disableDefaultAppCss) || !$disableDefaultAppCss)
        <link href="{{ mix('assets/frontend/dist/css/app.css') }}" rel="stylesheet" />
    @endif
    @stack('styles')
    @if (array_key_exists('embed-code-head-tag', $config) && $config['embed-code-head-tag'])
        {!! $config['embed-code-head-tag'] !!}
    @endif
</head>
<body>
    @csrf

    @include('frontend::layouts.master-header')

    <main id="main">
        @yield('content')
    </main>

    @include('frontend::layouts.master-footer')
    @include('frontend::layouts.master-menu-mobile')
    @include('frontend::layouts.master-fabs')
    @include('frontend::layouts.master-cookie-consent')

    <div id="loading" class="loading">
        <div class="wrapper">
            <img src="{{ asset('/assets/frontend/images/icon-loading.gif') }}" class="icon" alt="Loading" />
        </div>
    </div>

    <script type="text/javascript">
        let commonLabel = {
            departureDate: '{{ __('frontend::common.departure_date') }}',
            viewFullScreen: '{{ __('frontend::common.view_full_screen') }}',
            functionUnderDevelopment: '{{ __('frontend::common.function_under_development') }}'
        };
        let apiHomepage = {
            searchTour: '{{ route(Utilities::getRouteName('frontend.index.search-tour'), ['languageCode' => $languageCode]) }}'
        };
        let apiCabin = {
            getById: '{{ route(Utilities::getRouteName('frontend.api.cabin.getById'), ['languageCode' => $languageCode]) }}'
        };
        let apiService = {
            getById: '{{ route(Utilities::getRouteName('frontend.api.service.getById'), ['languageCode' => $languageCode]) }}',
            priceFormat: '{!! addslashes(sprintf(__('frontend::service.price_per_guest'), '__PRICE__')) !!}'
        };
        let apiCookie = {
            consent: '{{ route('frontend.cookie.consent') }}'
        };
    </script>
    @if (!isset($disableDefaultAppJs) || !$disableDefaultAppJs)
        <script src="{{ mix('assets/frontend/dist/js/app.js') }}" defer></script>
    @endif
    @stack('scripts')
</body>
</html>
