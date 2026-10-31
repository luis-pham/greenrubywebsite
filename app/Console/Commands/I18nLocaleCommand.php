<?php

namespace App\Console\Commands;

use App\Services\Locale\FrontendManifest;
use App\Services\Locale\LocaleRegistry;
use App\Services\Locale\LocaleStatusService;
use App\Support\FrontendRevalidator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\BackEnd\Entities\AdLanguage;

/**
 * Locale registry state (phase 1B, greenruby-web docs/i18n-audit/phase-1/1b-report.md §3).
 *
 *   php artisan i18n:locale list
 *   php artisan i18n:locale manifest [--notify]           every web instance's readiness answer (fresh)
 *
 * --notify (scheduled every minute, phase 1D-b): when what the website may link for intl languages changed since the
 * last run (a web deploy or rollback, an instance down or back, a section added or removed), the website's caches
 * are expired so hreflang, switcher and intl pages follow at once. A no-op while no intl language is public.
 *   php artisan i18n:locale status ja editing --reason="…" [--dry-run]
 *
 * Transitions: disabled ⇄ editing ⇄ public (disabled → public is refused: a language is prepared first). Going
 * public is refused unless every web instance supports the language right now. en/vi (legacy) can never leave
 * 'public' here: withdrawing them would remove the existing site.
 */
class I18nLocaleCommand extends Command
{
    protected $signature = 'i18n:locale {action : list|manifest|status} {code?} {status?} {--reason=} {--dry-run} {--notify}';
    protected $description = 'List the locale registry, check frontend readiness, or change a language\'s site status';


    public function handle(): int
    {
        return match ($this->argument('action')) {
            'list' => $this->listLocales(),
            'manifest' => $this->manifest(),
            'status' => $this->changeStatus(),
            default => $this->refuse('Unknown action. Use list, manifest or status.'),
        };
    }

    private function listLocales(): int
    {
        if (!LocaleRegistry::migrated()) {
            $this->warn('Registry columns missing: run the 2026_11_01_000001 migration. Showing the legacy view.');
        }
        $this->table(
            ['id', 'code', 'native name', 'status', 'route style', 'url prefix', 'price list', 'x-default', 'effective public'],
            LocaleRegistry::all()->map(fn (AdLanguage $l) => [
                $l->id, $l->code, $l->getAttribute('native_name') ?? $l->name, LocaleRegistry::status($l),
                LocaleRegistry::isLegacy($l) ? 'legacy' : 'intl', var_export($l->getAttribute('url_prefix'), true),
                LocaleRegistry::priceListCode((string) $l->code) ?? '—', $l->getAttribute('xdefault_priority') ?? '—',
                LocaleRegistry::isEffectivePublic($l) ? 'yes' : 'no',
            ])->all()
        );

        return self::SUCCESS;
    }

    private function manifest(): int
    {
        // Scheduled run while no intl language is public: nothing to fetch (no request to the web instances).
        if ($this->option('notify') && LocaleRegistry::all()->every(fn (AdLanguage $l) => LocaleRegistry::isLegacy($l) || LocaleRegistry::status($l) !== 'public')) {
            return $this->notifyReadinessChange();
        }
        $instances = FrontendManifest::instances(true);
        if ($instances === []) {
            $this->warn('No FRONTEND_MANIFEST_URLS configured: no new language can be effectively public.');
        }
        $this->table(['instance', 'ok', 'build', 'ready', 'error'], array_map(fn ($i) => [
            $i['url'], $i['ok'] ? 'yes' : 'no', $i['buildId'] ?? '—', implode(', ', $i['ready']), $i['error'] ?? '',
        ], $instances));
        $this->line('Supported by every instance: ' . (implode(', ', FrontendManifest::supportedCodes()) ?: 'none'));

        return $this->option('notify') ? $this->notifyReadinessChange() : self::SUCCESS;
    }

    /** Expires the website's caches when LocaleRegistry::intlReadiness() differs from the last run's. */
    private function notifyReadinessChange(): int
    {
        $key = 'i18n.readiness.' . DB::connection()->getDatabaseName();
        $now = json_encode(LocaleRegistry::intlReadiness());
        $before = Cache::get($key);
        if ($before === $now || ($before === null && $now === '[]')) {
            Cache::forever($key, $now);
            $this->line('Intl readiness unchanged.');

            return self::SUCCESS;
        }
        Log::notice('Intl readiness changed: expiring website caches', ['before' => $before, 'now' => $now]);
        if (!FrontendRevalidator::send(['content'], true)) {
            // Keep the old value so the next run retries.
            $this->warn('Intl readiness changed, but the website could not be revalidated (FRONTEND_REVALIDATE_URL).');

            return self::FAILURE;
        }
        Cache::forever($key, $now);
        $this->info("Intl readiness changed ({$before} → {$now}): website caches expired.");

        return self::SUCCESS;
    }

    private function changeStatus(): int
    {
        $code = (string) $this->argument('code');
        $target = (string) $this->argument('status');
        $reason = trim((string) $this->option('reason'));
        $language = LocaleRegistry::migrated() ? LocaleRegistry::byCode($code) : null;
        if ($refusal = LocaleStatusService::refusal($language, $target, $reason)) {
            return $this->refuse($language || !LocaleRegistry::migrated() ? $refusal : "Unknown language '{$code}'.");
        }
        $current = LocaleRegistry::status($language);
        if ($current === $target) {
            $this->info("{$code} is already {$target}.");

            return self::SUCCESS;
        }
        if ($this->option('dry-run')) {
            $this->info("Dry run: {$code} {$current} → {$target}.");

            return self::SUCCESS;
        }
        $result = LocaleStatusService::change($language, $target, $reason, null, 'cli');
        $this->info("{$code}: {$result['from']} → {$result['to']}." . ($result['revalidated'] ? ' Website revalidated.' : ' Website NOT revalidated (check FRONTEND_REVALIDATE_URL).'));

        return self::SUCCESS;
    }

    private function refuse(string $message): int
    {
        $this->error($message);

        return self::FAILURE;
    }
}
