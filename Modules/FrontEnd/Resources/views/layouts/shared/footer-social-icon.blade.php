@switch($type)
    @case('tripadvisor')
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgba(240,236,228,0.5)" stroke-width="1.5">
            <circle cx="12" cy="12" r="4"/>
            <path d="M16 8v5a3 3 0 006 0v-1a10 10 0 10-3.92 7.94"/>
        </svg>
        @break
    @case('facebook')
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgba(240,236,228,0.5)" stroke-width="1.5">
            <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
        </svg>
        @break
    @case('instagram')
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgba(240,236,228,0.5)" stroke-width="1.5">
            <rect x="2" y="2" width="20" height="20" rx="5"/>
            <circle cx="12" cy="12" r="4"/>
            <circle cx="17.5" cy="6.5" r="0.5" fill="rgba(240,236,228,0.5)"/>
        </svg>
        @break
    @case('tiktok')
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgba(240,236,228,0.5)" stroke-width="1.5">
            <path d="M9 12a4 4 0 100-8 4 4 0 000 8z"/>
            <path d="M15 8a4 4 0 110 8"/>
            <path d="M21 21l-6-6"/>
        </svg>
        @break
    @case('youtube')
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgba(240,236,228,0.5)" stroke-width="1.5">
            <path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 001.95-1.95A29 29 0 0023 12a29 29 0 00-.46-5.58z"/>
            <polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" fill="rgba(240,236,228,0.5)" stroke="none"/>
        </svg>
        @break
    @case('linkedin')
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgba(240,236,228,0.5)" stroke-width="1.5">
            <path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/>
            <rect x="2" y="9" width="4" height="12"/>
            <circle cx="4" cy="4" r="2"/>
        </svg>
        @break
    @case('x')
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgba(240,236,228,0.5)" stroke-width="1.5">
            <path d="M4 4l16 16M20 4L4 20"/>
        </svg>
        @break
    @case('pinterest')
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgba(240,236,228,0.5)" stroke-width="1.5">
            <circle cx="12" cy="12" r="9"/>
            <path d="M9.5 11.5c0-1.5 1.2-2.5 2.8-2.5 1.4 0 2.2.8 2.2 2 0 2.2-1.2 4.8-2.8 4.8-.9 0-1.3-.6-1.3-1.3 0-.2 0-.4.1-.6l-.5-2.1c-.1-.4.1-.7.5-.9.3-.1.6-.2.9-.2"/>
        </svg>
        @break
@endswitch
