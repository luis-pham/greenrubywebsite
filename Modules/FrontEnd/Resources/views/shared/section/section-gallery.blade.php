@php
    $title = $title ?? "";
    $description = $description ?? "";
    $list = $list ?? [];
    $bgClass = $bgClass ?? 'bg-tender-white';
    $titleClass = $titleClass ?? 'section-title';
    $tagHeading = $tagHeading ?? 'h2';
@endphp
<section class="section-gallery bg {{ $bgClass }}">
    <div class="container-fluid">
        <div class="container">
            <{{ $tagHeading }} class="{{ $titleClass }}">{{ $title }}</{{ $tagHeading }}>
            <p class="section-description font-heading">{{ $description }}</p>
            <div class="slide-1">
                <div class="gallery-container gallery-grid-layout">
                    @php
                        $remaining = count($list) - 6;
                    @endphp
                    @foreach($list as $img)
                        @php
                            $src = \Modules\BackEnd\Helpers\Utilities::getFileLink($img->link);
                            $thumbnail = \Modules\BackEnd\Helpers\Utilities::getFileLink($img->thumbnail ?: $img->link);
                            $caption = $img->name ?? ($img->title ?? '');
                        @endphp
                        <a href="{{$src}}" class="gallery-image-wrapper {{$remaining > 0 ? 'overflow' : ''}}" data-fancybox="gallery" data-caption="{{$caption}}" data-remaining="{{$remaining}}">
                            <img src="{{$thumbnail}}" alt="{{$caption}}"/>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
