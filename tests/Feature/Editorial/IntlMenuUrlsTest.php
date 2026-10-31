<?php

namespace Tests\Feature\Editorial;

use App\Services\Content\IntlMenuUrls;
use App\Services\Content\TranslationFactory;
use App\Services\Locale\FrontendManifest;
use App\Services\Locale\IntlSitemap;
use App\Services\Locale\LocaleRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Phase 2B: a new language's menu links to its own public pages (resolver / PathBuilder) and otherwise to the English
 * page: never to a /ja URL that does not exist. Fixture: EN cruise #3 (green-ruby-1) ↔ VI #1.
 */
class IntlMenuUrlsTest extends EditorialTestCase
{
    private function makeJaPublic(array $extraSections = []): void
    {
        DB::table('ad_language')->where('code', 'ja')->update(['site_status' => 'public']);
        config([
            'i18n.allow_public_new_locales' => true,
            'i18n.frontend_manifest.urls' => ['http://web.test/api/i18n/manifest'],
            'i18n.frontend_manifest.secret' => 's',
        ]);
        $sections = array_merge(['home', 'cruise', 'cabin', 'itinerary', 'experiences', 'destinations', 'blog', 'news', 'faq', 'services', 'gallery', 'aboutUs', 'contact'], $extraSections);
        Http::fake(['*' => Http::response(['schema' => 1, 'buildId' => 'b', 'locales' => array_map(fn ($c) => ['code' => $c, 'routes' => true, 'dictionary' => true, 'sections' => $sections], ['en', 'vi', 'ja'])])]);
        LocaleRegistry::flush();
        FrontendManifest::flush();
    }

    private function jaCruise(): int
    {
        $id = TranslationFactory::create('cruise', 3, $this->ja(), 1)['id'];
        DB::table('app_cruise')->where('id', $id)->update(['slug' => 'green-ruby-1', 'publish_state' => 'published']);

        return $id;
    }

    public function test_detail_and_home_links_go_to_the_public_japanese_page(): void
    {
        $id = $this->jaCruise();
        $this->makeJaPublic();
        $ja = $this->ja();

        $this->assertSame("/ja/cruise/green-ruby-1-{$id}.html", IntlMenuUrls::resolve('/cruise/green-ruby-1-3.html', $ja));
        $this->assertSame('/ja', IntlMenuUrls::resolve('/', $ja), 'the home hub lists a public Japanese cruise');
        $this->assertSame('/ja#section-cruise', IntlMenuUrls::resolve('/#section-cruise', $ja), 'fragment kept');
    }

    public function test_pages_that_are_not_public_in_japanese_keep_the_english_url(): void
    {
        $id = $this->jaCruise();
        DB::table('app_cruise')->where('id', $id)->update(['publish_state' => 'unpublished']);
        $this->makeJaPublic();
        $ja = $this->ja();

        $this->assertSame('/cruise/green-ruby-1-3.html', IntlMenuUrls::resolve('/cruise/green-ruby-1-3.html', $ja), 'unpublished translation');
        $this->assertSame('/', IntlMenuUrls::resolve('/', $ja), 'empty Japanese home hub');
        $this->assertSame('/faq', IntlMenuUrls::resolve('/faq', $ja), 'no Japanese FAQ');
        $this->assertSame('/services#event', IntlMenuUrls::resolve('/services#event', $ja), 'services page not published for ja');
        $this->assertSame('/legal#safety-policies', IntlMenuUrls::resolve('/legal#safety-policies', $ja), 'no Japanese legal pages');
        $this->assertSame('#chat-with-ai', IntlMenuUrls::resolve('#chat-with-ai', $ja));
        $this->assertSame('https://example.com/x', IntlMenuUrls::resolve('https://example.com/x', $ja));
    }

