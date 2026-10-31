<?php

namespace Tests\Feature\Editorial;

use App\Services\Content\IntlMenuUrls;
use App\Services\Content\TranslationFactory;
use App\Services\Locale\FrontendManifest;
use App\Services\Locale\LocaleRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Phase 2B: a new language's menu links to its own public pages (resolver / PathBuilder) and otherwise to the English
 * page: never to a /ja URL that does not exist. Fixture: EN cruise #3 (green-ruby-1) ↔ VI #1.
 */
class IntlMenuUrlsTest extends EditorialTestCase
{
    private function makeJaPublic(): void
    {
        DB::table('ad_language')->where('code', 'ja')->update(['site_status' => 'public']);
        config([
            'i18n.allow_public_new_locales' => true,
            'i18n.frontend_manifest.urls' => ['http://web.test/api/i18n/manifest'],
            'i18n.frontend_manifest.secret' => 's',
        ]);
        $sections = ['home', 'cruise', 'cabin', 'itinerary', 'experiences', 'destinations', 'blog', 'news', 'faq', 'services', 'gallery', 'aboutUs', 'contact'];
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
}
