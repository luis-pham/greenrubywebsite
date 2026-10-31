<?php

namespace App\Console\Commands;

use App\Services\Content\ContentDraftImporter;
use App\Services\Content\EditorialActor;
use App\Services\Locale\LocaleRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Imports translation drafts of entity content (cruises, cabins + rooms, itineraries + programme lines,
 * destinations) into a new language (phase 2C; sibling of i18n:import-drafts, which imports CMS configuration):
 *
 *   php artisan i18n:import-content ja [--file=launch-content-ja-draft.json] [--apply] [--include-flagged]
 *       [--create-missing] [--actor=ID] [--report=path.json]
 *
 * DRY RUN by default. Per item: created | updated | skipped | conflict | needs_review | missing_target, with the
 * reason (ContentDraftImporter). Never publishes, never approves, never writes business/copy fields, links or
 * workflow steps; published targets get a pending revision only. --create-missing creates a missing translation as
 * an unpublished draft (TranslationFactory). Refused for en/vi and for a language not in status 'editing'; --apply
 * is refused outside a local green_ruby_i18n_* clone unless --i-know-this-is-production.
 */
class I18nImportContentCommand extends Command
{
    use Concerns\GuardsLocalDatabase;

    protected $signature = 'i18n:import-content {code}
        {--file= : content draft JSON (schema 1; default ../greenruby-web/docs/i18n-audit/phase-2c/content/launch-content-{code}-draft.json)}
        {--apply : write (default: dry run)}
        {--include-flagged : also import COMMERCIAL / LEGAL / NEEDS_CONTEXT items (BLOCKED never)}
        {--create-missing : create a missing translation (unpublished draft) through TranslationFactory}
        {--actor= : ad_user id (existing, active) recorded as translator / revision author / audit actor}
        {--report= : write the per-item report as JSON to this path}
        {--i-know-this-is-production : allow --apply outside a local green_ruby_i18n_* database}';
    protected $description = 'Import translation drafts of cruises, cabins, itineraries and destinations into a new language (dry run by default)';

    private const DEFAULT_DIR = '../greenruby-web/docs/i18n-audit/phase-2c/content';

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
            $actor = EditorialActor::signIn($this->option('actor'));
        } catch (\InvalidArgumentException $e) {
            return $this->refuse($e->getMessage());
        }

        $path = (string) ($this->option('file') ?: base_path(self::DEFAULT_DIR . "/launch-content-{$language->code}-draft.json"));
        if (!is_file($path)) {
            return $this->refuse("File not found: {$path}");
        }
        $data = json_decode((string) file_get_contents($path), true);
        if (!is_array($data) || !isset($data['items']) || !is_array($data['items'])) {
            return $this->refuse("Not a content draft file (no 'items'): {$path}");
        }
        if ((int) ($data['schema'] ?? 0) !== 1) {
            return $this->refuse('Unsupported schema ' . json_encode($data['schema'] ?? null) . ' (expected 1).');
        }
        if (isset($data['language']) && strtolower((string) $data['language']) !== strtolower((string) $language->code)) {
            return $this->refuse("The file is for '{$data['language']}', not '{$language->code}'.");
        }

        $importer = new ContentDraftImporter($language, $apply, (bool) $this->option('include-flagged'), (bool) $this->option('create-missing'), $actor);
        $results = $importer->run($data['items']);

        $counts = array_fill_keys(ContentDraftImporter::STATUSES, 0);
        foreach ($results as $r) {
            $counts[$r['status']]++;
            if ($this->getOutput()->isVerbose() || $r['status'] !== 'skipped') {
                $this->line(sprintf('%-14s %-16s %s', $r['status'], $r['id'], $r['reason']));
                foreach ($r['notes'] ?? [] as $note) {
                    $this->line(sprintf('%-14s %-16s   note: %s', '', '', $note));
                }
            }
        }
        if ($report = $this->option('report')) {
            file_put_contents($report, json_encode([
                'generated_at' => now()->toIso8601String(), 'database' => DB::connection()->getDatabaseName(),
                'language' => (string) $language->code, 'apply' => $apply, 'include_flagged' => (bool) $this->option('include-flagged'),
                'create_missing' => (bool) $this->option('create-missing'), 'actor' => $actor, 'file' => $path,
                'counts' => $counts, 'items' => $results,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
            $this->line("Report: {$report}");
        }
        $this->newLine();
        $this->line(($apply ? 'APPLIED' : 'DRY RUN (nothing written; pass --apply)') . ' — ' . count($results) . ' items');
        foreach ($counts as $status => $n) {
            $this->line(sprintf('  %-14s %d', $status, $n));
        }
        if ($apply) {
            Log::notice('i18n content drafts imported', ['code' => $language->code, 'counts' => $counts, 'actor' => $actor]);
        }

        return self::SUCCESS;
    }

    private function refuse(string $message): int
    {
        $this->error($message);

        return self::FAILURE;
    }
}
