<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @if ($pageUpdatedAt)
        <sitemap>
            <loc>{{ route('frontend.sitemap.page') }}</loc>
            <lastmod>{{ Carbon\Carbon::parse($pageUpdatedAt)->format('Y-m-d\TH:i:sP') }}</lastmod>
        </sitemap>
    @endif
    @if ($article)
        <sitemap>
            <loc>{{ route('frontend.sitemap.article') }}</loc>
            <lastmod>{{ Carbon\Carbon::parse($article->updated_at ?: $article->created_at)->format('Y-m-d\TH:i:sP') }}</lastmod>
        </sitemap>
        <sitemap>
            <loc>{{ route('frontend.sitemap.category', ['type' => 'article']) }}</loc>
            <lastmod>{{ Carbon\Carbon::parse($article->updated_at ?: $article->created_at)->format('Y-m-d\TH:i:sP') }}</lastmod>
        </sitemap>
    @endif
    @if ($experience)
        <sitemap>
            <loc>{{ route('frontend.sitemap.experience') }}</loc>
            <lastmod>{{ Carbon\Carbon::parse($experience->updated_at ?: $experience->created_at)->format('Y-m-d\TH:i:sP') }}</lastmod>
        </sitemap>
    @endif
    @if ($service)
        <sitemap>
            <loc>{{ route('frontend.sitemap.service') }}</loc>
            <lastmod>{{ Carbon\Carbon::parse($service->updated_at ?: $service->created_at)->format('Y-m-d\TH:i:sP') }}</lastmod>
        </sitemap>
    @endif
    @if ($itinerary)
        <sitemap>
            <loc>{{ route('frontend.sitemap.itinerary') }}</loc>
            <lastmod>{{ Carbon\Carbon::parse($itinerary->updated_at ?: $itinerary->created_at)->format('Y-m-d\TH:i:sP') }}</lastmod>
        </sitemap>
    @endif
    @if ($cruise)
        <sitemap>
            <loc>{{ route('frontend.sitemap.cruise') }}</loc>
            <lastmod>{{ Carbon\Carbon::parse($cruise->updated_at ?: $cruise->created_at)->format('Y-m-d\TH:i:sP') }}</lastmod>
        </sitemap>
    @endif
    @if ($gallery)
        <sitemap>
            <loc>{{ route('frontend.sitemap.gallery') }}</loc>
            <lastmod>{{ Carbon\Carbon::parse($gallery)->format('Y-m-d\TH:i:sP') }}</lastmod>
        </sitemap>
    @endif
    @if($faq)
        <sitemap>
            <loc>{{ route('frontend.sitemap.faq') }}</loc>
            <lastmod>{{ Carbon\Carbon::parse($faq->updated_at ?: $faq->created_at)->format('Y-m-d\TH:i:sP') }}</lastmod>
        </sitemap>
    @endif
</sitemapindex>