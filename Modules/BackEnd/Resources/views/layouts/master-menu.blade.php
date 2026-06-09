@foreach ($menu as $key => $obj)
    @php
        $activeUrl = json_decode($obj->active_url ? $obj->active_url : '[]');
        $isActive = $obj->url == Request::getPathInfo() || in_array(Request::getPathInfo(), $activeUrl);
        $hasChild = !empty($obj->child);
    @endphp
    <li class="c-sidebar-nav-item{{ $hasChild ? ' c-sidebar-nav-dropdown' : '' }}">
        <a class="c-sidebar-nav-link{{ $hasChild ? ' c-sidebar-nav-dropdown-toggle' : '' }}" href="{{ $hasChild ? 'javascript:void(0)' : $obj->url }}">
            <div class="c-sidebar-nav-icon">
                @if ($obj->icon)
                    <i class="{{ $obj->icon }}"></i>
                @endif
            </div>
            {{ $obj->name }}
        </a>
        @if ($hasChild)
            @include('backend::layouts.master-menu-child', ['menu' => $obj->child])
        @endif
    </li>
@endforeach