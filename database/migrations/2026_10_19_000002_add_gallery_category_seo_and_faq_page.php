<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Editable SEO for hubs that had none:
 * - Gallery categories: a "SEO danh mục" section on each gallery page config, with a title and a
 *   description textbox per category (keys "seo-title-{gallery key}" / "seo-description-{gallery key}").
 * - FAQ hub: an app_page row "faq" per language (SEO card only, no sections) plus its admin menu entry.
 */
return new class extends Migration
{
    private const SECTION_NAME = 'SEO danh mục';
    private const TEXTBOX = 1;

    public function up(): void
    {
        $now = now();

        foreach (DB::table('app_page')->where('code', 'gallery')->get() as $page) {
            if (DB::table('app_page_section')->where('page_id', $page->id)->where('name', self::SECTION_NAME)->exists()) {
                continue;
            }
            $sectionId = DB::table('app_page_section')->insertGetId([
                'page_id' => $page->id,
                'name' => self::SECTION_NAME,
                'ord' => 2,
                'created_at' => $now,
                'created_by' => 1,
            ]);
            $categories = DB::table('app_page_config')
                ->where('page_id', $page->id)
                ->where('key', 'like', 'gallery-file%')
                ->where('key', 'not like', '%-vr-360')
                ->orderBy('ord')
                ->get();
            $ord = 1;
            foreach ($categories as $category) {
                foreach (['title' => 'SEO title', 'description' => 'SEO description'] as $field => $label) {
                    DB::table('app_page_config')->insert([
                        'page_id' => $page->id,
                        'section_id' => $sectionId,
                        'key' => "seo-{$field}-{$category->key}",
                        'label' => "{$label} – {$category->label}",
                        'type' => self::TEXTBOX,
                        'list_value' => '',
                        'value' => '',
                        'ord' => $ord++,
                    ]);
                }
            }
        }

        foreach (DB::table('ad_language')->get() as $language) {
            if (DB::table('app_page')->where('code', 'faq')->where('language_id', $language->id)->exists()) {
                continue;
            }
            DB::table('app_page')->insert([
                'language_id' => $language->id,
                'code' => 'faq',
                'title' => 'Câu hỏi thường gặp',
                'created_at' => $now,
                'created_by' => 1,
            ]);
        }

        if (!DB::table('ad_menu')->where('url', '/admincp/page-config/faq')->exists()) {
            $gallery = DB::table('ad_menu')->where('url', '/admincp/page-config/gallery')->first();
            if ($gallery) {
                DB::table('ad_menu')->insert([
                    'parent_id' => $gallery->parent_id,
                    'privilege_id' => $gallery->privilege_id,
                    'name' => 'Câu hỏi thường gặp',
                    'url' => '/admincp/page-config/faq',
                    'is_multi_language' => 1,
                    'icon' => 'fas fa-question-circle',
                    'ord' => 6,
                    'status' => 1,
                    'created_at' => $now,
                    'created_by' => 1,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('ad_menu')->where('url', '/admincp/page-config/faq')->delete();
        DB::table('app_page')->where('code', 'faq')->delete();

        $pageIds = DB::table('app_page')->where('code', 'gallery')->pluck('id');
        $sectionIds = DB::table('app_page_section')->whereIn('page_id', $pageIds)->where('name', self::SECTION_NAME)->pluck('id');
        DB::table('app_page_config')->whereIn('section_id', $sectionIds)->delete();
        DB::table('app_page_section')->whereIn('id', $sectionIds)->delete();
    }
};
