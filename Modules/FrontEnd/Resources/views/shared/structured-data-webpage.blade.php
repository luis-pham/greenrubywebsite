@php
    $hubSeo = $hubSeo ?? [];
    $hubCanonicalUrl = $hubCanonicalUrl ?? '';
    $hubHasPart = $hubHasPart ?? null;

    $webPageSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $hubSeo['title'] ?? '',
        'description' => $hubSeo['description'] ?? '',
        'url' => $hubCanonicalUrl,
        'isPartOf' => ['@id' => config('frontend.organizationSchemaId')],
    ];

    if (!empty($hubHasPart)) {
        $webPageSchema['hasPart'] = $hubHasPart;
    }
@endphp

@if (!empty($hubSeo['title']) && !empty($hubCanonicalUrl))
<script type="application/ld+json">
    {!! json_encode($webPageSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
