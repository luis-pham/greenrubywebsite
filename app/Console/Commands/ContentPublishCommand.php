<?php

namespace App\Console\Commands;

use App\Observers\PublishGuardObserver;
use App\Services\Publication\PublicationPolicy;
use App\Support\FrontendRevalidator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Publish or unpublish one content row (phase 1C-2), through Eloquent so PublishGuardObserver applies:
 *   php artisan content:publish cabin 24 unpublished --reason="…" [--dry-run]
 * Types: cruise, cabin, itinerary, experience, faq, faq_group, article_category (publish_state).
 */
class ContentPublishCommand extends Command
{
    protected $signature = 'content:publish {type} {id} {state : published|unpublished} {--reason=} {--dry-run}';
    protected $description = 'Publish or unpublish a content row (row-level publication)';

    public function handle(): int
    {
        $type = (string) $this->argument('type');
        $state = (string) $this->argument('state');
        $model = array_search($type, PublishGuardObserver::MODELS, true);
        if ($model === false || (PublicationPolicy::TYPES[$type]['flag'] ?? null) !== 'publish_state') {
            $this->error('Type must be one of: cruise, cabin, itinerary, experience, faq, faq_group, article_category.');

            return self::FAILURE;
        }
        if (!in_array($state, ['published', 'unpublished'], true)) {
            $this->error('State must be published or unpublished.');

            return self::FAILURE;
        }
        if (mb_strlen(trim((string) $this->option('reason'))) < 10) {
            $this->error('Give a --reason of at least 10 characters (it is logged).');

            return self::FAILURE;
        }
        if (!PublicationPolicy::hasColumn(PublicationPolicy::TYPES[$type]['table'])) {
            $this->error('publish_state is missing: run the 2026_11_02_000001 migration first.');

            return self::FAILURE;
        }
        $row = $model::find((int) $this->argument('id'));
        if (!$row) {
            $this->error("{$type} #{$this->argument('id')} not found.");

            return self::FAILURE;
        }
        $from = $row->publish_state;
        if ($this->option('dry-run')) {
            $this->info("Dry run: {$type} #{$row->id} {$from} → {$state}.");

            return self::SUCCESS;
        }
        try {
            $row->publish_state = $state;
            $row->save();
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
        Log::notice('Content publication changed', ['type' => $type, 'id' => $row->id, 'from' => $from, 'to' => $state, 'reason' => $this->option('reason')]);
        // Expire at once: pages linking the row (hreflang, switcher, lists) must drop or add it in one pass.
        FrontendRevalidator::send(['content'], true);
        $this->info("{$type} #{$row->id}: {$from} → {$state}.");

        return self::SUCCESS;
    }
}
