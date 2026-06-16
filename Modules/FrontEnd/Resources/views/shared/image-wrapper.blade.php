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
if (array_key_exists('q', $imageConfig)) {
    $imageLinkRouteParam['q'] = $imageConfig['q'];
}

$thumbnailSrc = FeUtils::getThumbnail($imageLinkRouteParam);

$imageConfigMobile = $imageConfigMobile ?? [];
$thumbnailSrcMobile = null;
if (!empty($imageConfigMobile)) {
    $imageLinkRouteParamMobile = ['link' => $link];
    if (array_key_exists('w', $imageConfigMobile)) {
        $imageLinkRouteParamMobile['w'] = $imageConfigMobile['w'];
    }
    if (array_key_exists('h', $imageConfigMobile)) {
        $imageLinkRouteParamMobile['h'] = $imageConfigMobile['h'];
    }
    if (array_key_exists('cr', $imageConfigMobile)) {
        $imageLinkRouteParamMobile['cr'] = $imageConfigMobile['cr'];
    }
    if (array_key_exists('q', $imageConfigMobile)) {
        $imageLinkRouteParamMobile['q'] = $imageConfigMobile['q'];
    }
    $thumbnailSrcMobile = FeUtils::getThumbnail($imageLinkRouteParamMobile);
}

$iconTintOnLight = !empty($iconTintOnLight);
$wrapperClasses = trim((isset($ratio) ? 'image-' . $ratio : '') . ' position-relative' . ($iconTintOnLight ? ' icon-on-light' : ''));
?>
<div
    class="image-wrapper {{ $wrapperClasses }}"
    @if ($iconTintOnLight) style="--icon-mask: url('{{ $thumbnailSrc }}');" @endif
>
    <picture>
        <source
            media="(max-width: 768px)"
            srcset="{{ $thumbnailSrcMobile ?? $thumbnailSrc }}"
            type="image/webp">
        <source
            media="(min-width: 769px)"
            srcset="{{ $thumbnailSrc }}"
            type="image/webp">
        <img
            src="{{ $thumbnailSrc }}"
            alt="{{ $alt }}"
            width="{{ $imageConfig['w'] ?? '' }}"
            height="{{ $imageConfig['h'] ?? '' }}"
            class="position-absolute w-100 h-100"
            loading="{{ $loading }}"
            decoding="{{ $decoding }}"
            {!! $fetchpriority ? 'fetchpriority="' . $fetchpriority . '"' : '' !!}
        />
    </picture>
</div>
