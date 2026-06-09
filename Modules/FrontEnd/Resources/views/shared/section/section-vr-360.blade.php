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
            <h2 class="section-title">{{$title}}</h2>
            <p class="section-description font-heading">{{$description}}</p>
            <div class="vr-container-outer">
                <div class="vr-container-inner">
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
                </div>
            </div>
        </div>
    </div>
</section>
