<?php

namespace App\Console\Commands;

use App\Services\Seo\KeywordMatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the keyword ownership registry (app_seo_owner) from database/data/seo-owners.php.
 *
 *   seo:owners-import            add missing rows (existing keyword + intent rows are left as edited in the app)
 *   seo:owners-import --update   also overwrite existing rows with the file
 *   seo:owners-import --dry-run  report only
 */
class SeoOwnersImportCommand extends Command
{
    protected $signature = 'seo:owners-import {file?} {--update} {--dry-run}';

    protected $description = 'Seed the SEO keyword ownership registry';

    public function handle(): int
    {
        $rows = require $this->argument('file') ?: database_path('data/seo-owners.php');
        $this->rekey();
        $languages = DB::table('ad_language')->pluck('id', 'code');
        $created = $updated = $kept = 0;
        foreach ($rows as [$lang, $keyword, $intent, $url, $type, $role, $cluster, $supporting, $status, $note]) {
            $data = [
                'language_id' => $languages[$lang],
                'keyword' => $keyword,
                'keyword_key' => KeywordMatcher::normalize($keyword),
                'intent' => $intent,
                'owner_url' => $url,
                'owner_type' => $type,
                'page_role' => $role,
                'cluster' => $cluster,
                'supporting_keywords' => json_encode($supporting, JSON_UNESCAPED_UNICODE),
                'status' => $status,
                'note' => $note,
            ];
            $key = ['language_id' => $data['language_id'], 'keyword_key' => $data['keyword_key'], 'intent' => $intent];
            $exists = DB::table('app_seo_owner')->where($key)->exists();
            if ($exists && !$this->option('update')) {
                $kept++;
                continue;
            }
            if (!$this->option('dry-run')) {
                $exists
                    ? DB::table('app_seo_owner')->where($key)->update($data + ['updated_at' => now()])
                    : DB::table('app_seo_owner')->insert($data + ['created_at' => now()]);
            }
            $exists ? $updated++ : $created++;
        }
        $this->info(sprintf('%d created, %d updated, %d kept%s.', $created, $updated, $kept, $this->option('dry-run') ? ' (dry run)' : ''));

        return self::SUCCESS;
    }

    /**
     * Recomputes keyword_key after a change to KeywordMatcher::normalize, so rows stay unique on the current
     * rule. When two rows now share a key, the newer one is kept and the older one removed.
     */
    private function rekey(): void
    {
        foreach (DB::table('app_seo_owner')->orderByDesc('id')->get() as $row) {
            $key = KeywordMatcher::normalize($row->keyword);
            if ($key === $row->keyword_key) {
                continue;
            }
            $clash = DB::table('app_seo_owner')->where('language_id', $row->language_id)->where('keyword_key', $key)
                ->where('intent', $row->intent)->where('id', '!=', $row->id)->exists();
            if ($this->option('dry-run')) {
                $this->line(($clash ? 'merge ' : 'rekey ') . $row->keyword);
                continue;
            }
            $clash ? DB::table('app_seo_owner')->where('id', $row->id)->delete()
                : DB::table('app_seo_owner')->where('id', $row->id)->update(['keyword_key' => $key]);
        }
    }
}
