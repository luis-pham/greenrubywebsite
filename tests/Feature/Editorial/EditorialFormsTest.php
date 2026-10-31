<?php

namespace Tests\Feature\Editorial;

use App\Services\Content\TranslationFactory;
use App\Services\Publication\PagePublication;
use App\Services\Locale\LocaleRegistry;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppPage;

/** Phase 1E-c: shared forms (translation panel), slug administration, page-level publication. */
class EditorialFormsTest extends EditorialTestCase
{
    private function jaCruise(): int
    {
        return TranslationFactory::create('cruise', 3, $this->ja(), 1)['id'];
    }

    public function test_ja_cruise_form_shows_source_text_inherited_business_values_and_versions(): void
    {
        $id = $this->jaCruise();
        $html = $this->get("/admincp/ja/cruise/edit/{$id}")->assertOk()->getContent();
        $this->assertStringContainsString('id="translation-panel"', $html);
        $this->assertStringContainsString('Bản gốc EN #3', $html);
        $this->assertStringContainsString('Theo bản EN (chỉ đọc)', $html);
        $this->assertStringContainsString('<code>capacity</code> = 92', $html);
        $this->assertStringContainsString('— chưa đặt —', $html, 'no slug until an editor sets one');
        $this->assertStringContainsString('name="seo_title"', $html, 'cruise SEO fields (1E-c)');

        $en = $this->get('/admincp/cruise/edit/3')->assertOk()->getContent();
        $this->assertStringContainsString('id="translation-panel"', $en);
        $this->assertStringNotContainsString('Theo bản EN (chỉ đọc)', $en, 'EN owns its business data');
        $this->assertStringContainsString('日本語 #' . $id, $en, 'link to the ja version');
    }

    public function test_slug_change_is_explicit_validated_and_keeps_history(): void
    {
        $id = $this->jaCruise();
        $this->post('/admincp/slug', ['type' => 'cruise', 'id' => $id, 'slug' => 'グリーン', 'reason' => 'Set the Japanese URL'])->assertSessionHasErrors();
        $this->post('/admincp/slug', ['type' => 'cruise', 'id' => $id, 'slug' => 'green-ruby-1', 'reason' => 'short'])->assertSessionHasErrors();
        $this->post('/admincp/slug', ['type' => 'cruise', 'id' => $id, 'slug' => 'green-ruby-1', 'reason' => 'Set the Japanese URL'])->assertSessionHasNoErrors();
        $this->assertSame('green-ruby-1', DB::table('app_cruise')->where('id', $id)->value('slug'));
        $this->assertSame(0, DB::table('app_slug_history')->count(), 'first slug: nothing to redirect');

        // Renaming never changes the URL.
        DB::table('app_cruise')->where('id', $id)->update(['name' => 'グリーンルビー1']);
        $this->assertSame('green-ruby-1', DB::table('app_cruise')->where('id', $id)->value('slug'));

        $this->post('/admincp/slug', ['type' => 'cruise', 'id' => $id, 'slug' => 'green-ruby-one', 'reason' => 'Clearer Japanese URL'])->assertSessionHasNoErrors();
        $history = DB::table('app_slug_history')->first();
        $this->assertSame(['green-ruby-1', 'green-ruby-one', 3], [$history->old_slug, $history->new_slug, (int) $history->language_id]);
        $this->assertStringContainsString('green-ruby-1', $this->get("/admincp/ja/cruise/edit/{$id}")->getContent(), 'old slug listed as redirecting');
    }

    public function test_page_level_publication_needs_approval_for_a_new_language(): void
    {
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
        $ja = $this->ja();
        DB::table('app_page')->insert(['language_id' => 2, 'code' => 'service', 'title' => 'Services', 'is_visible' => 1, 'created_at' => now(), 'created_by' => 1]);
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
        $this->assertFalse(PagePublication::visible('service', $ja), 'seeded hidden');
        $this->assertTrue(PagePublication::visible('service', LocaleRegistry::byCode('en')), 'en/vi unchanged');
        $this->assertFalse(PagePublication::visible('contact', $ja), 'no page row: not public');

        $page = AppPage::where('language_id', 3)->where('code', 'service')->first();
        $page->is_visible = 1;
        $this->assertThrows(fn () => $page->save(), \RuntimeException::class);
        DB::table('translation_meta')->where('entity_type', 'page')->where('entity_id', $page->id)->update(['workflow' => 'approved']);
        $page->save();
        $this->assertTrue(PagePublication::visible('service', $ja));
    }

    public function test_itinerary_form_has_seo_fields(): void
    {
        $this->artisan('i18n:seed-locale', ['code' => 'ja']);
        $html = $this->get('/admincp/itinerary/edit/3')->assertOk()->getContent();
        $this->assertStringContainsString('name="seo_title"', $html);
        $this->assertStringContainsString('name="seo_description"', $html);
    }

    public function test_page_configuration_of_a_new_language_has_publication_controls(): void
    {
        DB::table('app_page')->insert(['language_id' => 2, 'code' => 'service', 'title' => 'Services', 'is_visible' => 1, 'created_at' => now(), 'created_by' => 1]);
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
        $id = (int) DB::table('app_page')->where('language_id', 3)->where('code', 'service')->value('id');
        $html = $this->get('/admincp/ja/page-config/service')->assertOk()->getContent();
        $this->assertStringContainsString('id="translation-panel"', $html);
        $this->assertStringContainsString('Chưa xuất bản', $html);
        $this->assertStringContainsString('Gửi duyệt bản dịch', $html);
        $this->assertStringContainsString('Xem trước', $html, 'pages are previewable (1E hardening)');
        $this->assertStringNotContainsString('id="translation-panel"', $this->get('/admincp/page-config/homepage')->getContent(), 'en/vi pages: no panel');
        foreach (['submit', 'approve_translation', 'approve_seo', 'publish'] as $action) {
            $this->post('/admincp/workflow', ['type' => 'page', 'id' => $id, 'action' => $action])->assertSessionHasNoErrors();
        }
        $this->assertSame(1, (int) DB::table('app_page')->where('id', $id)->value('is_visible'));
    }
}
