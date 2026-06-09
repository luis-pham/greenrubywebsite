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
                            <i class="icon-qoute fa-solid fa-quote-left"></i>
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
