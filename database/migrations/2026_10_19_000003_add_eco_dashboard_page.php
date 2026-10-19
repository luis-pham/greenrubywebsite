<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The eco dashboard is a Next.js-only page. An app_page row per language gives it the SEO card in
 * "Quản lý cấu hình trang" (no sections); GET /pages/eco-dashboard returns null SEO when it is empty.
 */
return new class extends Migration
{
    private array $seo = [
        'en' => ['Eco Impact Dashboard · Ha Long Cruise | Green Ruby Cruises', "Explore Green Ruby Cruises' environmental goals, projected carbon savings and transparent journey toward measured lower-impact cruising."],
        'vi' => ['Báo Cáo Tác Động Môi Trường | Green Ruby Cruises', 'Mục tiêu môi trường, mức giảm carbon dự kiến và hành trình minh bạch của Green Ruby Cruises hướng tới du thuyền ít tác động, được đo lường thực tế.'],
    ];

    public function up(): void
    {
        foreach (DB::table('ad_language')->get() as $language) {
            if (DB::table('app_page')->where('code', 'eco-dashboard')->where('language_id', $language->id)->exists()) {
                continue;
            }
            [$title, $description] = $this->seo[$language->code] ?? [null, null];
            DB::table('app_page')->insert([
                'language_id' => $language->id,
                'code' => 'eco-dashboard',
                'title' => 'Báo cáo sinh thái',
                'seo_title' => $title,
                'seo_description' => $description,
                'created_at' => now(),
                'created_by' => 1,
            ]);
        }

        if (!DB::table('ad_menu')->where('url', '/admincp/page-config/eco-dashboard')->exists()) {
            $gallery = DB::table('ad_menu')->where('url', '/admincp/page-config/gallery')->first();
            if ($gallery) {
                DB::table('ad_menu')->insert([
                    'parent_id' => $gallery->parent_id,
                    'privilege_id' => $gallery->privilege_id,
                    'name' => 'Báo cáo sinh thái',
                    'url' => '/admincp/page-config/eco-dashboard',
                    'is_multi_language' => 1,
                    'icon' => 'fas fa-leaf',
                    'ord' => 7,
                    'status' => 1,
                    'created_at' => now(),
                    'created_by' => 1,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('ad_menu')->where('url', '/admincp/page-config/eco-dashboard')->delete();
        DB::table('app_page')->where('code', 'eco-dashboard')->delete();
    }
};
