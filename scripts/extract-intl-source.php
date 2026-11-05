<?php
/**
 * English source of the entity content a new intl language needs, in the i18n:import-content draft format with
 * the English text in `fields` (the translator replaces it) and its sha1 in `source_hash`. Scope: every English
 * row that already has a translation in --like (default ja), plus published English blog/news articles.
 *
 *   php scripts/extract-intl-source.php > content-source-en.json
 *
 * Read-only. Types: cruise, cabin (+ app_cabin_room), itinerary (+ app_itinerary_day_detail, with day/time as
 * context), destination, experience, faq_group, faq, article_category, article.
 */

use App\Services\Content\TranslatableFields;
use App\Services\Content\TranslationFactory;
use App\Services\Locale\LocaleRegistry;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$like = LocaleRegistry::byCode('ja');
$en = LocaleRegistry::byCode('en');
$types = ['cruise', 'cabin', 'itinerary', 'destination', 'experience', 'faq_group', 'faq', 'article_category', 'article'];
$items = [];

$hashFields = function (object $row, array $columns): array {
    $fields = $hashes = [];
    foreach ($columns as $column) {
        $value = $row->{$column} ?? null;
        if ($value === null || trim((string) $value) === '') {
            continue;
        }
        $fields[$column] = (string) $value;
        $hashes[$column] = sha1((string) $value);
    }

    return [$fields, $hashes];
};

foreach ($types as $type) {
    $meta = TranslatableFields::of($type);
    $rows = DB::table($meta['table'])->where('language_id', $en->id)->orderBy('id')->get();
    foreach ($rows as $row) {
        $hasJa = TranslationFactory::existingTranslation($type, (int) $row->id, (int) $like->id);
        $publishedArticle = $type === 'article' && (int) ($row->is_published ?? 0) === 1;
        if (!$hasJa && !$publishedArticle) {
            continue;
        }
        [$fields, $hashes] = $hashFields($row, $meta['text']);
        $children = [];
        if ($type === 'cabin') {
            foreach (DB::table('app_cabin_room')->where('cabin_id', $row->id)->orderBy('ord')->orderBy('id')->get() as $child) {
                [$f, $h] = $hashFields($child, ['title', 'description']);
                $children[] = ['table' => 'app_cabin_room', 'en_id' => (int) $child->id, 'fields' => $f, 'source_hash' => $h];
            }
        }
        if ($type === 'itinerary') {
            $details = DB::table('app_itinerary_day_detail')->join('app_itinerary_day', 'app_itinerary_day.id', '=', 'app_itinerary_day_detail.itinerary_day_id')
                ->where('app_itinerary_day.itinerary_id', $row->id)
                ->orderBy('app_itinerary_day.day')->orderBy('app_itinerary_day.id')->orderBy('app_itinerary_day_detail.ord')->orderBy('app_itinerary_day_detail.id')
                ->get(['app_itinerary_day_detail.*', 'app_itinerary_day.day']);
            foreach ($details as $child) {
                [$f, $h] = $hashFields($child, ['title', 'description']);
                $children[] = ['table' => 'app_itinerary_day_detail', 'en_id' => (int) $child->id, 'context' => ['day' => (int) $child->day, 'time' => $child->time ?? null], 'fields' => $f, 'source_hash' => $h];
            }
        }
        $items[] = array_filter([
            'type' => $type,
            'en_id' => (int) $row->id,
            'slug' => $row->slug ?? null,
            'fields' => $fields,
            'source_hash' => $hashes,
            'children' => $children,
        ], fn ($v) => $v !== null);
    }
}

echo json_encode(['schema' => 1, 'language' => 'en', 'source' => 'extract-intl-source.php ' . date('c'), 'items' => $items], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
