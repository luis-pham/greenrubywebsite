        <url>
            <loc>{{ $entry['loc'] }}</loc>
            <lastmod>{{ $entry['lastmod'] }}</lastmod>
            <changefreq>{{ $entry['changefreq'] ?? 'monthly' }}</changefreq>
            <priority>{{ $entry['priority'] ?? '0.6' }}</priority>
            @if (!empty($entry['alternates']))
                @foreach ($entry['alternates'] as $alternate)
                    <xhtml:link rel="alternate" hreflang="{{ $alternate['hreflang'] }}" href="{{ $alternate['url'] }}"/>
                @endforeach
            @endif
        </url>
