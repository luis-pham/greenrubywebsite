<?php

namespace Tests\Feature\Editorial;

use App\Services\Ai\ArticleWriter;
use App\Services\Content\TranslationFactory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Phase 1E-e: the editor API works from the language registry and refuses unsupported languages explicitly. */
class EditorApiTest extends EditorialTestCase
{
    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('editor_tokens', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('user_id');
            $t->char('token_hash', 64);
            $t->dateTime('expires_at');
            $t->dateTime('last_used_at')->nullable();
            $t->dateTime('created_at')->nullable();
        });
        Schema::create('app_article_plan', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('article_id')->nullable();
            $t->string('code')->nullable();
            $t->string('lang', 35)->default('en');
        });
        $token = str_repeat('t', 48);
        DB::table('editor_tokens')->insert(['user_id' => 1, 'token_hash' => hash('sha256', $token), 'expires_at' => now()->addDay(), 'last_used_at' => now()]);
        $this->headers = ['Authorization' => "Bearer {$token}", 'X-Editor' => '1'];
    }

    public function test_locales_come_from_the_registry_with_per_language_limits(): void
    {
        $locales = collect($this->getJson('/api/v1/editor/locales', $this->headers)->assertOk()->json('locales'))->keyBy('code');
        $this->assertSame(['en', 'vi', 'ja'], $locales->keys()->all(), 'ko is disabled');
        $this->assertSame([true, 'words', 60], [$locales['en']['aiDrafting'], $locales['en']['metric'], $locales['en']['titleMax']]);
        $this->assertSame(['editing', false, 'characters', 32], [$locales['ja']['status'], $locales['ja']['aiDrafting'], $locales['ja']['metric'], $locales['ja']['titleMax']]);
    }

    public function test_unknown_or_disabled_languages_are_refused_not_turned_into_english(): void
    {
        $this->getJson('/api/v1/editor/articles?lang=fr', $this->headers)->assertStatus(422);
        $this->postJson('/api/v1/editor/articles', ['lang' => 'ko', 'title' => 'x'], $this->headers)->assertStatus(422);
        $this->postJson('/api/v1/editor/articles', ['lang' => 'xx'], $this->headers)->assertStatus(422);
        $this->assertSame(2, DB::table('app_article')->count(), 'nothing created');
        $this->getJson('/api/v1/editor/articles?lang=en', $this->headers)->assertOk()->assertJsonCount(1, 'items');
    }

    public function test_japanese_draft_and_translation_from_english(): void
    {
        $created = $this->postJson('/api/v1/editor/articles', ['lang' => 'ja', 'title' => 'ハロン湾の旅'], $this->headers)->assertCreated();
        $this->assertSame(['ja', 'draft'], [$created->json('article.lang'), $created->json('article.status')]);
        $this->assertSame(0, (int) DB::table('app_article')->where('id', $created->json('article.id'))->value('is_published'));

        // Translate EN #100 into ja: refused until its category has a ja translation, then a text draft.
        $this->postJson('/api/v1/editor/articles/100/translate', ['target' => 'ja'], $this->headers)->assertStatus(422);
        TranslationFactory::create('article_category', 10, $this->ja(), 1);
        $res = $this->postJson('/api/v1/editor/articles/100/translate', ['target' => 'ja'], $this->headers)->assertCreated();
        $this->assertSame(['ja', 'draft', 'current'], [$res->json('article.lang'), $res->json('article.workflow'), $res->json('article.freshness')]);
        $this->assertSame(['en', 'Best time to visit', 'EN'], [$res->json('article.source.lang'), $res->json('article.source.title'), $res->json('article.source.content')]);
        $this->assertSame(100, $res->json('article.translationId'), 'a new language links back to its source');
        // Again: the existing draft, not a second one.
        $this->postJson('/api/v1/editor/articles/100/translate', ['target' => 'ja'], $this->headers)->assertOk();
        $this->assertSame(1, DB::table('app_article')->where('language_id', 3)->where('category_id', '>', 0)->count());
        // en↔vi without a target: unchanged behaviour (the linked VI article).
        $this->assertSame(200, $this->postJson('/api/v1/editor/articles/100/translate', [], $this->headers)->assertOk()->json('article.id'));
        $this->assertSame(['en', 'vi', 'ja'], array_keys($this->getJson('/api/v1/editor/categories', $this->headers)->json()));
    }

    public function test_ai_drafting_needs_the_languages_style_guide(): void
    {
        $this->assertSame('en', ArticleWriter::draftingLanguage('en'));
        $this->assertSame('vi', ArticleWriter::draftingLanguage('VI'));
        $this->assertSame('en', ArticleWriter::draftingLanguage(''));
        try {
            ArticleWriter::draftingLanguage('ja');
            $this->fail('ja has no style guide yet');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('style-guide-ja.md', $e->getMessage());
        }
        $this->assertThrows(fn () => ArticleWriter::draftingLanguage('fr'), \RuntimeException::class);
    }

    /** 1E hardening: a new language's articles need the translation privileges in the writing app too; en/vi unchanged. */
    public function test_translation_privileges_apply_to_new_languages_only(): void
    {
        $this->seedTeam();
        $as = function (int $user): array {
            $token = str_repeat((string) $user, 48);
            DB::table('editor_tokens')->insert(['user_id' => $user, 'token_hash' => hash('sha256', $token), 'expires_at' => now()->addDay(), 'last_used_at' => now()]);

            return ['Authorization' => "Bearer {$token}", 'X-Editor' => '1'];
        };
        $legacy = $as(6);
        $this->postJson('/api/v1/editor/articles', ['lang' => 'en', 'title' => 'EN as before'], $legacy)->assertCreated();
        $this->putJson('/api/v1/editor/articles/100', ['title' => 'Best time to visit'], $legacy)->assertOk();
        $this->postJson('/api/v1/editor/articles', ['lang' => 'ja', 'title' => '許可なし'], $legacy)->assertForbidden();

        $translator = $as(2);
        $id = $this->postJson('/api/v1/editor/articles', ['lang' => 'ja', 'title' => '下書き'], $translator)->assertCreated()->json('article.id');
        $this->putJson("/api/v1/editor/articles/{$id}", ['title' => '下書き2'], $translator)->assertOk();
        $this->putJson("/api/v1/editor/articles/{$id}", ['title' => '下書き3'], $legacy)->assertForbidden();
        $this->putJson("/api/v1/editor/articles/{$id}", ['isPublished' => true], $translator)->assertForbidden();
        $this->assertSame(0, (int) DB::table('app_article')->where('id', $id)->value('is_published'));
        $this->deleteJson("/api/v1/editor/articles/{$id}", [], $legacy)->assertForbidden();
        $this->assertSame('下書き2', DB::table('app_article')->where('id', $id)->value('title'));
    }
}
