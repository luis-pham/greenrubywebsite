<?php

namespace Tests\Feature\Editorial;

use App\Services\Content\RevisionStore;
use App\Services\Content\Workflow;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AdUser;
use Modules\BackEnd\Services\AppPageConfigService;
use Modules\BackEnd\Services\AppPageService;

/**
 * Phase 2C safety fix 4: a PUBLISHED static page of a new language is edited through a pending revision (title/SEO
 * and configuration values), reviewed and applied by the workflow; the live page keeps its approved text meanwhile.
 * Unpublished ja pages and en/vi pages are edited directly, as before. Fixture: EN homepage #1 (banner_title).
 */
class PageRevisionsTest extends EditorialTestCase
{
    private object $page;
    private object $config;

    protected function setUp(): void
    {
        parent::setUp();
        (require database_path('migrations/2026_11_04_000001_create_content_revision_and_preview_tables.php'))->up();
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
        $this->page = DB::table('app_page')->where('language_id', 3)->where('code', 'homepage')->first();
        $this->config = DB::table('app_page_config')->where('page_id', $this->page->id)->where('key', 'banner_title')->first();
        $this->actingAs(AdUser::find(2), 'admin');
    }

    private function publish(): void
    {
        DB::table('app_page_config')->where('id', $this->config->id)->update(['value' => 'ハロン湾をクルーズ']);
        DB::table('translation_meta')->where('entity_type', 'page')->where('entity_id', $this->page->id)->update(['workflow' => 'approved']);
        $this->assertSame('published', Workflow::apply('page', (int) $this->page->id, 'publish'));
    }

    public function test_an_unpublished_ja_page_is_edited_directly(): void
    {
        AppPageService::update(['id' => $this->page->id, 'seo_title' => 'ホーム'], 3);
        AppPageConfigService::update(['id' => $this->config->id, 'value' => '湾を巡る']);
        $this->assertSame('ホーム', DB::table('app_page')->find($this->page->id)->seo_title);
        $this->assertSame('湾を巡る', DB::table('app_page_config')->find($this->config->id)->value);
        $this->assertSame(0, DB::table('content_revision')->count());
        $this->assertSame(2, (int) DB::table('translation_meta')->where('entity_type', 'page')->where('entity_id', $this->page->id)->value('translated_by'));
    }

    public function test_a_published_ja_page_edit_goes_into_a_revision_and_is_applied_on_approval(): void
    {
        $this->publish();
        AppPageService::update(['id' => $this->page->id, 'seo_title' => '新しいタイトル', 'seo_description' => '説明'], 3);
        AppPageConfigService::update(['id' => $this->config->id, 'value' => 'ランハ湾をクルーズ']);

        $this->assertSame('Home', DB::table('app_page')->find($this->page->id)->title);
        $this->assertNull(DB::table('app_page')->find($this->page->id)->seo_title, 'live SEO unchanged');
        $this->assertSame('ハロン湾をクルーズ', DB::table('app_page_config')->find($this->config->id)->value, 'live config unchanged');
        $revision = RevisionStore::open('page', (int) $this->page->id);
        $fields = json_decode($revision->fields, true);
        $this->assertSame(['新しいタイトル', '説明', 'ランハ湾をクルーズ', 2], [$fields['seo_title'], $fields['seo_description'],
            $fields['_children']['app_page_config'][$this->config->id]['value'], (int) $revision->created_by]);
        $this->assertSame(1, DB::table('content_revision')->count(), 'one revision carries the page and its configuration');

        $state = Workflow::state('page', (int) $this->page->id);
        $this->assertTrue($state['published']);
        $this->assertSame('draft', $state['step']);
        Workflow::apply('page', (int) $this->page->id, 'submit');
        Workflow::apply('page', (int) $this->page->id, 'approve_translation');
        $this->assertSame('revision_applied', Workflow::apply('page', (int) $this->page->id, 'approve_seo'));
        $this->assertSame('新しいタイトル', DB::table('app_page')->find($this->page->id)->seo_title);
        $this->assertSame('ランハ湾をクルーズ', DB::table('app_page_config')->find($this->config->id)->value);
        $this->assertSame(1, (int) DB::table('app_page')->find($this->page->id)->is_visible, 'still published');
        $this->assertNull(RevisionStore::open('page', (int) $this->page->id));
        $this->assertSame(['publish', 'revision_opened', 'submit', 'approve_translation', 'approve_seo'],
            DB::table('editorial_audit_log')->where('entity_type', 'page')->orderBy('id')->pluck('action')->all());
    }

    public function test_a_revision_can_be_discarded_and_unpublishing_is_not_intercepted(): void
    {
        $this->publish();
        AppPageConfigService::update(['id' => $this->config->id, 'value' => '別の文']);
        $this->assertSame('revision_discarded', Workflow::apply('page', (int) $this->page->id, 'discard_revision'));
        $this->assertSame('ハロン湾をクルーズ', DB::table('app_page_config')->find($this->config->id)->value);
        AppPageService::update(['id' => $this->page->id, 'is_visible' => 0], 3);
        $this->assertSame(0, (int) DB::table('app_page')->find($this->page->id)->is_visible);
    }

    public function test_en_pages_are_edited_directly_as_before(): void
    {
        AppPageService::update(['id' => 1, 'seo_title' => 'Home | Green Ruby'], 2);
        $enConfig = DB::table('app_page_config')->where('page_id', 1)->where('key', 'banner_title')->value('id');
        AppPageConfigService::update(['id' => $enConfig, 'value' => 'Sail Lan Ha']);
        $this->assertSame('Home | Green Ruby', DB::table('app_page')->find(1)->seo_title);
        $this->assertSame('Sail Lan Ha', DB::table('app_page_config')->find($enConfig)->value);
        $this->assertSame(0, DB::table('content_revision')->count());
    }

    public function test_the_page_panel_shows_the_pending_revision(): void
    {
        $this->publish();
        AppPageConfigService::update(['id' => $this->config->id, 'value' => '別の文']);
        $panel = \Modules\BackEnd\Helpers\TranslationPanel::for('page', DB::table('app_page')->find($this->page->id));
        $this->assertNotNull($panel['pending']);
        $this->assertContains('discard_revision', array_column($panel['actions'], 'key'));
    }
}
