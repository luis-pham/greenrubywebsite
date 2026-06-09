@php
use Modules\FrontEnd\Helpers\FeLanguageUtils;
@endphp

@php
    $currentPage = (int)Request::route('page') ?: 1;
    $queryString = Request::all();
    $language = FeLanguageUtils::getCurrentLanguage();
    $pagePrefix = '/' . strtolower(__('frontend::common.page'));
@endphp

@if ($totalPage > 1)
    <ul class="pagination flex-wrap justify-content-center mb-0">
        {{-- Previous Page Link --}}
        @if ($currentPage > 1)
            {{-- @if ($totalPage > 2)
                <li class="page-item">
                    <a href="{{ asset(Utilities::setQueryStringToUrl($baseUrl . $pagePrefix . '-1', $queryString)) }}" class="page-link d-none d-md-block"><i class="fa-solid fa-angles-left"></i></a>
                </li>
            @endif --}}
            <li class="page-item">
                <a href="{{ asset(Utilities::setQueryStringToUrl($baseUrl . $pagePrefix . '-' . ($currentPage - 1), $queryString)) }}" class="page-link"><i class="fa-solid fa-angle-left"></i></a>
            </li>
        @endif

        {{-- Pagination Elements --}}
        @php
            $pageStart = $currentPage - 5 > 0 ? $currentPage - 5 : 0;
            $pageEnd = $currentPage + 4 > $totalPage ? $totalPage : $currentPage + 4;
        @endphp
        @for ($i = $pageStart; $i < $pageEnd; $i++)
            @php $page = $i + 1 @endphp
            @if ($page == $currentPage)
                <li class="page-item active">
                    <span class="page-link">{{ $page }}<span class="sr-only">(current)</span></span>
                </li>
            @else
                <li class="page-item">
                    <a href="{{ asset(Utilities::setQueryStringToUrl($baseUrl . $pagePrefix . '-' . $page, $queryString)) }}" class="page-link">{{ $page }}</a>
                </li>
            @endif
        @endfor

        {{-- Next Page Link --}}
        @if ($currentPage < $totalPage)
            <li class="page-item">
                <a href="{{ asset(Utilities::setQueryStringToUrl($baseUrl . $pagePrefix . '-' . ($currentPage + 1), $queryString)) }}" class="page-link"><i class="fa-solid fa-angle-right"></i></a>
            </li>
            {{-- @if ($totalPage > 2)
                <li class="page-item">
                    <a href="{{ asset(Utilities::setQueryStringToUrl($baseUrl . $pagePrefix . '-' . $totalPage, $queryString)) }}" class="page-link d-none d-md-block"><i class="fa-solid fa-angles-right"></i></a>
                </li>
            @endif --}}
        @endif
    </ul>
@endif