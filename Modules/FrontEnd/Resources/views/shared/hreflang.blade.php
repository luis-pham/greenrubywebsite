@php
    $hreflangLinks = \Modules\FrontEnd\Helpers\FeHreflangUtils::getAlternateLinks();
@endphp
@foreach ($hreflangLinks as $link)
    <link rel="alternate" hreflang="{{ $link['hreflang'] }}" href="{{ $link['url'] }}">
@endforeach
