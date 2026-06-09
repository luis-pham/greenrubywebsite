@if (isset($obj) && $obj)
    @php
        $excludedFooterUrlPatterns = [
            'terms-and-conditions',
            'dieu-khoan-dieu-kien',
            'privacy-policy',
            'chinh-sach-bao-mat',
        ];
        $footerMenuChildren = [];
        if (count($obj->child) > 0) {
            for ($i = 0; $i < count($obj->child); $i++) {
                $isExcluded = false;
                foreach ($excludedFooterUrlPatterns as $pattern) {
                    if (str_contains($obj->child[$i]->url, $pattern)) {
                        $isExcluded = true;
                        break;
                    }
                }
                if (!$isExcluded) {
                    $footerMenuChildren[] = $obj->child[$i];
                }
            }
        }
    @endphp
    @if (count($footerMenuChildren) > 0)
        <p class="title">{{ $obj->name }}</p>
        <nav class="navigation">
            <ul class="list-unstyled mb-0">
                @for ($i = 0; $i < count($footerMenuChildren); $i++)
                    @php
                        $footerLinkName = $footerMenuChildren[$i]->name;
                        if (in_array($footerLinkName, ['Chat with AI', 'AI Concierge'], true)) {
                            $footerLinkName = 'Ask AI';
                        } elseif (in_array($footerLinkName, ['Chat với AI', 'Nhắn tin với AI'], true)) {
                            $footerLinkName = 'Hỏi AI';
                        }
                        $footerAiSoonNames = ['Chat with AI', 'Chat với AI', 'Nhắn tin với AI', 'Ask AI', 'Hỏi AI', 'AI Concierge'];
                    @endphp
                    <li>
                        <a href="{{ $footerMenuChildren[$i]->url }}" class="footer-link text-reset">
                            {{ $footerLinkName }}
                            @if (in_array($footerMenuChildren[$i]->name, $footerAiSoonNames, true))
                                <span class="footer-ai-soon-badge">Soon</span>
                            @endif
                        </a>
                    </li>
                @endfor
            </ul>
        </nav>
    @endif
@endif