    public function test_published_static_page_links_to_its_japanese_url(): void
    {
        $this->makeJaPublic();
        DB::table('app_page')->insert(['language_id' => 2, 'code' => 'service', 'title' => 'Services', 'is_visible' => 1, 'created_at' => now(), 'created_by' => 1]);
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
        DB::table('app_page')->where('language_id', 3)->where('code', 'service')->update(['is_visible' => 1]);

        $this->assertSame('/ja/services#event', IntlMenuUrls::resolve('/services#event', $this->ja()));
        $this->assertSame('/contact', IntlMenuUrls::resolve('/contact', $this->ja()), 'contact page still hidden');
    }

    public function test_travel_guides_link_to_japanese_only_when_built_and_published(): void
    {
        foreach (['guide-first-time', 'guide-transfer'] as $code) {
            DB::table('app_page')->insert(['language_id' => 2, 'code' => $code, 'title' => $code, 'is_visible' => 1, 'created_at' => now(), 'created_by' => 1]);
        }
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
        DB::table('app_page')->where('language_id', 3)->where('code', 'guide-first-time')->update(['is_visible' => 1]);

        $this->makeJaPublic(['guides']);
        $ja = $this->ja();
        $this->assertSame('/ja/guides', IntlMenuUrls::resolve('/guides', $ja));
        $this->assertSame('/ja/guides/first-time-halong-bay-cruise', IntlMenuUrls::resolve('/guides/first-time-halong-bay-cruise', $ja));
        $this->assertSame('/guides/hanoi-to-halong-bay', IntlMenuUrls::resolve('/guides/hanoi-to-halong-bay', $ja), 'transfer guide not published for ja');
        $this->assertSame(['guides', 'guideFirstTime'], array_values(array_intersect(['guides', 'guideFirstTime', 'guideTransfer'], IntlSitemap::publicSections($ja))));
        $locs = array_column(IntlSitemap::entries('page'), 'loc');
        $this->assertContains(url('/ja/guides'), $locs);
        $this->assertContains(url('/ja/guides/first-time-halong-bay-cruise'), $locs);
        $this->assertNotContains(url('/ja/guides/hanoi-to-halong-bay'), $locs);
    }

    public function test_english_links_in_japanese_rich_text_go_to_the_public_japanese_pages(): void
    {
        $id = $this->jaCruise();
        $this->makeJaPublic();
        $html = '<p><a href="/cruise/green-ruby-1-3.html#cabins">ship</a> <a href="/faq">faq</a> <a href="/ja/contact">x</a>'
            . ' <a href="https://example.com/a">e</a> <img src="/upload/a.jpg"> <a href=\'/cruise/green-ruby-1-3.html\'>q</a></p>';
        $out = \App\Http\Middleware\IntlContentLinks::localize($html, $this->ja(), '/ja');
        $this->assertStringContainsString("href=\"/ja/cruise/green-ruby-1-{$id}.html#cabins\"", $out);
        $this->assertStringContainsString("href='/ja/cruise/green-ruby-1-{$id}.html'", $out);
        $this->assertStringContainsString('href="/faq"', $out, 'no Japanese FAQ: English kept');
        $this->assertStringContainsString('href="/ja/contact"', $out, 'already Japanese: untouched');
        $this->assertStringContainsString('href="https://example.com/a"', $out);
        $this->assertStringContainsString('src="/upload/a.jpg"', $out);
    }

    public function test_faq_group_links_go_to_the_japanese_group_once_it_has_questions(): void
    {
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
        $this->makeJaPublic();
        $ja = $this->ja();
        $this->assertSame('/faq/bookings', IntlMenuUrls::resolve('/faq/bookings', $ja), 'no Japanese group yet');

        $group = TranslationFactory::create('faq_group', 57, $ja, 1)['id'];
        DB::table('app_group')->where('id', $group)->update(['slug' => 'bookings', 'publish_state' => 'published']);
        $faq = TranslationFactory::create('faq', 1, $ja, 1)['id'];
        DB::table('app_faq')->where('id', $faq)->update(['publish_state' => 'published']);
        $this->assertSame('/ja/faq/bookings', IntlMenuUrls::resolve('/faq/bookings', $ja));
    }
}
