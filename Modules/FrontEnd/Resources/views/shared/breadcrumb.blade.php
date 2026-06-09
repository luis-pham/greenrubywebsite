@php
    $listBreadcrumb = $listBreadcrumb ?? [];
    $isVisible = $isVisible ?? true;
    $isHiddenLastItem = $isHiddenLastItem ?? false;
@endphp

@if (count($listBreadcrumb) > 0)
    @if ($isVisible)
        <div class="page-breadcrumb">
            <ol class="breadcrumb px-0 pt-0 mb-0">
                @for ($i = 0; $i < count($listBreadcrumb); $i++)
                    @if ($i < count($listBreadcrumb) - 1)
                        <li class="breadcrumb-item"><a href="{{ $listBreadcrumb[$i]['url'] }}">{{ $listBreadcrumb[$i]['name'] }}</a></li>
                    @elseif ($i == count($listBreadcrumb) - 2)
                        @if (!$isHiddenLastItem)
                            <li class="breadcrumb-item"><a href="{{ $listBreadcrumb[$i]['url'] }}">{{ $listBreadcrumb[$i]['name'] }}</a></li>
                        @else
                            <li class="breadcrumb-item active"><a href="{{ $listBreadcrumb[$i]['url'] }}" class="text-reset"><strong>{{ $listBreadcrumb[$i]['name'] }}</strong></a></li>
                        @endif
                    @elseif (!$isHiddenLastItem)
                        <li class="breadcrumb-item active">{{ $listBreadcrumb[$i]['name'] }}</li>
                    @endif
                @endfor
            </ol>
        </div>
    @endif

    @php
        $listBreadcrumbSeo = [];
        for ($i = 0; $i < count($listBreadcrumb); $i++) {
            $breadcrumbSeo = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $listBreadcrumb[$i]['name']
            ];
            if ($i < count($listBreadcrumb) - 1) {
                $breadcrumbSeo['item'] = $listBreadcrumb[$i]['url'];
            }
            $listBreadcrumbSeo[] = $breadcrumbSeo;
        }
    @endphp

    <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "BreadcrumbList",
            "itemListElement": {!! json_encode($listBreadcrumbSeo, JSON_UNESCAPED_UNICODE) !!}
        }
    </script>
@endif