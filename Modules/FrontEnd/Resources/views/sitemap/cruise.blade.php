<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @for ($i = 0; $i < count($list); $i++)
        <url>
            <loc>{{ $list[$i]->url }}</loc>
            <lastmod>{{ Carbon\Carbon::parse($list[$i]->updated_at ?: $list[$i]->created_at)->format('Y-m-d\TH:i:sP') }}</lastmod>
            <changefreq>always</changefreq>
            <priority>1.0</priority>
        </url>
    @endfor
</urlset>
