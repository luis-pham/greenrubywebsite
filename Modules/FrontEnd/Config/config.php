<?php

return [
    'name' => 'FrontEnd',
    'organizationSchemaId' => 'https://greenrubycruises.com/#organization',
    'organizationCanonicalUrl' => 'https://greenrubycruises.com',
    'organizationAddress' => [
        'streetAddress' => 'Marina, Tuan Chau',
        'addressLocality' => 'Ha Long',
        'addressRegion' => 'Quang Ninh',
        'addressCountry' => 'VN',
    ],
    'organizationGeo' => [
        'latitude' => 20.916492117326154,
        'longitude' => 106.98930708346906,
    ],
    'organizationAreaServed' => ['Ha Long Bay', 'Lan Ha Bay'],
    'organizationLogoSocial' => [
        'url' => '/assets/frontend/images/logo-social.jpg?v=0.0.1',
        'width' => 1200,
        'height' => 627,
    ],
    'paginationLimit' => 20,
    'paginationArticleLimit' => 10,
    'paginationGalleryLimit' => 20,
    'imageBlank' => '/assets/frontend/images/blank.gif',
    'imageProxyQuality' => max(1, min(95, (int) env('IMAGE_PROXY_QUALITY', 90))),
    'showPublicPrices' => env('FRONTEND_SHOW_PUBLIC_PRICES', false),
];
