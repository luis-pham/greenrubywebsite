<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Applies a reviewed set of guarded text edits to CMS records from a JSON file (database/content-patches/*.json).
 *
 *   content:patch database/content-patches/2026-10-25-seo-safe-fixes.json           dry run: report only
 *   content:patch database/content-patches/2026-10-25-seo-safe-fixes.json --apply   write, in one transaction
 *
 * Each op names {table, id, column, type, old, new}. An op only writes when the stored value still matches
 * what was reviewed, so an edit made in the admin since then is never overwritten:
 *   replace       `old` must occur exactly once in the value; it becomes `new`
 *   json_replace  the value is JSON; exactly one string leaf equal to `old` becomes `new` (the value must
 *                 round-trip through json_encode with one of the usual flag sets, which is then reused)
 * Several ops on the same record and column apply in file order; the record is written once.
 *   set           the whole value must equal `old` (null allowed); it becomes `new`
 * Ops whose `new` text is already in place are reported as applied and skipped, so a file can be re-run.
 * Before writing, the previous values are saved to storage/app/content-patch-backups/. The command does not
 * revalidate Next.js: POST the tags listed in the file afterwards.
 */
class ContentPatchCommand extends Command
{
    protected $signature = 'content:patch {file : JSON patch file} {--apply : write the changes (default is a dry run)}';

    protected $description = 'Apply reviewed, guarded text edits to CMS records';

    public function handle(): int
    {
        $path = $this->argument('file');
        $patch = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
        if (!is_array($patch) || !isset($patch['ops']) || !is_array($patch['ops'])) {
            $this->error("Not a patch file: $path");

            return self::FAILURE;
        }

        // table|id|column => ['op' => first op, 'before' => stored value, 'after' => value after the ops so far]
        $planned = [];
        $problems = 0;
        foreach ($patch['ops'] as $i => $op) {
            $label = sprintf('#%d %s.%s id=%s', $i + 1, $op['table'] ?? '?', $op['column'] ?? '?', $op['id'] ?? '?');
            $key = implode('|', [$op['table'] ?? '', $op['id'] ?? '', $op['column'] ?? '']);
            [$state, $value] = $this->plan($op, $planned[$key]['after'] ?? null);
            if ($state === 'ok') {
                $planned[$key] ??= ['op' => $op, 'before' => $this->current($op)];
                $planned[$key]['after'] = $value;
                $this->line("<info>change</info>  $label  " . ($op['note'] ?? ''));
            } elseif ($state === 'applied') {
                $this->line("<comment>applied</comment> $label (already in place)");
            } else {
                $problems++;
                $this->line("<error>skip</error>    $label: $value");
            }
        }

        $this->newLine();
        $this->info(sprintf('%d to change, %d skipped with problems, %d ops in total.', count($planned), $problems, count($patch['ops'])));
        if (!$this->option('apply')) {
            $this->comment('Dry run: nothing written. Re-run with --apply.');

            return $problems ? self::FAILURE : self::SUCCESS;
        }
        if (!$planned) {
            return $problems ? self::FAILURE : self::SUCCESS;
        }

        $dir = storage_path('app/content-patch-backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $backup = $dir . '/' . pathinfo($path, PATHINFO_FILENAME) . '-' . date('Ymd-His') . '.json';
        file_put_contents($backup, json_encode(array_map(fn ($p) => [
            'table' => $p['op']['table'], 'id' => $p['op']['id'], 'column' => $p['op']['column'], 'value' => $p['before'],
        ], $planned), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        DB::transaction(function () use ($planned) {
            foreach ($planned as $p) {
                $op = $p['op'];
                $row = [$op['column'] => $p['after']];
                if (Schema::hasColumn($op['table'], 'updated_at')) {
                    $row['updated_at'] = now();
                }
                DB::table($op['table'])->where('id', $op['id'])->update($row);
            }
        });
        $this->info('Written. Backup of the previous values: ' . $backup);
        if (!empty($patch['revalidate'])) {
            $this->comment('Revalidate Next.js tags: ' . implode(', ', $patch['revalidate']));
        }

        return $problems ? self::FAILURE : self::SUCCESS;
    }

    protected function current(array $op)
    {
        return DB::table($op['table'])->where('id', $op['id'])->value($op['column']);
    }

    /**
     * @param mixed $working value after earlier ops on the same record and column (null: read the database)
     * @return array{0: 'ok'|'applied'|'problem', 1: mixed} new value, or the reason it cannot be applied
     */
    protected function plan(array $op, $working = null): array
    {
        foreach (['table', 'id', 'column', 'type'] as $key) {
            if (!isset($op[$key])) {
                return ['problem', "missing \"$key\""];
            }
        }
        if (!Schema::hasColumn($op['table'], $op['column'])) {
            return ['problem', 'unknown table or column'];
        }
        if (!DB::table($op['table'])->where('id', $op['id'])->exists()) {
            return ['problem', 'record not found'];
        }
        $value = $working ?? $this->current($op);
        $old = $op['old'] ?? null;
        $new = $op['new'] ?? null;

        switch ($op['type']) {
            case 'set':
                if ($value === $new) {
                    return ['applied', $value];
                }

                return $value === $old ? ['ok', $new] : ['problem', 'current value differs from the reviewed one'];

            case 'replace':
                $value = (string) $value;
                $count = substr_count($value, (string) $old);
                if ($count === 0 && $new !== '' && str_contains($value, (string) $new)) {
                    return ['applied', $value];
                }
                if ($count !== 1) {
                    return ['problem', "expected the reviewed text once, found it $count times"];
                }

                return ['ok', str_replace((string) $old, (string) $new, $value)];

            case 'json_replace':
                $data = json_decode((string) $value, true);
                $flags = null;
                foreach ([0, JSON_UNESCAPED_SLASHES, JSON_UNESCAPED_UNICODE, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE] as $candidate) {
                    if (is_array($data) && json_encode($data, $candidate) === $value) {
                        $flags = $candidate;
                        break;
                    }
                }
                if ($flags === null) {
                    return ['problem', 'value is not JSON that round-trips unchanged'];
                }
                $found = 0;
                $already = 0;
                array_walk_recursive($data, function (&$leaf) use ($old, $new, &$found, &$already) {
                    if ($leaf === $old) {
                        $leaf = $new;
                        $found++;
                    } elseif ($leaf === $new) {
                        $already++;
                    }
                });
                if ($found === 0 && $already > 0) {
                    return ['applied', $value];
                }

                return $found === 1 ? ['ok', json_encode($data, $flags)] : ['problem', "expected one matching JSON string, found $found"];
        }

        return ['problem', 'unknown type ' . $op['type']];
    }
}
