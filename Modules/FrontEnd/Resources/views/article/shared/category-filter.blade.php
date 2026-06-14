@php
    $listCategoryChild = $listCategoryChild ?? [];
    $category = $category ?? null;
    $categoryParent = $categoryParent ?? null;
    $languageCode = $languageCode ?? Route::current()->parameter('languageCode');
@endphp

@if (count($listCategoryChild) > 0)
    <div id="section-article-filter" class="gallery-filter-sticky">
        <div class="container-fluid px-0">
            <div class="gallery-filter-inner">
                <div class="container">
                    <nav class="gallery-filter-bar list-filter" aria-label="{{ __('frontend::article.category_default_sub_title') }}">
                        @if (!$category)
                            <a href="{{ route(Utilities::getRouteName('frontend.article.index'), ['languageCode' => $languageCode]) }}" class="item gallery-filter-tab active">{{ __('frontend::common.all') }}</a>
                        @elseif ($categoryParent)
                            @php
                                $categoryUrl = $categoryParent->slug != 'root'
                                    ? route(Utilities::getRouteName('frontend.article.category'), ['languageCode' => $languageCode, 'slug' => $categoryParent->slug])
                                    : route(Utilities::getRouteName('frontend.article.index'), ['languageCode' => $languageCode]);
                            @endphp
                            <a href="{{ $categoryUrl }}" class="item gallery-filter-tab {{ $category->id == $categoryParent->id ? 'active' : '' }}">{{ __('frontend::common.all') }}</a>
                        @endif
                        @for ($i = 0; $i < count($listCategoryChild); $i++)
                            <a href="{{ route(Utilities::getRouteName('frontend.article.category'), ['languageCode' => $languageCode, 'slug' => $listCategoryChild[$i]->slug]) }}" class="item gallery-filter-tab {{ $category && $category->id == $listCategoryChild[$i]->id ? 'active' : '' }}">{{ $listCategoryChild[$i]->name }}</a>
                        @endfor
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endif
