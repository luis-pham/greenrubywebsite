@php
    $languageCode = \Modules\FrontEnd\Helpers\FeLanguageUtils::getRouteLanguageCode();
@endphp
<div id="fabs">
    <div class="list-btn">
        <a href="{{route(Utilities::getRouteName('frontend.booking'),['languageCode' => $languageCode])}}" class="btn btn-warning btn-book-now mobile-btn">
            <span>Book now</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M11.5 21h-5.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v6"/>
                <path d="M16 3v4"/><path d="M8 3v4"/><path d="M4 11h16"/><path d="M15 19l2 2l4 -4"/>
            </svg>
        </a>
        <a href="javascript:;" class="btn btn-tooltip btn-chat-with-ai mobile-btn icon-btn" data-toggle="tooltip" data-title="{{__('frontend::page.fabs.chat-with-ai')}}" data-placement="left">
            <span>AI chatbot</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M6 4m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z"/>
                <path d="M12 2v2"/><path d="M3 20l3 -3"/><path d="M21 20l-3 -3"/>
                <path d="M3 13h2"/><path d="M19 13h2"/>
                <path d="M9 12v4"/><path d="M12 12v4"/><path d="M15 12v4"/>
                <path d="M6 17h12a1 1 0 0 1 1 1v1a1 1 0 0 1 -1 1h-12a1 1 0 0 1 -1 -1v-1a1 1 0 0 1 1 -1z"/>
                <path d="M9 7l0 .01"/><path d="M15 7l0 .01"/>
            </svg>
        </a>
        @if (array_key_exists('hotline', $config) && $config['hotline'])
            <a href="{{ 'tel:' . $config['hotline'] }}" class="btn btn-tooltip btn-direct-chat mobile-btn icon-btn" data-toggle="tooltip" data-title="{{__('frontend::page.fabs.direct-contact')}}" data-placement="left">
                <span>Direct contact</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9"/>
                    <path d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1"/>
                </svg>
            </a>
        @endif
        <a href="javascript:;" class="btn btn-navigate-to-top icon-btn" title="{{__('frontend::page.fabs.navigate-to-top')}}" >
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M6 15l6 -6l6 6"/>
            </svg>
        </a>
    </div>
</div>
