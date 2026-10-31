<?php

namespace App\Console\Commands;

use App\Services\Locale\LocaleRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Prepares a new content language (phase 1E-b/c; plan §3 `i18n:seed-locale`):
 *
 *   php artisan i18n:seed-locale ja --from=en [--dry-run]
 *
 * Creates the per-language rows the admin and the Content API need, as untranslated, HIDDEN copies of the source
 * language, each linked to its source in translation_meta (entity types group, page, menu):
 *   - structural app_group rows (cabin facility types, experience tabs, service groups; same slug): TranslationFactory
 *     maps a cabin's or service's group through them. FAQ groups (type faq) are content: translate them in the matrix;
 *   - app_page rows with their sections and page configuration, is_visible = 0 (page-level publication);
 *   - per-language site configuration (ad_config) and front-end menus.
 * Never touches business tables (prices, departures, capacities). Idempotent: rows already seeded are skipped.
 * Refused for en/vi and for a disabled language.
 */
class I18nSeedLocaleCommand extends Command
{
    protected $signature = 'i18n:seed-locale {code} {--from=en} {--dry-run}';
    protected $description = 'Create the hidden per-language pages, groups, menus and config of a new content language';

    public function handle(): int
    {
        $target = LocaleRegistry::byCode((string) $this->argument('code'));
        $source = LocaleRegistry::byCode((string) $this->option('from'));
        if (!$target || !$source) {
            $this->error('Unknown language.');

            return self::FAILURE;
        }
        if (LocaleRegistry::isLegacy($target) || !LocaleRegistry::isEditable($target)) {
            $this->error("{$target->code}: only a new language in status 'editing' (or public) can be seeded.");

            return self::FAILURE;
        }
        $dry = (bool) $this->option('dry-run');
        $from = (int) $source->id;
        $to = (int) $target->id;
        $counts = ['group' => 0, 'page' => 0, 'page_section' => 0, 'page_config' => 0, 'ad_config' => 0, 'menu' => 0];
        $faqType = (int) config('backend.groupType.faq');

        $sourceCode = (string) $source->code;
        $targetCode = (string) $target->code;
        $run = function () use ($from, $to, $faqType, &$counts, $dry, $sourceCode, $targetCode) {
            $now = now();
            foreach (DB::table('app_group')->where('language_id', $from)->where('type', '<>', $faqType)->orderBy('id')->get() as $group) {
                if ($this->seeded('group', (int) $group->id, $to) || DB::table('app_group')->where('language_id', $to)->where('type', $group->type)->where('slug', $group->slug)->exists()) {
                    continue;
                }
                $counts['group']++;
                if (!$dry) {
                    $row = (array) $group;
                    unset($row['id']);
                    $id = DB::table('app_group')->insertGetId(['language_id' => $to, 'created_at' => $now, 'updated_at' => $now] + $row);
                    $this->link('group', $id, $to, (int) $group->id, $from, $now);
                }
            }
            foreach (DB::table('app_page')->where('language_id', $from)->orderBy('id')->get() as $page) {
                if (DB::table('app_page')->where('language_id', $to)->where('code', $page->code)->exists()) {
                    continue;
                }
                $counts['page']++;
                $sections = DB::table('app_page_section')->where('page_id', $page->id)->orderBy('id')->get();
                $configs = DB::table('app_page_config')->where('page_id', $page->id)->orderBy('id')->get();
                $counts['page_section'] += $sections->count();
                $counts['page_config'] += $configs->count();
                if ($dry) {
                    continue;
                }
                $row = (array) $page;
                unset($row['id']);
                $pageId = DB::table('app_page')->insertGetId(['language_id' => $to, 'is_visible' => 0, 'created_at' => $now, 'updated_at' => $now] + $row);
                $sectionIds = [];
                foreach ($sections as $section) {
                    $s = (array) $section;
                    unset($s['id']);
                    $sectionIds[$section->id] = DB::table('app_page_section')->insertGetId(['page_id' => $pageId] + $s);
                }
                foreach ($configs as $config) {
                    $c = (array) $config;
                    unset($c['id']);
                    DB::table('app_page_config')->insert(['page_id' => $pageId, 'section_id' => $sectionIds[$config->section_id] ?? $config->section_id] + $c);
                }
                $this->link('page', $pageId, $to, (int) $page->id, $from, $now);
            }
            // Per-language site configuration keys end with the language code ("website-name-en"; keys are unique):
            // the new language gets "website-name-ja". Keys without the suffix are shared and not copied.
            foreach (DB::table('ad_config')->where('language_id', $from)->orderBy('id')->get() as $config) {
                $suffix = '-' . strtolower((string) $sourceCode);
                if (!str_ends_with(strtolower((string) $config->key), $suffix)) {
                    continue;
                }
                $key = substr((string) $config->key, 0, -strlen($suffix)) . '-' . strtolower((string) $targetCode);
                if (DB::table('ad_config')->where('key', $key)->exists()) {
                    continue;
                }
                $counts['ad_config']++;
                if (!$dry) {
                    $row = (array) $config;
                    unset($row['id']);
                    DB::table('ad_config')->insert(['language_id' => $to, 'key' => $key] + $row);
                }
            }
            foreach (DB::table('app_menu_front_end')->where('language_id', $from)->orderBy('id')->get() as $menu) {
                if (DB::table('app_menu_front_end')->where('language_id', $to)->where('code', $menu->code)->exists()) {
                    continue;
                }
                $counts['menu']++;
                if (!$dry) {
                    $row = (array) $menu;
                    unset($row['id']);
                    $id = DB::table('app_menu_front_end')->insertGetId(['language_id' => $to, 'created_at' => $now] + $row);
                    $this->link('menu', $id, $to, (int) $menu->id, $from, $now);
                }
            }
        };
        $dry ? $run() : DB::transaction($run);

        foreach ($counts as $what => $n) {
            $this->line(sprintf('%-13s %d%s', $what, $n, $dry ? ' (dry run)' : ''));
        }
        if (!$dry) {
            Log::notice('Locale seeded', ['code' => $target->code, 'from' => $source->code, 'counts' => $counts]);
        }

        return self::SUCCESS;
    }

    private function seeded(string $type, int $sourceId, int $to): bool
    {
        return DB::table('translation_meta')->where('entity_type', $type)->where('source_entity_id', $sourceId)->where('language_id', $to)->exists();
    }

    private function link(string $type, int $id, int $to, int $sourceId, int $from, $now): void
    {
        DB::table('translation_meta')->insert([
            'entity_type' => $type, 'entity_id' => $id, 'language_id' => $to, 'workflow' => 'draft',
            'source_entity_id' => $sourceId, 'source_language_id' => $from, 'updated_at' => $now,
        ]);
    }
}
