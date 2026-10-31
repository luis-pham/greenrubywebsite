<?php

namespace App\Console\Commands;

use App\Services\Content\DraftImporter;
use App\Services\Content\EditorialActor;
use App\Services\Locale\LocaleRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Imports the phase 2A translation drafts of CMS configuration text into a new language's hidden rows (phase 2B):
 *
 *   php artisan i18n:import-drafts ja [--source=cms-config-source.json] [--drafts|--file=cms-config-ja-draft.json]
 *       [--apply] [--include-flagged] [--create-missing] [--actor=ID] [--report=path.json]
 *
 * DRY RUN by default: nothing is written without --apply. Per draft row: created | updated | skipped | conflict |
 * needs_review | missing_target, with the reason (DraftImporter). Only PENDING_REVIEW drafts are imported;
 * --include-flagged adds LEGAL/COMMERCIAL_REVIEW_REQUIRED, NEEDS_CONTEXT never. --create-missing creates a missing
 * TranslatableFields draft through TranslationFactory (unpublished). Rows are never created otherwise: run
 * i18n:seed-locale for pages, menus, config and structural groups. Creating an article category needs --actor (an
 * existing, active admin user: AppCategoryService stamps created_by); without it those rows are missing_target.
 * Entity content drafts (cruise, cabin, itinerary, destination, experience, FAQ group, FAQ) are imported by i18n:import-content.
 *
 * Refused for en/vi and for a language not in status 'editing'. --apply is refused unless the connection is a local
 * green_ruby_i18n_* clone (or the test database); production use needs --i-know-this-is-production and a separate
 * authorisation.
 */
class I18nImportDraftsCommand extends Command
{
    use Concerns\GuardsLocalDatabase;

    protected $signature = 'i18n:import-drafts {code}
        {--source= : cms-config-source.json (English rows of the 2026-10-29 export)}
        {--drafts= : cms-config-ja-draft.json}
        {--file= : same as --drafts (e.g. phase-2c/cms/cms-config-ja-draft.json)}
        {--apply : write (default: dry run)}
        {--include-flagged : also import LEGAL_REVIEW_REQUIRED / COMMERCIAL_REVIEW_REQUIRED drafts}
        {--create-missing : create a missing TranslatableFields translation (unpublished) through TranslationFactory}
        {--actor= : ad_user id (existing, active) recorded as translator / revision author / audit actor; needed to create article categories}
        {--report= : write the per-row report as JSON to this path}
        {--i-know-this-is-production : allow --apply outside a local green_ruby_i18n_* database}';
    protected $description = 'Import translation drafts of CMS configuration text into a new language (dry run by default)';

    private const DEFAULT_DIR = '../greenruby-web/docs/i18n-audit/phase-2a/cms';

    public function handle(): int
    {
        $language = LocaleRegistry::byCode((string) $this->argument('code'));
        if (!$language) {
            return $this->refuse('Unknown language.');
        }
        if (LocaleRegistry::isLegacy($language)) {
            return $this->refuse("{$language->code} is a legacy language (en/vi): drafts are only imported into a new language.");
        }
        if (LocaleRegistry::status($language) !== 'editing') {
            return $this->refuse("{$language->code} is '" . LocaleRegistry::status($language) . "': drafts are only imported while the language is 'editing'.");
        }
        $apply = (bool) $this->option('apply');
        if ($apply && ($why = $this->notLocal()) !== null) {
            if (!$this->option('i-know-this-is-production')) {
                return $this->refuse("--apply refused: {$why}. Production import is a separate, authorised step (--i-know-this-is-production).");
            }
            $this->warn("WARNING: {$why}; writing anyway (--i-know-this-is-production).");
        }

        try {
            $actor = EditorialActor::signIn($this->option('actor')); // an existing, active admin user, or null (the CLI)
        } catch (\InvalidArgumentException $e) {
            return $this->refuse($e->getMessage());
        }
        if ($this->option('drafts') && $this->option('file') && $this->option('drafts') !== $this->option('file')) {
            return $this->refuse('Give the drafts file once (--drafts or --file).');
        }

        $sourcePath = (string) ($this->option('source') ?: base_path(self::DEFAULT_DIR . '/cms-config-source.json'));
        $draftPath = (string) ($this->option('drafts') ?: $this->option('file') ?: base_path(self::DEFAULT_DIR . "/cms-config-{$language->code}-draft.json"));
        $sources = $this->readJson($sourcePath);
        $drafts = $this->readJson($draftPath);
        if ($sources === null || $drafts === null) {
            return self::FAILURE;
        }
        $sourceRows = [];
        foreach ($sources['rows'] ?? [] as $row) {
            $sourceRows[$row['id']] = $row;
        }

        $importer = new DraftImporter($language, $apply, (bool) $this->option('include-flagged'), (bool) $this->option('create-missing'), $actor);
        $results = $importer->run($sourceRows, $drafts['rows'] ?? []);

        $counts = array_fill_keys(DraftImporter::STATUSES, 0);
        foreach ($results as $r) {
            $counts[$r['status']]++;
            if ($this->getOutput()->isVerbose() || !in_array($r['status'], ['skipped'], true)) {
                $this->line(sprintf('%-14s %s  %s', $r['status'], $r['id'], $r['reason']));
            }
        }
        if ($path = $this->option('report')) {
            $report = [
                'generated_at' => now()->toIso8601String(), 'database' => DB::connection()->getDatabaseName(),
                'language' => (string) $language->code, 'apply' => $apply, 'include_flagged' => (bool) $this->option('include-flagged'),
                'create_missing' => (bool) $this->option('create-missing'), 'actor' => $actor,
                'source' => $sourcePath, 'drafts' => $draftPath, 'counts' => $counts, 'rows' => $results,
            ];
            file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
            $this->line("Report: {$path}");
        }
        $this->newLine();
        $this->line(($apply ? 'APPLIED' : 'DRY RUN (nothing written; pass --apply)') . ' — ' . count($results) . ' drafts');
        foreach ($counts as $status => $n) {
            $this->line(sprintf('  %-14s %d', $status, $n));
        }
        if ($apply) {
            Log::notice('i18n drafts imported', ['code' => $language->code, 'counts' => $counts, 'actor' => $actor]);
        }

        return self::SUCCESS;
    }

    private function readJson(string $path): ?array
    {
        if (!is_file($path)) {
            $this->error("File not found: {$path}");

            return null;
        }
        $data = json_decode((string) file_get_contents($path), true);
        if (!is_array($data) || !isset($data['rows']) || !is_array($data['rows'])) {
            $this->error("Not a draft/source file (no 'rows'): {$path}");

            return null;
        }

        return $data;
    }

    private function refuse(string $message): int
    {
        $this->error($message);

        return self::FAILURE;
    }
}
