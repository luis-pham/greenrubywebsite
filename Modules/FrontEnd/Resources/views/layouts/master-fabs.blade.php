@php
    $languageCode = Route::current()->parameter('languageCode');
@endphp
<div id="fabs">
    <div class="list-btn">
        <a href="{{route(Utilities::getRouteName('frontend.booking'),['languageCode' => $languageCode])}}" class="btn btn-warning btn-book-now mobile-btn">
            <span>Book now</span>
            <i class="fas fa-calendar-check"></i>
        </a>
        <a href="javascript:;" class="btn btn-tooltip btn-chat-with-ai mobile-btn icon-btn" data-toggle="tooltip" data-title="{{__('frontend::page.fabs.chat-with-ai')}}" data-placement="left">
            <span>AI chatbot</span>
            <i class="fas fa-robot"></i>
        </a>
        @if (array_key_exists('hotline', $config) && $config['hotline'])
            <a href="{{ 'tel:' . $config['hotline'] }}" class="btn btn-tooltip btn-direct-chat mobile-btn icon-btn" data-toggle="tooltip" data-title="{{__('frontend::page.fabs.direct-contact')}}" data-placement="left">
                <span>Direct contact</span>
                <i class="fa-brands fa-whatsapp"></i>
            </a>
        @endif
        <a href="javascript:;" class="btn btn-navigate-to-top icon-btn" title="{{__('frontend::page.fabs.navigate-to-top')}}" >
            <i class="fas fa-chevron-up"></i>
        </a>
    </div>
</div>
