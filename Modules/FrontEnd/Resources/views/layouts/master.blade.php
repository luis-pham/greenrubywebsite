@php
    $config = isset($config) ? $config : [];
    $languageCode = \Modules\FrontEnd\Helpers\FeLanguageUtils::getRouteLanguageCode();
    $routeLanguageParams = $languageCode ? ['languageCode' => $languageCode] : [];
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no, viewport-fit=cover">
    <meta name="theme-color" content="#0c3d31">
    @if (array_key_exists('block-search-engine', $config) && ($config['block-search-engine'] ?: 'false') == 'true')
        <meta name="robots" content="noindex,nofollow">
    @endif
    @if (array_key_exists('website-icon', $config) && $config['website-icon'])
        <link rel="shortcut icon" href="{{ asset(Utilities::getFileLink($config['website-icon'])) }}" type="image/x-icon">
    @endif
    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! TwitterCard::generate() !!}
    @include('frontend::shared.hreflang')
    @stack('preload')
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
    @include('frontend::shared.modal-cabin-details')

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
            searchTour: '{{ route(Utilities::getRouteName('frontend.index.search-tour'), $routeLanguageParams) }}'
        };
        let apiCabin = {
            getById: '{{ route(Utilities::getRouteName('frontend.api.cabin.getById'), $routeLanguageParams) }}'
        };
        let apiService = {
            getById: '{{ route(Utilities::getRouteName('frontend.api.service.getById'), $routeLanguageParams) }}',
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
