@php
    $completeLanguageCode = \Route::current() ? \Route::current()->parameter('languageCode') : null;
    $completeHomeUrl = $completeLanguageCode
        ? route(Utilities::bindRouteNameMultiLanguage('frontend.index'), ['languageCode' => $completeLanguageCode])
        : route('frontend.index');
@endphp
<div class="complete-column" id="complete-panel">
    <div class="complete-card">
        <div class="complete-icon" id="complete-icon">
            <i class="fa-solid fa-hourglass-half"></i>
        </div>
        <h3 class="complete-title" id="complete-title">
            {{ __('frontend::booking.complete_pending_title') }}
        </h3>
        <p class="complete-message" id="complete-message">
            {{ __('frontend::booking.complete_pending_message') }}
        </p>
        <button type="button" class="complete-back-button" onclick="window.location.href='{{ $completeHomeUrl }}'">
            &#8592; {{ __('frontend::booking.button_back_to_home') }}
        </button>
    </div>
</div>

