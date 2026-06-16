<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\BackEnd\Entities\AppArticle;

class BlogCleanupSeeder extends Seeder
{
    /**
     * Unpublish articles that are clearly off-topic for a Ha Long / Lan Ha cruise brand.
     * Safe to re-run: only touches published rows matching these patterns.
     */
    private const OFF_TOPIC_TITLE_PATTERNS = [
        'wedding',
        'casino',
        'bitcoin',
        'crypto',
        'stock market',
        'real estate',
        'fashion week',
        'k-pop',
        'premier league',
        'nfl ',
        'nba ',
    ];

    public function run(): void
    {
        $unpublished = 0;

        AppArticle::where('is_published', true)
            ->orderBy('id')
            ->chunkById(100, function ($articles) use (&$unpublished) {
                foreach ($articles as $article) {
                    if (!$this->isOffTopic($article->title)) {
                        continue;
                    }

                    $article->is_published = false;
                    $article->save();
                    $unpublished++;
                    $this->command?->line('Unpublished off-topic article #' . $article->id . ': ' . $article->title);
                }
            });

        $this->command?->info("BlogCleanupSeeder: {$unpublished} off-topic article(s) unpublished.");
    }

    private function isOffTopic(?string $title): bool
    {
        $title = mb_strtolower(trim((string) $title));
        if ($title === '') {
            return false;
        }

        foreach (self::OFF_TOPIC_TITLE_PATTERNS as $pattern) {
            if (str_contains($title, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
