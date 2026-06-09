<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @for ($i = 0; $i < count($listCategory); $i++)
        <url>
            <loc>{{ $listCategory[$i]->url }}</loc>
            <lastmod>{{ Carbon\Carbon::parse($listCategory[$i]->updated_at ?: $listCategory[$i]->created_at)->format('Y-m-d\TH:i:sP') }}</lastmod>
            <changefreq>always</changefreq>
            <priority>0.9</priority>
        </url>
    @endfor
</urlset>