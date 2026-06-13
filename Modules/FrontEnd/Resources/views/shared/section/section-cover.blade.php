@php
    $class = isset($class) ? $class : '';
    $tagHeading = isset($tagHeading) ? $tagHeading : 'p';
    $listBreadCrumb = $listBreadCrumb ?? [];
    $heroEyebrow = $heroEyebrow ?? null;
    $allowTitleHtml = $allowTitleHtml ?? false;
    $imageConfig = $imageConfig ?? [];
    $cruiseHero = $cruiseHero ?? false;
    $vesselLabel = $vesselLabel ?? '';
    $shipHeroSub = $shipHeroSub ?? '';
@endphp

@if (count($list) > 0)
    <section class="{{ $class }} section-cover position-relative {{ $cruiseHero ? 'is-cruise-hero' : '' }} {{ count($list) > 1 ? 'is-loading' : '' }} {{count($listBreadCrumb) > 0 && count($list) == 1 ? 'has-breadcrumb' : ''}}">
        <div class="container-fluid px-0">
            <div class="page-cover position-relative">
                @if (count($list) > 1)
                    <div class="slide-1">
                        <div class="list-item owl-carousel owl-theme">
                            @for ($i = 0; $i < count($list); $i++)
                                <div class="item">
                                    @include('frontend::shared.section.section-cover-item', [
                                        'obj' => $list[$i],
                                        'isVideoAutoPlay' => false,
                                        'tagHeading' => ($tagHeading === 'h1' && $i > 0) ? 'h2' : $tagHeading,
                                        'isPriorityMedia' => $i === 0,
                                        'heroEyebrow' => $heroEyebrow,
                                        'allowTitleHtml' => $allowTitleHtml,
                                        'imageConfig' => $imageConfig,
                                        'cruiseHero' => $cruiseHero,
                                        'vesselLabel' => $vesselLabel,
                                        'shipHeroSub' => $shipHeroSub,
                                    ])
                                </div>
                            @endfor
                        </div>
                    </div>
                @else
                    @include('frontend::shared.section.section-cover-item', [
                        'obj' => $list[0],
                        'isVideoAutoPlay' => true,
                        'tagHeading' => $tagHeading,
                        'listBreadCrumb' => $listBreadCrumb,
                        'isPriorityMedia' => true,
                        'heroEyebrow' => $heroEyebrow,
                        'allowTitleHtml' => $allowTitleHtml,
                        'imageConfig' => $imageConfig,
                        'cruiseHero' => $cruiseHero,
                        'vesselLabel' => $vesselLabel,
                        'shipHeroSub' => $shipHeroSub,
                    ])
                @endif
            </div>
        </div>
    </section>
@endif
