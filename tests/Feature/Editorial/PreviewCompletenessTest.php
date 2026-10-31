<?php

namespace Tests\Feature\Editorial;

use App\Services\Content\TranslationFactory;
use App\Services\Preview\Preview;
use Illuminate\Support\Facades\DB;

/** 1E hardening: preview of FAQ items and pages (static pages, hubs) with the same token and session rules. */
class PreviewCompletenessTest extends EditorialTestCase
{
    private array $content = ['X-Content-Key' => 'content-test-key'];

    protected function setUp(): void
    {
        parent::setUp();
        (require database_path('migrations/2026_11_04_000001_create_content_revision_and_preview_tables.php'))->up();
        config(['services.content_api.key' => 'content-test-key', 'i18n.preview.key' => 'preview-test-key']);
        DB::table('app_page')->insert(['language_id' => 2, 'code' => 'service', 'title' => 'Services', 'is_visible' => 1, 'created_at' => now(), 'created_by' => 1]);
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
    }

    private function previewHeaders(string $type, int $id): array
    {
        return $this->content + ['X-Preview-Key' => 'preview-test-key', 'X-Preview-Session' => Preview::verify(Preview::issue(1, $type, $id))['session']];
    }

    public function test_faq_item_preview_shows_only_that_draft(): void
    {
        $group = TranslationFactory::create('faq_group', 57, $this->ja(), 1)['id'];
        DB::table('app_group')->where('id', $group)->update(['slug' => 'bookings', 'publish_state' => 'published']);
        $a = TranslationFactory::create('faq', 1, $this->ja(), 1)['id'];
        DB::table('app_faq')->insert(['language_id' => 2, 'group_id' => 57, 'question' => 'Second?', 'answer' => 'B', 'ord' => 2, 'publish_state' => 'published', 'created_at' => now(), 'created_by' => 1]);
        $b = TranslationFactory::create('faq', (int) DB::table('app_faq')->where('question', 'Second?')->value('id'), $this->ja(), 1)['id'];
        DB::table('app_faq')->where('id', $a)->update(['question' => 'JA-DRAFT-A']);
        DB::table('app_faq')->where('id', $b)->update(['question' => 'JA-DRAFT-B']);

        $this->assertSame('/ja/faq/bookings', Preview::verify(Preview::issue(1, 'faq', $a))['path']);
        $this->getJson('/api/v1/content/faqs?lang=ja', $this->content)->assertNotFound();
        $res = $this->getJson('/api/v1/content/faqs?lang=ja', $this->previewHeaders('faq', $a))->assertOk();
        $this->assertStringContainsString('JA-DRAFT-A', $res->getContent());
        $this->assertStringNotContainsString('JA-DRAFT-B', $res->getContent(), 'another draft FAQ stays hidden');
        $this->assertStringContainsString('no-store', (string) $res->headers->get('Cache-Control'));
    }

    public function test_static_page_and_hub_preview(): void
    {
        $service = (int) DB::table('app_page')->where('language_id', 3)->where('code', 'service')->value('id');
        $home = (int) DB::table('app_page')->where('language_id', 3)->where('code', 'homepage')->value('id');
        $this->assertSame('/ja/services', Preview::verify(Preview::issue(1, 'page', $service))['path']);
        $this->assertSame('/ja', Preview::verify(Preview::issue(1, 'page', $home))['path']);
        $this->getJson('/api/v1/content/pages/service?lang=ja', $this->content)->assertNotFound();
        $this->getJson('/api/v1/content/pages/service?lang=ja', $this->previewHeaders('page', $service))->assertOk()->assertJson(['code' => 'service']);
        // A session for the services page does not open another hidden page.
        $this->getJson('/api/v1/content/pages/gallery?lang=ja', $this->previewHeaders('page', $service))->assertNotFound();
        $this->getJson('/api/v1/content/pages/service?lang=ja', $this->content)->assertNotFound();
        // A page with no website section is refused.
        DB::table('app_page')->insert(['language_id' => 3, 'code' => 'legal', 'title' => 'x', 'is_visible' => 0, 'created_at' => now(), 'created_by' => 1]);
        $this->assertThrows(fn () => Preview::issue(1, 'page', (int) DB::table('app_page')->where('language_id', 3)->where('code', 'legal')->value('id')), \RuntimeException::class);
    }
}
