@php
    $class = $class ?? '';
    $title = $title ?? '';
    $description = $description ?? '';
    $showTitleAndDescription = $showTitleAndDescription ?? false;
    $list = $list ?? [];
@endphp
<section class="{{$class}} section-vr-360 bg">
    <div class="container-fluid">
        <div class="container">
            @if(!str_contains($class, 'gallery'))
                <div class="vr-header">
                    <p class="vr-eyebrow">
                        <span class="vr-eyebrow-line"></span>
                        Immersive Experience
                    </p>
                    <h2 class="vr-heading">
                        Step Aboard <em>Virtually.</em>
                    </h2>
                    <p class="vr-subtitle">
                        A 360° tour of Ha Long Bay —
                        coming October 2026.
                    </p>
                </div>
            @else
                <h2 class="section-title">{{$title}}</h2>
                <p class="section-description font-heading">{{$description}}</p>
            @endif
            <div class="vr-container-outer">
                <div class="vr-container-inner">
                    @if(isset($list) && count($list) > 0)
                        @if(count($list) > 1)
                            <div class="slide-1">
                                <div class="vr-carousel owl-carousel owl-theme">
                                    @foreach($list as $item)
                                        <div class="item">
                                            <div class="video-container">
                                                <video
                                                    class="video-js vjs-default-skin"
                                                    src="{{$item->link}}"
                                                    id="vr-player-{{$item->id}}"
                                                    preload="auto"
                                                />
                                            </div>
                                            @if($showTitleAndDescription)
                                                <div class="text-center py-3 px-2">
                                                    <p class="title give-ellipsis after-1-lines">{{$item->name}}</p>
                                                    <span class="description give-ellipsis after-1-lines">{{$item->description}}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @elseif(count($list) == 1)
                            <div class="item">
                                <div class="video-container">
                                    <video
                                        class="video-js vjs-default-skin"
                                        src="{{$list[0]->link}}"
                                        id="vr-player-{{$list[0]->id}}"
                                        preload="auto"
                                    />
                                </div>
                                @if($showTitleAndDescription)
                                    <div class="text-center py-3 px-2">
                                        <p class="title give-ellipsis after-1-lines">{{$list[0]->name}}</p>
                                        <span class="description give-ellipsis after-1-lines">{{$list[0]->description}}</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @else
                        <div class="vr-placeholder">

                            <div class="vr-corner vr-corner--tl">
                            </div>
                            <div class="vr-corner vr-corner--br">
                            </div>

                            <div class="vr-play-btn">
                                <svg width="20" height="20"
                                    viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <polygon points="8,5 19,12 8,19"/>
                                </svg>
                            </div>

                            <p class="vr-coming-title">
                                360° Tour — Coming Soon
                            </p>
                            <p class="vr-coming-note">
                                Filming in progress · October 2026
                            </p>

                            <a href="/contact"
                                class="vr-notify-btn">
                                <span class="vr-notify-dot"></span>
                                Notify me when available
                                <span class="vr-notify-arrow">→</span>
                            </a>

                            <p class="vr-360-label">
                                360° · VR · Ha Long Bay
                            </p>

                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
