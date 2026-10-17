<?php

namespace App\Console\Commands;

use App\Services\Content\ContentTranslations;
use Illuminate\Console\Command;
use Modules\BackEnd\Services\AdLanguageService;

/**
 * EN <-> VI translation links (same data as admin "Liên kết bản dịch").
 *
 *   content:translations list [type]          current pairing per row (linked or heuristic)
 *   content:translations link cabin 24 38     link rows as translations of each other (one id = no translation)
 *   content:translations unlink cabin 24      back to the heuristic
 *   content:translations freeze [type]        store every heuristic pair as an explicit link (--dry-run to preview)
 */
class ContentTranslationsCommand extends Command
{
    protected $signature = 'content:translations {action : list|link|unlink|freeze} {type?} {ids?*} {--dry-run}';

    protected $description = 'List, link, unlink or freeze EN <-> VI translation links of content rows';

    public function handle(): int
    {
        $action = $this->argument('action');
        $type = $this->argument('type');
        $ids = array_map('intval', $this->argument('ids'));
        if ($type !== null && !isset(ContentTranslations::TYPES[$type])) {
            $this->error('Unknown type. Use one of: ' . implode(', ', array_keys(ContentTranslations::TYPES)));

            return self::FAILURE;
        }
        $types = $type ? [$type] : array_keys(ContentTranslations::TYPES);

        switch ($action) {
            case 'list':
                foreach ($types as $t) {
                    $this->listType($t);
                }

                return self::SUCCESS;
            case 'link':
            case 'unlink':
                if (!$type || $ids === []) {
                    $this->error("Usage: content:translations $action <type> <id> [id]");

                    return self::FAILURE;
                }
                $action === 'link' ? ContentTranslations::link($type, $ids) : ContentTranslations::unlink($type, $ids[0]);
                $this->info(ucfirst($action) . "ed $type [" . implode(', ', $ids) . ']');

                return self::SUCCESS;
            case 'freeze':
                foreach ($types as $t) {
                    $this->freezeType($t, (bool) $this->option('dry-run'));
                }

                return self::SUCCESS;
        }

        $this->error('Unknown action: ' . $action);

        return self::FAILURE;
    }

    private function listType(string $type): void
    {
        [$source, $target] = $this->languages();
        $targetItems = ContentTranslations::items($type, (int) $target->id)->keyBy('id');
        $rows = [];
        $paired = [];
        foreach (ContentTranslations::items($type, (int) $source->id) as $item) {
            $id = ContentTranslations::effectiveCounterpartId($type, $item->id, $source, $target);
            $paired[] = $id;
            $rows[] = [
                $item->id,
                $item->name,
                $id ? $id . ' ' . ($targetItems[$id]->name ?? '?') : '-',
                ContentTranslations::isLinked($type, $item->id) ? 'linked' : 'auto',
            ];
        }
        foreach ($targetItems->except(array_filter($paired)) as $item) {
            $rows[] = ['-', '-', $item->id . ' ' . $item->name, 'unpaired'];
        }

        $this->line("<info>$type</info>");
        $this->table([strtoupper($source->code) . ' id', 'name', strtoupper($target->code), 'state'], $rows);
    }

    /** Pins what the heuristics resolve today, so later edits (image, ord, ...) cannot silently break it. */
    private function freezeType(string $type, bool $dryRun): void
    {
        [$source, $target] = $this->languages();
        $count = 0;
        foreach (ContentTranslations::items($type, (int) $source->id) as $item) {
            if (ContentTranslations::isLinked($type, $item->id)) {
                continue;
            }
            $targetId = ContentTranslations::effectiveCounterpartId($type, $item->id, $source, $target);
            if (!$targetId || ContentTranslations::isLinked($type, $targetId)) {
                continue;
            }
            $this->line(($dryRun ? '[dry-run] ' : '') . "$type {$item->id} <-> $targetId");
            if (!$dryRun) {
                ContentTranslations::link($type, [$item->id, $targetId]);
            }
            $count++;
        }
        $this->info("$type: " . ($dryRun ? 'would link' : 'linked') . " $count pair(s)");
    }

    private function languages(): array
    {
        $source = AdLanguageService::getDefaultLanguage();

        return [$source, AdLanguageService::getAll()->firstWhere('id', '!=', $source->id)];
    }
}
