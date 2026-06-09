@php
    $isVideoAutoPlay = isset($isVideoAutoPlay) ? $isVideoAutoPlay : false;
    $isPriorityMedia = isset($isPriorityMedia) ? $isPriorityMedia : false;
    $tagHeading = isset($tagHeading) ? $tagHeading : 'p';
    $hasButton = isset($obj->listButton) && count($obj->listButton) > 0;
    $listBreadCrumb = $listBreadCrumb ?? [];
    $heroEyebrow = $heroEyebrow ?? null;
    $allowTitleHtml = $allowTitleHtml ?? false;
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
    <div class="main-info mx-auto text-white text-center">
        @if ($heroEyebrow)
            <p class="hero-eyebrow mb-0">{{ $heroEyebrow }}</p>
        @endif
        <{{ $tagHeading }} class="title font-heading font-weight-bold text-break">{!! $allowTitleHtml ? $obj->title : e($obj->title) !!}</{{ $tagHeading }}>
        @if (isset($obj->description) && $obj->description)
            <p class="description {{ $hasButton ? 'mb-4' : 'mb-0' }} text-break">{{ $obj->description }}</p>
        @endif
        @if ($hasButton)
            <div class="list-button d-flex align-items-center">
                @for ($i = 0; $i < count($obj->listButton); $i++)
                    <div class="item">
                        <a href="{{ $obj->listButton[$i]->url }}" class="{{ $obj->listButton[$i]->class }} btn-rounded">{{ $obj->listButton[$i]->label }}</a>
                    </div>
                @endfor
            </div>
        @endif
        @if (isset($obj->extraContent) && $obj->extraContent)
            {!! $obj->extraContent !!}
        @endif
    </div>
</div>
