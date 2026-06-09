<div class="c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show" id="sidebar">
    <div class="c-sidebar-brand d-lg-down-none">        
        <div class="c-sidebar-brand-full text-uppercase">
            <a href="{{ route(Utilities::getRouteName('backend.index'), ['languageCode' => Route::current()->parameter('languageCode')]) }}" class="text-reset text-decoration-none">
                @if (array_key_exists('website-icon', $config) && $config['website-icon'])
                    <img src="{{ asset(Utilities::getFileLink($config['website-icon'])) }}" alt="Logo" class="img-fluid d-inline-block mr-1" style="width: 20px;" />
                @endif
                @if (array_key_exists('website-name', $config) && $config['website-name'])
                    <strong>{{ $config['website-name'] }}</strong>
                @endif
            </a>
        </div>
        <div class="c-sidebar-brand-minimized text-uppercase text-center">
            <strong>CMS</strong>
        </div>
    </div>
    <ul class="c-sidebar-nav">
        @foreach ($menu as $key => $obj)
            @php
                $hasChild = !empty($obj->child);
            @endphp
            @if ($hasChild)
                <li class="c-sidebar-nav-title">{{ $obj->name }}</li>
                @include('backend::layouts.master-menu', ['menu' => $obj->child])
            @endif
        @endforeach
    </ul>
</div>