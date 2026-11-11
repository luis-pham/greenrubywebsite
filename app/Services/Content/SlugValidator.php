<?php

namespace App\Services\Content;

use Illuminate\Support\Facades\DB;

/**
 * Slug rules (owner decision #4, phase 1C-3): lowercase ASCII words joined by single hyphens, 1–160 characters,
 * not a reserved path or route segment, and unique where the URL has no id. Returns an error code or null.
 */
final class SlugValidator
{
    public const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    /** Entity type => [table, uniqueness scope columns]. Types with the id in the URL need no uniqueness. */
    public const TYPES = [
        'cruise' => ['table' => 'app_cruise', 'unique' => null],
        'cabin' => ['table' => 'app_cabin', 'unique' => null],
        'itinerary' => ['table' => 'app_itinerary', 'unique' => null],
        'experience' => ['table' => 'app_exp_activity', 'unique' => null],
        'destination' => ['table' => 'app_destination', 'unique' => ['language_id']],
        'article' => ['table' => 'app_article', 'unique' => ['language_id', 'category_id']],
        'article_category' => ['table' => 'app_category', 'unique' => ['language_id', 'type']],
        'faq_group' => ['table' => 'app_group', 'unique' => ['language_id', 'type']],
    ];

    /** Paths the site, Nginx or Laravel own, and pagination words: a slug may never be one of them. */
    public static function reserved(): array
    {
        $words = ['api', 'admincp', 'upload', 'uploads', 'thumbnail', 'payment', 'assets', 'static', 'public', '_next',
            'sitemap', 'robots', 'llms', 'search', 'tim-kiem', 'page', 'trang', 'root', 'index', 'en', 'vi', 'ja', 'fr', 'es',
            'new', 'edit', 'preview', '_draft', 'feed', 'rss', 'amp'];
        // Every registered language code is a URL prefix (/ja, /fr …), whatever the list above names.
        foreach (\App\Services\Locale\LocaleRegistry::all() as $language) {
            $words[] = strtolower((string) $language->code);
        }
        foreach (PathBuilder::SEGMENTS as $segments) {
            foreach ($segments as $segment) {
                $words[] = explode('/', $segment)[0];
            }
        }

        return array_values(array_unique($words));
    }

    /**
     * @param array $row the row being validated (needs language_id and the scope columns of its type)
     * @return string|null error code: slug_empty, slug_too_long, slug_format, slug_reserved, slug_taken
     */
    public static function check(string $type, string $slug, array $row, ?int $exceptId = null): ?string
    {
        if ($slug === '') {
            return 'slug_empty';
        }
        if (strlen($slug) > 160) {
            return 'slug_too_long';
        }
        if (!preg_match(self::PATTERN, $slug)) {
            return 'slug_format';
        }
        if (in_array($slug, self::reserved(), true) || preg_match('/^(page|trang)-\d+$/', $slug)) {
            return 'slug_reserved';
        }
        $meta = self::TYPES[$type] ?? null;
        if ($meta && $meta['unique']) {
            $query = DB::table($meta['table'])->where('slug', $slug);
            foreach ($meta['unique'] as $column) {
                $query->where($column, $row[$column] ?? null);
            }
            if ($exceptId !== null) {
                $query->where('id', '<>', $exceptId);
            }
            if ($query->exists()) {
                return 'slug_taken';
            }
            // An old slug of another row of the same scope still redirects there: taking it would shadow that redirect.
            $history = DB::table('app_slug_history')->where('entity_type', $type)->where('language_id', $row['language_id'] ?? null)
                ->where('old_slug', $slug);
            if ($exceptId !== null) {
                $history->where('entity_id', '<>', $exceptId);
            }
            if ($history->exists()) {
                return 'slug_taken';
            }
        }

        return null;
    }
}
