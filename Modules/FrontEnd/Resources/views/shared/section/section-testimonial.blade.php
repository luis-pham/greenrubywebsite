@php
    $class = isset($class) ? $class : '';
    $list = $list ?? [];
@endphp

@if (count($list) > 0)
<section id="section-testimonial" class="{{ $class }} section-testimonial bg text-white">
    <div class="container-fluid">
        <div class="container">
            <h2 class="section-title">{{ __('frontend::common.section_testimonial_title') }}</h2>
            <p class="section-description font-heading">{{ __('frontend::common.section_testimonial_description') }}</p>
            <div class="slide-1">
                <div class="list-item owl-carousel owl-theme">
                    @for ($i = 0; $i < count($list); $i++)
                        <div class="item d-flex flex-column justify-content-center text-center">
                            <svg class="icon-qoute" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21c3 0 7 -1 7 -8v-5c0 -1.25 -.756 -2.017 -2 -2c-1 0 -4 .5 -4 2.5c0 1.5 .986 3 2 4c1 1 2 2 2 4c0 1 -1 1.5 -2 1.5s-1 .25 -1 1.5v2.5c0 1 0 1 1 1zm14 0c3 0 7 -1 7 -8v-5c0 -1.25 -.757 -2.017 -2 -2c-1 0 -4 .5 -4 2.5c0 1.5 .986 3 2 4c1 1 2 2 2 4c0 1 -1 1.5 -2 1.5s-1 .25 -1 1.5v2.5c0 1 0 1 1 1z"/></svg>
                            <div class="content text-break give-ellipsis">{{ $list[$i]->content }}</div>
                            <p class="fullname">{{ $list[$i]->fullname }}</p>
                            <p class="position mb-0">{{ $list[$i]->position }}</p>
                        </div>
                    @endfor
                </div>
            </div>
            <div class="list-filter d-flex flex-wrap justify-content-center align-items-center">
                @for ($i = 0; $i < count($list); $i++)
                    <a href="javascript:;" class="item d-block {{ $i == 0 ? 'active' : '' }}">
                        <div class="item-wrapper">
                            @include('frontend::shared.image-wrapper', [
                                'link' => $list[$i]->avatar,
                                'alt' => $list[$i]->fullname,
                                'imageConfig' => ['w' => 72, 'h' => 72]
                            ])
                        </div>
                    </a>
                @endfor
            </div>
        </div>
    </div>
</section>
@endif
