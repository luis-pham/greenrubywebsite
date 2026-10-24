<?php

namespace App\Console\Commands;

use App\Services\Content\ContentTranslations;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppDestination;
use Modules\BackEnd\Services\AdLanguageService;
use Modules\BackEnd\Services\AppDestinationService;

/**
 * Imports destination pairs from a PHP data file (default: database/data/destinations-initial.php).
 *
 *   destinations:import                 create missing destinations (existing slugs are left untouched)
 *   destinations:import --update        also overwrite existing rows with the file's content
 *   destinations:import --status=review import as "Chờ kiểm duyệt" instead of published
 *   destinations:import --update --only=sung-sot-cave   refresh one row (other rows untouched)
 *
 * Images listed with a "file" are copied from database/data/destination-images into the upload folder
 * and registered in the media library (app_file) when missing. Each pair is linked in
 * content_translations, and the itinerary ids are linked as planned Green Ruby stops.
 * A CLI import does not revalidate Next.js: POST the "destinations" tag afterwards.
 */
class DestinationsImportCommand extends Command
{
    protected $signature = 'destinations:import {file? : PHP file returning the destination pairs} {--update} {--status=published} {--only=* : import only these slugs (either language)} {--dry-run}';

    protected $description = 'Import destinations (EN/VI pairs) for the destinations hub';

    public function handle(): int
    {
        $file = $this->argument('file') ?: database_path('data/destinations-initial.php');
        $status = (string) $this->option('status');
        if (!array_key_exists($status, config('backend.destinationStatus'))) {
            $this->error('Unknown status ' . $status);

            return self::FAILURE;
        }
        $languages = AdLanguageService::getAll()->keyBy('code');
        foreach (['en', 'vi'] as $code) {
            if (!$languages->has($code)) {
                $this->error("Language $code is not configured");

                return self::FAILURE;
            }
        }

        $pairs = require $file;
        foreach ($pairs as $pair) {
            $ids = [];
            foreach (['en', 'vi'] as $code) {
                $ids[$code] = $this->importOne($pair, $pair[$code], (int) $languages[$code]->id, $pair['itineraries'][$code] ?? [], $status);
            }
            if (!$this->option('dry-run') && $ids['en'] && $ids['vi']) {
                ContentTranslations::link('destination', array_values($ids));
            }
        }

        $this->info('Done. Revalidate the frontend tag "destinations" (or "content").');

        return self::SUCCESS;
    }

    private function importOne(array $shared, array $local, int $languageId, array $itineraryIds, string $status): ?int
    {
        $only = (array) $this->option('only');
        $existing = AppDestination::where('language_id', $languageId)->where('slug', $local['slug'])->first();
        if ($only !== [] && !in_array($local['slug'], $only, true)) {
            return $existing ? (int) $existing->id : null;
        }
        if ($existing && !$this->option('update')) {
            $this->line("skip  {$local['slug']} (exists, id {$existing->id})");

            return (int) $existing->id;
        }
        if ($this->option('dry-run')) {
            $this->line(($existing ? 'update' : 'create') . " {$local['slug']}");

            return $existing?->id;
        }

        $image = $shared['image'] ?? null;
        if ($image) {
            $this->ensureImage($image, $local['image_alt'] ?? $local['name']);
        }

        $data = [
            'slug' => $local['slug'],
            'name' => $local['name'],
            'heading' => $local['heading'] ?? null,
            'bay' => $shared['bay'],
            'category' => $shared['category'],
            'summary' => $local['summary'],
            'content' => trim($local['content']),
            'facts' => $local['facts'] ?? [],
            'faq' => $local['faq'] ?? [],
            'sources' => $local['sources'] ?? $shared['sources'] ?? [], // per-language override allowed
            'latitude' => $shared['latitude'],
            'longitude' => $shared['longitude'],
            'coords_verified' => $shared['coords_verified'],
            'coords_source' => $shared['coords_source'],
            'image_link' => $image['link'] ?? null,
            'image_alt' => $image ? ($local['image_alt'] ?? null) : null,
            'image_credit' => $local['image_credit'] ?? $shared['image_credit'] ?? null,
            'status' => $status,
            'editor_notes' => $shared['editor_notes'] ?? null,
            'fact_checked_at' => '2026-10-24',
            'seo_title' => $local['seo_title'] ?? null,
            'seo_description' => $local['seo_description'] ?? null,
            'ord' => $shared['ord'] ?? 0,
            'itinerary_ids' => $itineraryIds,
        ];

        if ($existing) {
            AppDestinationService::update($data + ['id' => $existing->id], $languageId);
            $this->line("update {$local['slug']} (id {$existing->id})");

            return (int) $existing->id;
        }
        $id = AppDestinationService::create($data, $languageId);
        $this->line("create {$local['slug']} (id $id)");

        return (int) $id;
    }

    /** Copies a bundled image into the upload folder and registers it in the media library. */
    private function ensureImage(array $image, string $description): void
    {
        $target = public_path('upload' . $image['link']);
        if (!empty($image['file']) && !is_file($target)) {
            $source = database_path('data/destination-images/' . $image['file']);
            if (!is_file($source)) {
                $this->warn("missing bundled image {$image['file']}");

                return;
            }
            if (!is_dir(dirname($target))) {
                mkdir(dirname($target), 0775, true);
            }
            copy($source, $target);
        }
        if (is_file($target) && !DB::table('app_file')->where('link', $image['link'])->exists()) {
            DB::table('app_file')->insert([
                'name' => basename($image['link']),
                'link' => $image['link'],
                'size' => filesize($target),
                'extension' => strtolower(pathinfo($target, PATHINFO_EXTENSION)),
                'description' => $description,
                'is_360' => 0,
                'created_at' => now(),
                'created_by' => 1,
            ]);
        }
    }
}
