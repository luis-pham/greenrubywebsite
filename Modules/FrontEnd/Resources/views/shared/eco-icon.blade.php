@php
    $variant = (int) ($variant ?? 0);
@endphp
@if ($variant === 0)
    <svg width="56" height="56" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M24 12C24 12 16 19 16 25C16 29.4 19.6 33 24 33C28.4 33 32 29.4 32 25C32 19 24 12 24 12Z" fill="rgba(200,168,75,0.12)" stroke="#C8A84B" stroke-width="1.5" stroke-linejoin="round"/>
        <path d="M20 27C20 27 21.5 30 24 30C26.5 30 28 27 28 27" stroke="#0e5f4b" stroke-width="1.5" fill="none" stroke-linecap="round"/>
        <line x1="24" y1="17" x2="24" y2="23" stroke="#0e5f4b" stroke-width="1" stroke-linecap="round" opacity="0.5"/>
        <line x1="21" y1="20" x2="27" y2="20" stroke="#0e5f4b" stroke-width="1" stroke-linecap="round" opacity="0.5"/>
    </svg>
@elseif ($variant === 1)
    <svg width="56" height="56" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M24 10C24 10 14 20 14 27C14 32.5 18.5 37 24 37C29.5 37 34 32.5 34 27C34 20 24 10 24 10Z" fill="rgba(200,168,75,0.12)" stroke="#C8A84B" stroke-width="1.5"/>
        <path d="M18 30C18 30 20.5 34 24 34C27.5 34 30 30 30 30" stroke="#0e5f4b" stroke-width="1.5" fill="none" stroke-linecap="round"/>
    </svg>
@elseif ($variant === 2)
    <svg width="56" height="56" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <rect x="13" y="18" width="22" height="14" rx="1" fill="rgba(200,168,75,0.12)" stroke="#C8A84B" stroke-width="1.5"/>
        <line x1="19" y1="18" x2="19" y2="32" stroke="#C8A84B" stroke-width="0.75" opacity="0.4"/>
        <line x1="24" y1="18" x2="24" y2="32" stroke="#C8A84B" stroke-width="0.75" opacity="0.4"/>
        <line x1="29" y1="18" x2="29" y2="32" stroke="#C8A84B" stroke-width="0.75" opacity="0.4"/>
        <path d="M24 32L24 37M21 37L27 37" stroke="#0e5f4b" stroke-width="1.25" stroke-linecap="round"/>
        <path d="M10 25L13 25M35 25L38 25" stroke="#0e5f4b" stroke-width="1.25" stroke-linecap="round" opacity="0.5"/>
    </svg>
@else
    <svg width="56" height="56" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <circle cx="24" cy="30" r="8" fill="rgba(200,168,75,0.12)" stroke="#C8A84B" stroke-width="1.5"/>
        <path d="M22 30L23.5 31.5L26.5 28.5" stroke="#C8A84B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M14 22C14 22 16 14 24 13C32 12 35 18 35 18" stroke="#0e5f4b" stroke-width="1.25" fill="none" stroke-linecap="round" opacity="0.6"/>
        <path d="M35 18L32 17M35 18L34 21" stroke="#0e5f4b" stroke-width="1.25" stroke-linecap="round" opacity="0.6"/>
    </svg>
@endif
