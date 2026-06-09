@php
    $class = isset($class) ? $class : '';
    $searchUrl = isset($searchUrl) ? $searchUrl : '';
@endphp

<div class="widget {{ $class }}">
    <div class="widget-body">
        <form action="{{ $searchUrl }}" class="frm-search input-group">
            <input type="text" name="k" value="{{ Request::get('k') }}" class="form-control rounded-0 border-right-0" placeholder="{{ __('frontend::article.search_placeholder') }}" autocomplete="off" />
            <div class="input-group-append bg-white">
                <button type="button" class="btn-search input-group-text bg-transparent rounded-0"><i class="fa-solid fa-magnifying-glass"></i></span>
            </div>
        </form>
    </div>
</div>