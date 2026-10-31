<?php

namespace App\Console\Commands;

use App\Services\Content\SlugService;
use Illuminate\Console\Command;

/**
 * Deliberate URL change (owner decision #4, phase 1C-3):
 *   php artisan content:slug destination 17 thien-cung-cave --reason="…" [--dry-run]
 * Validates the new slug (format, reserved paths, uniqueness, old slugs still redirecting), records the old one in
 * app_slug_history and updates the row in one transaction. Old URLs then redirect permanently: id-based types
 * (cruise, cabin, itinerary, experience) through the website's existing canonical-slug redirect; slug-only types
 * (destination, article, article category, FAQ group) through the Content API's redirect answer.
 */
class ContentSlugCommand extends Command
{
    protected $signature = 'content:slug {type} {id} {slug} {--reason=} {--dry-run}';
    protected $description = 'Change the public slug of a content row (with history and redirect)';

    public function handle(): int
    {
        $type = (string) $this->argument('type');
        $new = (string) $this->argument('slug');
        try {
            $result = SlugService::change($type, (int) $this->argument('id'), $new, (string) $this->option('reason'), null, (bool) $this->option('dry-run'));
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
        if ($this->option('dry-run')) {
            $this->info("Dry run: {$type} #{$this->argument('id')} '{$result['from']}' → '{$result['to']}'.");
        } elseif (!$result['changed']) {
            $this->info("{$type} #{$this->argument('id')} already has slug '{$new}'.");
        } else {
            $this->info("{$type} #{$this->argument('id')}: '{$result['from']}' → '{$result['to']}'.");
        }

        return self::SUCCESS;
    }
}
