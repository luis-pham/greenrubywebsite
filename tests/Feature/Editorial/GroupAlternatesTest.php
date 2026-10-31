<?php

namespace Tests\Feature\Editorial;

use App\Services\Content\GroupAlternates;
use App\Services\Content\TranslationFactory;
use App\Services\Locale\FrontendManifest;
use App\Services\Locale\IntlSitemap;
use App\Services\Locale\LocaleRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * 1E hardening, item E: hreflang groups of blog categories and FAQ groups. New-language members join an EN/VI pair
 * only when that pair stays exactly as the site shows it today; otherwise nothing changes (F-2 baseline).
 * Fixture: EN category #10 ↔ VI #20 (one article each); EN FAQ group #57 (one FAQ) ↔ VI #58 (no FAQ).
 */
class GroupAlternatesTest extends EditorialTestCase
{
    private array $content = ['X-Content-Key' => 'content-test-key'];

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.content_api.key' => 'content-test-key']);
    }

    private function makeJaPublic(): void
    {
        DB::table('ad_language')->where('code', 'ja')->update(['site_status' => 'public']);
        config([
            'i18n.allow_public_new_locales' => true,
            'i18n.frontend_manifest.urls' => ['http://web.test/api/i18n/manifest'],
            'i18n.frontend_manifest.secret' => 's',
        ]);
        $sections = ['home', 'blog', 'news', 'faq'];
        Http::fake(['*' => Http::response(['schema' => 1, 'buildId' => 'b', 'locales' => array_map(fn ($c) => ['code' => $c, 'routes' => true, 'dictionary' => true, 'sections' => $sections], ['en', 'vi', 'ja'])])]);
        LocaleRegistry::flush();
        FrontendManifest::flush();
    }

    /** A published ja category of EN #10 with one published article (written directly: not a workflow test). */
    private function jaCategory(): int
    {
        $cat = TranslationFactory::create('article_category', 10, $this->ja(), 1)['id'];
        DB::table('app_category')->where('id', $cat)->update(['slug' => 'travel-tips', 'publish_state' => 'published']);
        $article = TranslationFactory::create('article', 100, $this->ja(), 1)['id'];
        DB::table('app_article')->where('id', $article)->update(['slug' => 'best-time', 'is_published' => 1, 'category_id' => $cat]);

        return $cat;
    }

    private function jaFaqGroup(): int
    {
        $group = TranslationFactory::create('faq_group', 57, $this->ja(), 1)['id'];
        DB::table('app_group')->where('id', $group)->update(['slug' => 'bookings', 'publish_state' => 'published']);
        $faq = TranslationFactory::create('faq', 1, $this->ja(), 1)['id'];
        DB::table('app_faq')->where('id', $faq)->update(['publish_state' => 'published', 'group_id' => $group]);

        return $group;
    }

    private function row(string $table, int $id): object
    {
        return DB::table($table)->where('id', $id)->first();
    }

    public function test_nothing_changes_while_no_new_language_is_public(): void
    {
        $cat = $this->jaCategory();
        foreach ([10, 20, $cat] as $id) {
            $this->assertSame([], GroupAlternates::for('article_category', $this->row('app_category', $id)));
        }
        $res = $this->getJson('/api/v1/content/articles?lang=en&category=travel-tips', $this->content)->assertOk();
        $this->assertArrayNotHasKey('alternates', $res->json(), 'EN/VI payload unchanged');
        $this->assertSame([], IntlSitemap::entries('category'));
    }

    public function test_a_category_pair_gains_the_new_language_reciprocally(): void
    {
        $cat = $this->jaCategory();
        $this->makeJaPublic();
        $expected = [
            ['lang' => 'vi', 'path' => '/vi/blog/hanh-trinh'],
            ['lang' => 'en', 'path' => '/blog/travel-tips'],
            ['lang' => 'ja', 'path' => '/ja/blog/travel-tips'],
            ['lang' => 'x-default', 'path' => '/blog/travel-tips'],
        ];
        $sort = fn (array $a) => collect($a)->sortBy('lang')->values()->all();
        foreach ([10, 20, $cat] as $id) {
            $this->assertSame($sort($expected), $sort(GroupAlternates::for('article_category', $this->row('app_category', $id))), "member #$id");
        }
        // The same list in the API (page 1 only) and on the ja sitemap entry.
        $this->assertSame($sort($expected), $sort($this->getJson('/api/v1/content/articles?lang=en&category=travel-tips', $this->content)->json('alternates')));
        $this->assertSame($sort($expected), $sort($this->getJson('/api/v1/content/articles?lang=ja&category=travel-tips', $this->content)->json('alternates')));
        $entry = collect(IntlSitemap::entries('category'))->firstWhere('loc', url('/ja/blog/travel-tips'));
        $this->assertCount(4, $entry['alternates']);
    }

    public function test_f2_pair_with_an_empty_counterpart_never_gains_a_new_language(): void
    {
        $cat = $this->jaCategory();
        DB::table('app_article')->where('id', 200)->update(['is_published' => 0]); // VI #20 is now empty (noindex): F-2 shape
        $this->makeJaPublic();
        foreach ([10, 20, $cat] as $id) {
            $this->assertSame([], GroupAlternates::for('article_category', $this->row('app_category', $id)), "member #$id keeps today's output");
        }
        $this->assertArrayNotHasKey('alternates', $this->getJson('/api/v1/content/articles?lang=en&category=travel-tips', $this->content)->json());
        $entry = collect(IntlSitemap::entries('category'))->firstWhere('loc', url('/ja/blog/travel-tips'));
        $this->assertSame([], $entry['alternates'], 'the ja category stays on its own, as before');
    }

    public function test_faq_group_joins_only_when_both_legacy_groups_are_listed(): void
    {
        $group = $this->jaFaqGroup();
        $this->makeJaPublic();
        // VI #58 has no FAQ: the EN/VI pair is not touched.
        $this->assertSame([], GroupAlternates::for('faq_group', $this->row('app_group', 57)));
        $this->assertSame([], GroupAlternates::for('faq_group', $this->row('app_group', $group)));

        DB::table('app_faq')->insert(['language_id' => 1, 'group_id' => 58, 'question' => 'Huỷ được không?', 'answer' => 'Được', 'ord' => 1, 'publish_state' => 'published', 'created_at' => now(), 'created_by' => 1]);
        $all = array_map(fn ($id) => collect(GroupAlternates::for('faq_group', $this->row('app_group', $id)))->sortBy('lang')->values()->all(), [57, 58, $group]);
        $this->assertCount(4, $all[0]);
        $this->assertSame($all[0], $all[1]);
        $this->assertSame($all[0], $all[2]);
        $this->assertContains(['lang' => 'ja', 'path' => '/ja/faq/bookings'], $all[0]);
        $this->assertSame($all[0], collect($this->getJson('/api/v1/content/faqs?lang=ja&group=bookings', $this->content)->json('alternates'))->sortBy('lang')->values()->all());
        $this->assertArrayNotHasKey('alternates', $this->getJson('/api/v1/content/faqs?lang=ja&group=bookings&page=2', $this->content)->json() ?? []);
    }
}
