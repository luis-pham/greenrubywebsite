@php
    $class = isset($class) ? $class : '';
    $title = isset($title) ? $title : '';
    $description = isset($description) ? $description : '';
    $content = isset($content) ? $content : '';
    $buttons = isset($buttons) ? $buttons : [];
@endphp

<section class="{{ $class }} section-call-to-action bg">
    <div class="container-fluid">
        <div class="container">
            <div class="section-wrapper">
                @if ($title)
                    <h2 class="section-title">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="section-description font-heading">{{ $description }}</p>
                @endif
                @if ($content)
                    <div class="content">{{ $content }}</div>
                @endif
                @if (count($buttons) > 0)
                    <div class="list-button">
                        @for ($i = 0; $i < count($buttons); $i++)
                            <div class="item">
                                <a href="{{ $buttons[$i]['url'] }}" class="text-center d-block {{ $buttons[$i]['class'] }} btn-rounded">{{ $buttons[$i]['label'] }}</a>
                            </div>
                        @endfor
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
