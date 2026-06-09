<?php
$link = isset($link) ? $link : null;
$alt = isset($alt) ? $alt : null;
$fetchpriority = isset($fetchpriority) ? $fetchpriority : null;
$loading = isset($loading) ? $loading : ($fetchpriority === 'high' ? 'eager' : 'lazy');
$decoding = isset($decoding) ? $decoding : 'async';

$imageLinkRouteParam = ['link' => $link];
$imageConfig = $imageConfig ?? [];
if (array_key_exists('w', $imageConfig)) {
    $imageLinkRouteParam['w'] = $imageConfig['w'];
}
if (array_key_exists('h', $imageConfig)) {
    $imageLinkRouteParam['h'] = $imageConfig['h'];
}
if (array_key_exists('cr', $imageConfig)) {
    $imageLinkRouteParam['cr'] = $imageConfig['cr'];
}
?>
<div class="image-wrapper {{ isset($ratio) ? 'image-' . $ratio : '' }} position-relative">
    <img
        src="{{ FeUtils::getThumbnail($imageLinkRouteParam) }}"
        alt="{{ $alt }}"
        class="position-absolute w-100 h-100"
        loading="{{ $loading }}"
        decoding="{{ $decoding }}"
        {!! $fetchpriority ? 'fetchpriority="' . $fetchpriority . '"' : '' !!}
    />
</div>