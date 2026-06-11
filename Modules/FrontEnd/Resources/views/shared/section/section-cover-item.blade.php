@php
    $isVideoAutoPlay = isset($isVideoAutoPlay) ? $isVideoAutoPlay : false;
    $isPriorityMedia = isset($isPriorityMedia) ? $isPriorityMedia : false;
    $tagHeading = isset($tagHeading) ? $tagHeading : 'p';
    $hasButton = isset($obj->listButton) && count($obj->listButton) > 0;
    $listBreadCrumb = $listBreadCrumb ?? [];
    $heroEyebrow = $heroEyebrow ?? null;
    $allowTitleHtml = $allowTitleHtml ?? false;
    $cruiseHero = $cruiseHero ?? false;
    $vesselLabel = $vesselLabel ?? '';
    $shipHeroSub = $shipHeroSub ?? '';
@endphp

@if (!in_array(pathinfo($obj->link, PATHINFO_EXTENSION), config('backend.fileTypeVideo')))
    @php
        $imageLinkRouteParam = ['link' => $obj->link];
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
    @endphp
    @include('frontend::shared.image-wrapper', [
        'link' => FeUtils::getThumbnail($imageLinkRouteParam),
        'alt' => $obj->title,
        'fetchpriority' => $isPriorityMedia ? 'high' : null,
        'loading' => $isPriorityMedia ? 'eager' : 'lazy',
    ])
@else
    <div class="image-wrapper position-relative">
        <video
            src="{{ asset(Utilities::getFileLink($obj->link)) }}"
            class="position-absolute w-100 h-100"
            muted
            playsinline
            preload="{{ $isPriorityMedia ? 'metadata' : 'none' }}"
            {{ $isVideoAutoPlay ? 'autoplay' : '' }}
            loop
        ></video>
    </div>
@endif
<div class="container hero-content position-absolute">
    @if(count($listBreadCrumb) > 0)
        @include('frontend::shared.breadcrumb',[
            'listBreadcrumb' => $listBreadCrumb
        ])
    @endif
    <div class="main-info {{ $cruiseHero ? 'ship-hero-main-info' : 'mx-auto text-center' }} text-white">
        @if ($cruiseHero)
            <p class="section-eyebrow section-eyebrow--gold">{{ $vesselLabel ?: 'Green Ruby Cruises' }}</p>
        @elseif ($heroEyebrow)
            <p class="hero-eyebrow mb-0">{{ $heroEyebrow }}</p>
        @endif
        <{{ $tagHeading }} class="title font-heading {{ $cruiseHero ? '' : 'font-weight-bold' }} text-break">{!! $allowTitleHtml ? safe_html($obj->title) : e($obj->title) !!}</{{ $tagHeading }}>
        @if ($cruiseHero && $shipHeroSub)
            <p class="ship-hero-sub">{{ $shipHeroSub }}</p>
        @elseif (isset($obj->description) && $obj->description)
            <p class="description {{ $hasButton ? 'mb-4' : 'mb-0' }} text-break">{{ $obj->description }}</p>
        @endif
        @if ($hasButton)
            <div class="list-button d-flex align-items-center">
                @for ($i = 0; $i < count($obj->listButton); $i++)
                    @php
                        $isWatchButton = str_contains($obj->listButton[$i]->class, 'btn-success');
                    @endphp
                    <div class="item">
                        <a href="{{ $obj->listButton[$i]->url }}" class="{{ $obj->listButton[$i]->class }} btn-rounded">
                            @if ($cruiseHero && $isWatchButton)
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polygon points="10,8 16,12 10,16" fill="currentColor" stroke="none"/>
                                </svg>
                            @endif
                            {{ $obj->listButton[$i]->label }}
                        </a>
                    </div>
                @endfor
            </div>
        @endif
        @if (isset($obj->extraContent) && $obj->extraContent)
            {!! safe_html($obj->extraContent) !!}
        @endif
    </div>
</div>
@if ($cruiseHero)
    <div class="ship-hero-accent-line"></div>
@endif
