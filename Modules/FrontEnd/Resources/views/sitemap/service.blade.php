<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ $list[0]->url }}</loc>
        <lastmod>{{ Carbon\Carbon::parse($list[0]->updated_at ?: $list[0]->created_at)->format('Y-m-d\TH:i:sP') }}</lastmod>
        <changefreq>always</changefreq>
        <priority>1.0</priority>
    </url>
</urlset>