<?php

/*
 * Locale registry and frontend readiness (greenruby-web docs/i18n-audit/12 §2.1 and §5.1, 08 §3, phase 1B).
 *
 * A language is effectively public when ad_language.site_status = 'public' AND every deployed web instance says it
 * can serve it (its build has the routes and dictionaries). en/vi (route_style 'legacy') never depend on the
 * frontend manifest. Changing a value needs `php artisan config:cache` in production.
 */
return [
    'content_api' => [
        // ?lang absent: the language served before the registry existed (legacy contract, kept on purpose).
        'missing_language' => 'en',
        // ?lang present but not an effectively public language: 'not_found' (404) or 'legacy_en' (serve English,
        // the pre-1B behaviour, logged). The 2026-10-31 audit found no caller sending anything but en/vi.
        'unknown_language' => env('CONTENT_API_UNKNOWN_LANGUAGE', 'not_found'),
    ],

    // Pull-based readiness (08 §3): the CMS asks every web instance; a new language is ready only when ALL of them
    // answer and list it as ready. Comma-separated internal URLs, e.g. http://green-ruby-web:3000/api/i18n/manifest.
    'frontend_manifest' => [
        'urls' => array_values(array_filter(array_map('trim', explode(',', (string) env('FRONTEND_MANIFEST_URLS', ''))))),
        'secret' => env('FRONTEND_MANIFEST_SECRET'),
        'timeout' => 1.0,
        // Seconds an instance's answer (or failure) is reused. A rolled-back instance drops a language within this time.
        'ttl' => 60,
        'schema' => 1,
    ],

    // Seconds the ad_language registry is cached; any save or delete of a row flushes it at once.
    'registry_ttl' => 300,

    // New languages may only be added once publication gating and N-language sitemaps exist (phase 1C).
    'allow_new_locales' => (bool) env('I18N_ALLOW_NEW_LOCALES', false),

    // Even with status 'public' and every web instance ready, a new (intl) language is not effectively public until
    // the rest of phase 1C exists: stored slugs (a Japanese name gives an empty slug), PathBuilder (Laravel would
    // use Vietnamese path segments for it) and page-level publication (empty hubs). en/vi are unaffected.
    'allow_public_new_locales' => (bool) env('I18N_ALLOW_PUBLIC_NEW_LOCALES', false),
    /*
     * Phase 1E-d: authenticated preview (12 §8, A-7). key: shared with the website (CONTENT_PREVIEW_KEY), different
     * from the content key; entry_url: the website's preview entry (never under /api/*, which Nginx sends to Laravel).
     */
    'preview' => [
        'key' => env('CONTENT_PREVIEW_KEY'),
        'entry_url' => env('PREVIEW_ENTRY_URL', rtrim((string) env('FRONTEND_URL', env('APP_URL', '')), '/') . '/_draft/enter'),
    ],
    /*
     * 1E hardening: may the person who last wrote a translation (or a revision) approve it themselves? Default no:
     * every approval step (translation, SEO, legal) needs a different person. Publishing needs its own privilege.
     */
    'workflow' => [
        'allow_self_approval' => (bool) env('I18N_ALLOW_SELF_APPROVAL', false),
    ],
];
