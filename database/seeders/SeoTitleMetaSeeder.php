<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeoTitleMetaSeeder extends Seeder
{
    private const LANG_VI = 1;
    private const LANG_EN = 2;

    private const HUB_PAGE_CODES = [
        'Home' => 'homepage',
        'Itinerary' => 'itinerary',
        'Experiences' => 'exp-activity',
        'Services' => 'service',
        'Gallery' => 'gallery',
        'Blog' => 'article',
        'About' => 'about-us',
        'Contact' => 'contact',
        'Booking' => 'booking',
    ];

    /** duration + bay (1 = Ha Long, 2 = Lan Ha) */
    private const ITINERARY_MATCH = [
        'Lan Ha Escape (2D1N)' => ['duration' => 2, 'bay' => 2],
        'Halong Escape (2D1N)' => ['duration' => 2, 'bay' => 1],
        'Lan Ha Immersion (3D2N)' => ['duration' => 3, 'bay' => 2],
        'Halong Immersion (3D2N)' => ['duration' => 3, 'bay' => 1],
    ];

    public function run(): void
    {
        $csvPath = __DIR__ . '/data/GreenRuby_SEO_TitleMeta_Final.csv';
        if (!is_readable($csvPath)) {
            $this->command?->error('Missing CSV: ' . $csvPath);
            return;
        }

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            $this->command?->error('Cannot open CSV: ' . $csvPath);
            return;
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return;
        }

        $hubCount = 0;
        $itineraryCount = 0;
        $skipped = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 6) {
                continue;
            }

            [$group, $page, $enTitle, $enMeta, $viTitle, $viMeta] = $row;
            $group = trim($group);
            $page = trim($page);

            if ($group === 'HUB') {
                $hubCount += $this->seedHubPage($page, $enTitle, $enMeta, $viTitle, $viMeta);
                continue;
            }

            if ($group === 'ITINERARY') {
                $result = $this->seedItineraryPage($page, $enTitle, $enMeta, $viTitle, $viMeta);
                if ($result) {
                    $itineraryCount += $result;
                } else {
                    $skipped[] = $page;
                }
            }
        }

        fclose($handle);

        $this->command?->info("SEO seeded: {$hubCount} hub language rows, {$itineraryCount} itinerary language rows.");
        if ($skipped) {
            $this->command?->warn('Itinerary rows skipped (no matching duration/bay in DB): ' . implode(', ', $skipped));
        }
    }

    private function seedHubPage(string $page, string $enTitle, string $enMeta, string $viTitle, string $viMeta): int
    {
        if (!array_key_exists($page, self::HUB_PAGE_CODES)) {
            $this->command?->warn("Unknown hub page: {$page}");
            return 0;
        }

        $code = self::HUB_PAGE_CODES[$page];
        $count = 0;
        $count += $this->upsertHubSeo($code, self::LANG_EN, $enTitle, $enMeta) ? 1 : 0;
        $count += $this->upsertHubSeo($code, self::LANG_VI, $viTitle, $viMeta) ? 1 : 0;

        return $count;
    }

    private function upsertHubSeo(string $code, int $languageId, string $title, string $description): bool
    {
        $title = trim($title);
        $description = trim($description);
        $now = now();

        $updated = DB::table('app_page')
            ->where('code', $code)
            ->where('language_id', $languageId)
            ->update([
                'seo_title' => $title,
                'seo_description' => $description,
                'updated_at' => $now,
            ]);

        if ($updated > 0) {
            return true;
        }

        DB::table('app_page')->insert([
            'language_id' => $languageId,
            'code' => $code,
            'title' => $code,
            'description' => null,
            'seo_title' => $title,
            'seo_description' => $description,
            'created_at' => $now,
            'created_by' => 1,
            'updated_at' => $now,
            'updated_by' => null,
        ]);

        return true;
    }

    private function seedItineraryPage(string $label, string $enTitle, string $enMeta, string $viTitle, string $viMeta): int
    {
        if (!array_key_exists($label, self::ITINERARY_MATCH)) {
            $this->command?->warn("Unknown itinerary label: {$label}");
            return 0;
        }

        if (! $this->itinerarySeoColumnsExist()) {
            $this->command?->error('app_itinerary.seo_title column missing — run migrations first.');
            return 0;
        }

        $rule = self::ITINERARY_MATCH[$label];
        $count = 0;

        $enId = $this->findItineraryId($rule['duration'], $rule['bay'], self::LANG_EN);
        $viId = $this->findItineraryId($rule['duration'], $rule['bay'], self::LANG_VI);

        if ($enId) {
            $this->updateItinerarySeo($enId, $enTitle, $enMeta);
            $count++;
        }

        if ($viId) {
            $this->updateItinerarySeo($viId, $viTitle, $viMeta);
            $count++;
        }

        return $count > 0 ? $count : 0;
    }

    private function itinerarySeoColumnsExist(): bool
    {
        return in_array('seo_title', Schema::getColumnListing('app_itinerary'), true);
    }

    private function findItineraryId(int $duration, int $bay, int $languageId): ?int
    {
        $id = DB::table('app_itinerary')
            ->where('language_id', $languageId)
            ->where('duration', $duration)
            ->where('bay', $bay)
            ->orderBy('id')
            ->value('id');

        return $id ? (int) $id : null;
    }

    private function updateItinerarySeo(int $id, string $title, string $description): void
    {
        DB::table('app_itinerary')
            ->where('id', $id)
            ->update([
                'seo_title' => trim($title),
                'seo_description' => trim($description),
                'updated_at' => now(),
            ]);
    }
}
