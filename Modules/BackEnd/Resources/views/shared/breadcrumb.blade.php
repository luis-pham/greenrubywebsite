@php
    $items = isset($breadcrumbItems) ? $breadcrumbItems : [];
@endphp

@if (count($items) > 0)
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            @foreach ($items as $index => $item)
                @if ($index === count($items) - 1)
                    <li class="breadcrumb-item active" aria-current="page">{{ $item['label'] }}</li>
                @else
                    <li class="breadcrumb-item">
                        @if (isset($item['url']))
                            <a href="{{ $item['url'] }}">
                                @if (isset($item['icon']))
                                    <i class="{{ $item['icon'] }}"></i>
                                @endif
                                {{ $item['label'] }}
                            </a>
                        @else
                            @if (isset($item['icon']))
                                <i class="{{ $item['icon'] }}"></i>
                            @endif
                            {{ $item['label'] }}
                        @endif
                    </li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif
