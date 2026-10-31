<?php

namespace Tests\Feature\Editorial;

use App\Services\Content\SlugService;
use App\Services\Content\TranslationFactory;
use App\Services\Content\Workflow;
use App\Services\Preview\Preview;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\BackEnd\Entities\AppCruise;

/** Phase 1E-d: workflow, revisions of published rows, authenticated preview (CMS side). */
class WorkflowPreviewTest extends EditorialTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        (require database_path('migrations/2026_11_04_000001_create_content_revision_and_preview_tables.php'))->up();
        config([
            'services.content_api.key' => 'content-test-key',
            'i18n.preview.key' => 'preview-test-key',
            'services.frontend_revalidate.url' => 'http://web.test/api/revalidate',
            'services.frontend_revalidate.secret' => 'rv',
        ]);
        Http::fake(['web.test/*' => Http::response(['ok' => true])]);
        $this->seedTeam();
    }

    private function jaCruise(bool $slug = true): int
    {
        $id = TranslationFactory::create('cruise', 3, $this->ja(), 1)['id'];
        if ($slug) {
            SlugService::change('cruise', $id, 'green-ruby-1', 'Set the Japanese URL', 1);
        }

        return $id;
    }

    /** Submitted by the translator (2), approved by a different person (reviewer 3): no self-approval. */
    private function approve(string $type, int $id): void
    {
        Workflow::apply($type, $id, 'submit', 2);
        Workflow::apply($type, $id, 'approve_translation', 3);
        Workflow::apply($type, $id, 'approve_seo', 3);
    }

    public function test_review_steps_then_publish_with_one_expiring_revalidation(): void
    {
        $id = $this->jaCruise(false);
        $this->assertSame(['submit'], array_column(Workflow::actionsFor('cruise', $id), 'key'));
        $this->assertThrows(fn () => Workflow::apply('cruise', $id, 'publish', 1), \RuntimeException::class);
        Workflow::apply('cruise', $id, 'submit', 1);
        Workflow::apply('cruise', $id, 'reject', 1);
        $this->assertSame('draft', DB::table('translation_meta')->where('entity_id', $id)->where('entity_type', 'cruise')->value('workflow'));
        $this->approve('cruise', $id);
        $meta = DB::table('translation_meta')->where('entity_type', 'cruise')->where('entity_id', $id)->first();
        $this->assertSame(['approved', 3, 3], [$meta->workflow, (int) $meta->reviewed_by, (int) $meta->seo_reviewed_by]);
        // Approved but without a slug: the publish guard refuses (a Japanese name derives no URL).
        $this->assertThrows(fn () => Workflow::apply('cruise', $id, 'publish', 1), \RuntimeException::class);
        SlugService::change('cruise', $id, 'green-ruby-1', 'Set the Japanese URL', 1);
        Http::fake(['web.test/*' => Http::response(['ok' => true])]);
        $this->assertSame('published', Workflow::apply('cruise', $id, 'publish', 1));
        $this->assertSame('published', DB::table('app_cruise')->where('id', $id)->value('publish_state'));
        Http::assertSent(fn ($r) => $r->url() === 'http://web.test/api/revalidate' && $r['tags'] === ['content'] && $r['expire'] === true);
        $this->assertSame('unpublished', Workflow::apply('cruise', $id, 'unpublish', 1));
    }

    public function test_faq_needs_the_legal_step(): void
    {
        $group = TranslationFactory::create('faq_group', 57, $this->ja(), 1)['id'];
        $faq = TranslationFactory::create('faq', 1, $this->ja(), 1)['id'];
        $this->approve('faq', $faq);
        $this->assertSame('legal_review', DB::table('translation_meta')->where('entity_type', 'faq')->where('entity_id', $faq)->value('workflow'));
        Workflow::apply('faq', $faq, 'approve_legal', 4);
        $this->assertNotNull(DB::table('translation_meta')->where('entity_type', 'faq')->where('entity_id', $faq)->value('legal_approved_at'));
        $this->assertGreaterThan(0, $group);
    }

    public function test_edits_to_a_published_translation_wait_in_a_revision(): void
    {
        $id = $this->jaCruise();
        $this->approve('cruise', $id);
        Workflow::apply('cruise', $id, 'publish', 1);

        $model = AppCruise::withoutGlobalScopes()->find($id);
        $model->name = 'グリーンルビー1（改訂）';
        $model->summary = '新しい説明';
        $model->save();
        $this->assertSame('Green Ruby 1', DB::table('app_cruise')->where('id', $id)->value('name'), 'live text unchanged');
        $revision = Workflow::pendingRevision('cruise', $id);
        $this->assertSame(['name' => 'グリーンルビー1（改訂）', 'summary' => '新しい説明'], json_decode($revision->fields, true));
        $this->assertSame(['submit', 'discard_revision'], array_column(Workflow::actionsFor('cruise', $id), 'key'), 'no unpublish while a revision is open');

        $this->approve('cruise', $id);
        $this->assertSame('グリーンルビー1（改訂）', DB::table('app_cruise')->where('id', $id)->value('name'), 'applied on approval');
        $this->assertNull(Workflow::pendingRevision('cruise', $id));
        $this->assertSame('published', DB::table('app_cruise')->where('id', $id)->value('publish_state'));

        // en/vi rows are edited directly, as before.
        $en = AppCruise::withoutGlobalScopes()->find(3);
        $en->summary = 'EN edited';
        $en->save();
        $this->assertSame('EN edited', DB::table('app_cruise')->where('id', 3)->value('summary'));
        $this->assertSame(0, DB::table('content_revision')->where('entity_id', 3)->count());
        $this->assertThrows(fn () => Workflow::apply('cruise', 3, 'submit', 1), \RuntimeException::class);
    }

    public function test_preview_tokens_are_single_use_short_lived_and_scoped(): void
    {
        $id = $this->jaCruise();
        $this->assertThrows(fn () => Preview::issue(1, 'cruise', TranslationFactory::create('itinerary', 3, $this->ja(), 1)['id'] + 999), \RuntimeException::class);
        $token = Preview::issue(1, 'cruise', $id);
        $this->assertSame(0, DB::table('content_preview_token')->where('token_hash', $token)->count(), 'only the hash is stored');
        $verify = fn ($t, $key = 'preview-test-key') => $this->postJson('/api/v1/content/preview/verify', ['token' => $t], ['X-Content-Key' => 'content-test-key', 'X-Preview-Key' => $key]);

        $verify($token, 'wrong')->assertStatus(403);
        $ok = $verify($token);
        $ok->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->assertSame(['cruise', $id, 'ja', '/ja/cruise/green-ruby-1-' . $id . '.html'], [$ok['type'], $ok['id'], $ok['lang'], $ok['path']]);
        $verify($token)->assertStatus(403)->assertJson(['error' => 'preview_token_used']);
        $verify(str_repeat('a', 64))->assertStatus(403)->assertJson(['error' => 'preview_token_invalid']);

        $late = Preview::issue(1, 'cruise', $id);
        DB::table('content_preview_token')->where('token_hash', hash('sha256', $late))->update(['expires_at' => now()->subMinute()]);
        $verify($late)->assertStatus(403)->assertJson(['error' => 'preview_token_expired']);
    }

    public function test_content_api_previews_only_the_scoped_entity_and_never_anonymously(): void
    {
        // Destinations: the cruise detail query uses MySQL date functions (covered by the MySQL integration run).
        DB::table('app_page')->insert(['language_id' => 3, 'code' => 'destinations', 'title' => 'x', 'is_visible' => 1, 'created_at' => now(), 'created_by' => 1]);
        $a = TranslationFactory::create('destination', 1, $this->ja(), 1)['id'];
        DB::table('app_destination')->insert(['id' => 30, 'language_id' => 2, 'slug' => 'luon-cave', 'name' => 'Luon Cave', 'bay' => 'ha-long', 'category' => 'cave', 'status' => 'published']);
        DB::table('app_destination')->insert(['id' => 31, 'language_id' => 1, 'slug' => 'hang-luon', 'name' => 'Hang Luồn', 'bay' => 'ha-long', 'category' => 'cave', 'status' => 'published']);
        \App\Services\Content\ContentTranslations::link('destination', [30, 31]);
        $b = TranslationFactory::create('destination', 30, $this->ja(), 1)['id'];
        SlugService::change('destination', $a, 'sung-sot-cave', 'Set the Japanese URL', 1);
        SlugService::change('destination', $b, 'luon-cave', 'Set the Japanese URL', 1);
        DB::table('app_destination')->where('id', $a)->update(['name' => 'JA-DRAFT-A']);
        DB::table('app_destination')->where('id', $b)->update(['name' => 'JA-DRAFT-B']);

        $session = Preview::verify(Preview::issue(1, 'destination', $a))['session'];
        $content = ['X-Content-Key' => 'content-test-key'];
        $preview = $content + ['X-Preview-Key' => 'preview-test-key', 'X-Preview-Session' => $session];

        $anon = $this->getJson('/api/v1/content/destinations/sung-sot-cave?lang=ja', $content);
        $anon->assertNotFound();
        $this->assertStringNotContainsString('JA-DRAFT', $anon->getContent());
        $ok = $this->getJson('/api/v1/content/destinations/sung-sot-cave?lang=ja', $preview)->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->assertStringContainsString('JA-DRAFT-A', $ok->getContent());
        $this->assertStringNotContainsString('JA-DRAFT-B', $ok->getContent(), 'other drafts stay hidden inside a preview');
        $this->assertStringContainsString('no-store', (string) $ok->headers->get('Cache-Control'));
        // Another draft in the same language: not in the session's scope.
        $this->getJson('/api/v1/content/destinations/luon-cave?lang=ja', $preview)->assertNotFound();
        // A forged session or key is refused, never answered publicly; so is an expired session.
        $this->getJson('/api/v1/content/destinations/sung-sot-cave?lang=ja', ['X-Preview-Session' => str_repeat('b', 64), 'X-Preview-Key' => 'preview-test-key'] + $content)->assertStatus(403);
        $this->getJson('/api/v1/content/destinations/sung-sot-cave?lang=ja', ['X-Preview-Session' => $session, 'X-Preview-Key' => 'nope'] + $content)->assertStatus(403);
        // The next anonymous request is public again (no preview state leaks between requests).
        $this->getJson('/api/v1/content/destinations/sung-sot-cave?lang=ja', $content)->assertNotFound();
        $this->getJson('/api/v1/content/destinations/sung-sot-cave?lang=en', $content)->assertOk();
        DB::table('content_preview_session')->update(['expires_at' => now()->subSecond()]);
        $this->getJson('/api/v1/content/destinations/sung-sot-cave?lang=ja', $preview)->assertStatus(403);
    }

    public function test_preview_shows_the_pending_revision_of_a_published_row(): void
    {
        DB::table('app_page')->insert(['language_id' => 3, 'code' => 'destinations', 'title' => 'x', 'is_visible' => 1, 'created_at' => now(), 'created_by' => 1]);
        $id = TranslationFactory::create('destination', 1, $this->ja(), 1)['id'];
        SlugService::change('destination', $id, 'sung-sot-cave', 'Set the Japanese URL', 1);
        $this->approve('destination', $id);
        Workflow::apply('destination', $id, 'publish', 1);
        $model = \Modules\BackEnd\Entities\AppDestination::find($id);
        $model->name = 'REVISION-TEXT';
        $model->save();
        $this->assertSame('Sung Sot Cave', DB::table('app_destination')->where('id', $id)->value('name'), 'live text unchanged');
        $session = Preview::verify(Preview::issue(1, 'destination', $id))['session'];
        $res = $this->getJson('/api/v1/content/destinations/sung-sot-cave?lang=ja', ['X-Content-Key' => 'content-test-key', 'X-Preview-Key' => 'preview-test-key', 'X-Preview-Session' => $session]);
        $res->assertOk();
        $this->assertStringContainsString('REVISION-TEXT', $res->getContent());
        $this->assertSame('Sung Sot Cave', DB::table('app_destination')->where('id', $id)->value('name'), 'preview never writes');
    }

    public function test_admin_preview_posts_the_token_in_a_body_never_a_url(): void
    {
        config(['i18n.preview.entry_url' => 'https://site.test/_draft/enter']);
        $id = $this->jaCruise();
        $res = $this->post('/admincp/preview', ['type' => 'cruise', 'id' => $id])->assertOk();
        $this->assertStringContainsString('method="post" action="https://site.test/_draft/enter"', $res->getContent());
        $this->assertStringContainsString('no-store', (string) $res->headers->get('Cache-Control'));
        $this->assertSame('no-referrer', $res->headers->get('Referrer-Policy'));
        $this->assertSame(1, DB::table('content_preview_token')->count());
        $this->assertStringNotContainsString(DB::table('content_preview_token')->value('token_hash'), $res->getContent());
        // An entity without a slug cannot be previewed (no URL yet).
        $this->post('/admincp/preview', ['type' => 'cruise', 'id' => TranslationFactory::create('itinerary', 3, $this->ja(), 1)['id']])->assertRedirect();
    }
}
