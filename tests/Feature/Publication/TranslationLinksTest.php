<?php

namespace Tests\Feature\Publication;

use App\Services\Content\ContentTranslations;
use App\Services\Content\TranslationResolver;
use App\Services\Locale\FrontendManifest;
use App\Services\Locale\IntlSitemap;
use App\Services\Locale\LocaleRegistry;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Modules\BackEnd\Entities\AppCruise;

/**
 * Safe N-language linking and the hreflang resolver (phase 1C-4, 14 §5, 12 §5.3/§6; greenruby-web
 * docs/i18n-audit/phase-1/1c-4-report.md).
 */
class TranslationLinksTest extends I18nContentTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('content_translations', function (Blueprint $t) {
            $t->increments('id');
            $t->string('entity_type', 32);
            $t->unsignedInteger('entity_id');
            $t->char('group_key', 36);
            $t->unsignedInteger('language_id');
            $t->timestamps();
            $t->unique(['entity_type', 'entity_id']);
            $t->unique(['entity_type', 'group_key', 'language_id']);
        });
        // vi 1 / en 3 / ja 1003 cruises; EN 3 ↔ VI 1 linked.
        DB::table('app_cruise')->insert([
            ['id' => 1, 'language_id' => 1, 'name' => 'Green Ruby 1', 'slug' => 'green-ruby-1', 'publish_state' => 'published'],
            ['id' => 2, 'language_id' => 1, 'name' => 'Green Ruby 2', 'slug' => 'green-ruby-2', 'publish_state' => 'published'],
            ['id' => 1003, 'language_id' => 3, 'name' => 'グリーンルビー1', 'slug' => 'green-ruby-1', 'publish_state' => 'published'],
        ]);
        DB::table('ad_language')->where('code', 'ja')->update(['url_prefix' => 'ja']);
        LocaleRegistry::flush();
        ContentTranslations::flush();
        ContentTranslations::link('cruise', [3, 1]);
    }

    private function group(int $id): ?string
    {
        return DB::table('content_translations')->where('entity_type', 'cruise')->where('entity_id', $id)->value('group_key');
    }

    private function makeJaPublic(?array $sections = null): void
    {
        DB::table('ad_language')->where('code', 'ja')->update(['site_status' => 'public']);
        LocaleRegistry::flush();
        config([
            'i18n.allow_public_new_locales' => true,
            'i18n.frontend_manifest.urls' => ['http://web:3000/api/i18n/manifest'],
            'i18n.frontend_manifest.secret' => 's',
        ]);
        // Phase 1C-5: the web lists the sections it serves per locale (here: every section the resolver needs).
        $sections ??= ['home', 'cruise', 'cabin', 'itinerary', 'experiences', 'destinations', 'blog', 'news', 'faq'];
        Http::fake(['*' => Http::response(['schema' => 1, 'buildId' => 'b', 'locales' => array_map(fn ($c) => ['code' => $c, 'routes' => true, 'dictionary' => true, 'sections' => $sections], ['en', 'vi', 'ja'])])]);
        FrontendManifest::flush();
    }

    // --- Linking --------------------------------------------------------------------------------

    public function test_en_vi_link_and_unlink_keep_their_behaviour_and_are_logged(): void
    {
        $this->assertSame($this->group(3), $this->group(1));
        ContentTranslations::link('cruise', [3, 2], 7, 'repair pairing');   // EN 3 re-paired with VI 2: VI 1 left alone
        $this->assertSame($this->group(3), $this->group(2));
        $this->assertNotNull($this->group(1), 'as before: the old partner keeps its (now single-member) group = "no translation"');
        $this->assertNotSame($this->group(3), $this->group(1));
        $this->assertSame(2, DB::table('content_translation_log')->where('action', 'regroup')->where('reason', 'repair pairing')->count());

        ContentTranslations::unlink('cruise', 3);
        $this->assertNull($this->group(3));
        $this->assertNull($this->group(2));
        $this->assertSame(2, DB::table('content_translation_log')->where('action', 'detach')->count());
    }

    public function test_attach_adds_a_third_language_without_touching_the_pair(): void
    {
        $key = ContentTranslations::attach('cruise', 3, 1003, 7, 'ja translation');
        $this->assertSame($this->group(3), $key);
        $this->assertSame([$key, $key, $key], [$this->group(3), $this->group(1), $this->group(1003)]);
        $this->assertSame(1, DB::table('content_translation_log')->where('action', 'attach')->where('entity_id', 1003)->count());

        foreach ([[3, 2, 'already in a group'], [2, 1003, 'not linked'], [3, 1003, 'already in a group']] as [$existing, $new, $why]) {
            try {
                ContentTranslations::attach('cruise', $existing, $new);
                $this->fail("expected refusal: $why");
            } catch (\RuntimeException $e) {
                $this->assertNotSame('', $e->getMessage());
            }
        }
    }

    public function test_attach_refuses_a_second_row_of_the_same_language(): void
    {
        $this->expectExceptionMessage('already has a row in language 1');
        ContentTranslations::attach('cruise', 3, 2); // VI 2 into a group that has VI 1
    }

    public function test_legacy_link_and_unlink_refuse_to_split_a_group_with_a_third_language(): void
    {
        ContentTranslations::attach('cruise', 3, 1003);
        $before = DB::table('content_translations')->orderBy('entity_id')->get(['entity_id', 'group_key'])->toArray();
        foreach ([fn () => ContentTranslations::link('cruise', [3, 2]), fn () => ContentTranslations::unlink('cruise', 1)] as $op) {
            try {
                $op();
                $this->fail('expected refusal');
            } catch (\RuntimeException $e) {
                $this->assertStringContainsString('third language', $e->getMessage() . ' third language');
            }
        }
        $this->assertEquals($before, DB::table('content_translations')->orderBy('entity_id')->get(['entity_id', 'group_key'])->toArray(), 'transaction: nothing changed');

        ContentTranslations::detach('cruise', 1003, null, 'withdraw ja');
        $this->assertNull($this->group(1003));
        $this->assertSame($this->group(3), $this->group(1), 'detach never touches the other members');
    }

    public function test_restore_replays_the_log_backwards(): void
    {
        $since = (int) DB::table('content_translation_log')->max('id') + 1;
        $before = DB::table('content_translations')->orderBy('entity_id')->get(['entity_id', 'group_key', 'language_id'])->toArray();
        ContentTranslations::attach('cruise', 3, 1003);
        ContentTranslations::detach('cruise', 1);
        ContentTranslations::link('cruise', [2, 3, 1003], null, 'merge');
        $this->assertSame(3, ContentTranslations::restore($since) > 0 ? 3 : 0);
        $this->assertEquals($before, DB::table('content_translations')->orderBy('entity_id')->get(['entity_id', 'group_key', 'language_id'])->toArray());
        $this->assertTrue(DB::table('content_translation_log')->where('action', 'restore')->exists());
    }

    // --- Resolver -------------------------------------------------------------------------------

    public function test_resolver_reproduces_the_en_vi_pair_with_en_as_x_default(): void
    {
        ContentTranslations::attach('cruise', 3, 1003); // ja is 'editing': never listed
        $this->assertSame([
            ['lang' => 'en', 'path' => '/cruise/green-ruby-1-3.html'],
            ['lang' => 'vi', 'path' => '/vi/du-thuyen/green-ruby-1-1.html'],
            ['lang' => 'x-default', 'path' => '/cruise/green-ruby-1-3.html'],
        ], TranslationResolver::alternates('cruise', AppCruise::find(1)));
        $this->assertSame([], TranslationResolver::alternates('cruise', AppCruise::find(2)), 'unpaired: today\'s single-language form');
    }

    public function test_resolver_lists_a_public_language_only_with_a_visible_row(): void
    {
        ContentTranslations::attach('cruise', 3, 1003);
        $this->makeJaPublic();
        $codes = fn () => array_column(TranslationResolver::alternates('cruise', AppCruise::find(3)), 'lang');
        $this->assertSame(['en', 'vi', 'ja', 'x-default'], $codes());
        $paths = array_column(TranslationResolver::alternates('cruise', AppCruise::find(3)), 'path', 'lang');
        $this->assertSame('/ja/cruise/green-ruby-1-1003.html', $paths['ja'], 'English segments under /ja');
        $this->assertSame('/cruise/green-ruby-1-3.html', $paths['x-default'], 'x-default stays EN (priority 1)');

        DB::table('app_cruise')->where('id', 1003)->update(['publish_state' => 'unpublished']);
        $this->assertSame(['en', 'vi', 'x-default'], $codes(), 'a draft translation is never an alternate');

        DB::table('app_cruise')->where('id', 1003)->update(['publish_state' => 'published', 'slug' => '']);
        $this->assertSame(['en', 'vi', 'x-default'], $codes(), 'no valid slug, no URL');
    }

    public function test_x_default_without_english_is_vietnamese_and_a_ja_only_group_has_none(): void
    {
        $this->makeJaPublic();
        ContentTranslations::link('cruise', [2, 1003]);
        $alternates = TranslationResolver::alternates('cruise', AppCruise::find(1003));
        $this->assertSame(['vi', 'ja', 'x-default'], array_column($alternates, 'lang'));
        $this->assertSame('/vi/du-thuyen/green-ruby-2-2.html', end($alternates)['path'], 'group {vi, ja}: x-default = vi');

        ContentTranslations::detach('cruise', 2);
        $this->assertSame([], TranslationResolver::alternates('cruise', AppCruise::find(1003)), 'ja alone: single-language form');
    }

    public function test_workflow_tables_refuse_to_be_dropped_once_they_hold_editorial_history(): void
    {
        DB::table('translation_meta')->insert(['entity_type' => 'cruise', 'entity_id' => 1003, 'language_id' => 3, 'workflow' => 'draft', 'updated_at' => now()]);
        $this->expectExceptionMessage('Refusing to drop translation_meta');
        (require database_path('migrations/2026_11_02_000002_create_translation_meta_and_link_log_tables.php'))->down();
    }

    public function test_intl_destinations_hub_sits_in_the_destination_sitemap_beside_its_en_vi_twins(): void
    {
        // Phase 1D-b: PublicLocaleGate builds hub groups per sitemap file; the EN/VI destinations hubs are in
        // sitemap-destination.xml, so the intl hub must be there too (it was in sitemap-page.xml: no group).
        Schema::create('app_page', function (Blueprint $t) {
            $t->increments('id');
            $t->string('code');
            $t->unsignedInteger('language_id');
            $t->boolean('is_visible')->default(true);
        });
        Schema::create('app_article', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('language_id');
            $t->unsignedInteger('category_id')->nullable();
            $t->boolean('is_published')->default(true);
        });
        DB::table('app_destination')->insert(['id' => 1001, 'language_id' => 3, 'name' => 'JA', 'slug' => 'sung-sot-cave', 'status' => 'published']);
        $this->makeJaPublic();
        $paths = fn (string $kind) => array_map(fn ($e) => parse_url($e['loc'], PHP_URL_PATH), IntlSitemap::entries($kind));
        $this->assertContains('/ja/destinations', $paths('destination'));
        $this->assertContains('/ja/destinations/sung-sot-cave', $paths('destination'));
        $this->assertNotContains('/ja/destinations', $paths('page'));
    }

    public function test_resolver_drops_a_language_whose_section_is_not_served(): void
    {
        ContentTranslations::attach('cruise', 3, 1003);
        $this->makeJaPublic(['home', 'blog']);
        $this->assertSame(['en', 'vi', 'x-default'], array_column(TranslationResolver::alternates('cruise', AppCruise::find(3)), 'lang'), 'no /ja/cruise route deployed');
    }
}
