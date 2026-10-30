<?php

namespace App\Console\Commands;

use App\Services\Content\ContentTranslations;
use App\Support\FrontendRevalidator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Services\AppCategoryService;

/**
 * Creates the article categories behind /news (EN "news") and /vi/tin-tuc (VI "tin-tuc") and links them
 * as translations. Existing categories are left as edited in the admin.
 *
 *   news:setup            create what is missing
 *   news:setup --dry-run  report only
 */
class NewsSetupCommand extends Command
{
    protected $signature = 'news:setup {--dry-run}';

    protected $description = 'Create the News / Tin tức article categories';

    private const CATEGORIES = [
        'en' => [
            'slug' => 'news',
            'name' => 'News',
            'description' => 'Company news from Green Ruby Cruises: the trade shows and industry events we attend, partnerships and milestones on the way to our launch.',
            'seo_title' => 'Green Ruby Cruises News: Events, Trade Shows & Updates',
            'seo_description' => 'The latest from Green Ruby Cruises: trade shows and industry events we attend, partnerships and milestones for our Ha Long Bay and Lan Ha Bay cruises.',
        ],
        'vi' => [
            'slug' => 'tin-tuc',
            'name' => 'Tin tức',
            'description' => 'Tin tức từ Green Ruby Cruises: các hội chợ, hội thảo du lịch chúng tôi tham dự, hợp tác và những cột mốc trên chặng đường ra mắt.',
            'seo_title' => 'Tin tức Green Ruby Cruises: Sự kiện, hội chợ & cập nhật',
            'seo_description' => 'Cập nhật mới nhất từ Green Ruby Cruises: hội chợ, hội thảo du lịch chúng tôi tham dự, hợp tác và các cột mốc của du thuyền trên vịnh Hạ Long và vịnh Lan Hạ.',
        ],
    ];

    public function handle(): int
    {
        // BaseModel only stamps created_at/created_by for a signed-in user; act as the first admin (as destinations:import).
        Auth::onceUsingId(1);
        $type = config('backend.categoryType.article');
        $languages = DB::table('ad_language')->pluck('id', 'code');
        $ids = [];
        foreach (self::CATEGORIES as $code => $data) {
            $languageId = (int) $languages[$code];
            $existing = AppCategoryService::getBySlug($data['slug'], $type, $languageId);
            if ($existing) {
                $this->line("$code: kept #{$existing->id} {$existing->name}");
                $ids[] = (int) $existing->id;
                continue;
            }
            if ($this->option('dry-run')) {
                $this->line("$code: would create {$data['name']} ({$data['slug']})");
                continue;
            }
            $ids[] = (int) AppCategoryService::create($data, $type, $languageId);
            $this->info("$code: created #" . end($ids) . " {$data['name']}");
        }

        if (count($ids) === 2 && !$this->option('dry-run')) {
            ContentTranslations::link('article_category', $ids);
            FrontendRevalidator::send(['articles', 'content']);
            $this->info('Linked as translations, frontend revalidated.');
        }

        return self::SUCCESS;
    }
}
