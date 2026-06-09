@php
    $gtmId = array_key_exists('google-tag-manager-id', $config) && $config['google-tag-manager-id'] ? $config['google-tag-manager-id'] : '';
@endphp

@if ($gtmId)
    @php
        $cookieAccepted = Request::cookie('cookie_consent');
    @endphp

    @if ($cookieAccepted == 'accepted')
        @push('scripts')
            <!-- Google Tag Manager -->
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','{{ $gtmId }}');</script>
            <!-- End Google Tag Manager -->
            <!-- Google Tag Manager (noscript) -->
            <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
            <!-- End Google Tag Manager (noscript) -->
        @endpush
    @endif

    @if (!$cookieAccepted)    
        <div id="cookie-banner" class="cookie-banner" data-gtm-id="{{ $gtmId }}">
            <div class="wrapper">
                {{-- <div class="icon">🍪</div> --}}
                <div class="text">
                    <p class="title mb-1 font-weight-bold">{{ __('frontend::common.cookie_consent_title') }}</strong>
                    <p class="mb-0">{{ __('frontend::common.cookie_consent_description') }}</p>
                </div>
                <div class="actions">
                    <button onclick="Cookie.prototype.setConsent('declined')" class="btn btn-outline-secondary btn-decline">{{ __('frontend::common.cookie_consent_button_decline') }}</button>
                    <button onclick="Cookie.prototype.setConsent('accepted')" class="btn btn-success btn-accept">{{ __('frontend::common.cookie_consent_button_accept') }}</button>
                </div>
            </div>
        </div>
    @endif

    @if ($cookieAccepted == 'declined')
        @push('scripts')
            <script>window.dataLayer = window.dataLayer || [];</script>
        @endpush
    @endif
@endif