<?php

namespace Tests\Feature\Locale;

use App\Http\Controllers\Api\Content\ResolvesContentLanguage;
use App\Payments\PriceList;
use App\Services\Locale\FrontendManifest;
use App\Services\Locale\LocaleRegistry;
use App\Services\Locale\PublicLocaleGate;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Bootstrap\BootProviders;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Modules\BackEnd\Entities\AdLanguage;
use Tests\TestCase;

/**
 * Locale registry, effective-public rule and frontend readiness (phase 1B, greenruby-web docs/i18n-audit/phase-1/
 * 1b-report.md): en/vi keep today's behaviour; a new language is public only with status 'public' AND every web
 * instance supporting it; unknown languages never get English under their own code.
 */
class LocaleRegistryTest extends TestCase
{
    private const WEB_A = 'http://web-a:3000/api/i18n/manifest';
    private const WEB_B = 'http://web-b:3000/api/i18n/manifest';
    private const REVALIDATE = 'http://web-a:3000/api/revalidate';

    public function createApplication()
    {
        $app = require __DIR__ . '/../../../bootstrap/app.php';
        $app->beforeBootstrapping(BootProviders::class, function ($app) {
            $schema = $app['db']->connection()->getSchemaBuilder();
            $schema->create('ad_language', function (Blueprint $t) {
                $t->increments('id');
                $t->string('code', 50);
                $t->string('name')->nullable();
                $t->boolean('is_default')->default(false);
                $t->unsignedInteger('ord')->default(0);
            });
            // Rows exist before routes are registered (both route trees read ad_language at boot).
            // Production ids: vi = 1 (ord 2), en = 2 (ord 1, default).
            $app['db']->connection()->table('ad_language')->insert([
                ['id' => 1, 'code' => 'vi', 'name' => 'Tiếng Việt', 'is_default' => false, 'ord' => 2],
                ['id' => 2, 'code' => 'en', 'name' => 'English', 'is_default' => true, 'ord' => 1],
            ]);
            $schema->create('ad_resource', function (Blueprint $t) {
                $t->increments('id');
                $t->string('alias');
            });
            $schema->create('ad_privilege', function (Blueprint $t) {
                $t->increments('id');
                $t->unsignedInteger('resource_id');
                $t->string('alias');
            });
        });
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->migration()->up();
        // A future language, inserted the way phase 2 will (the model guard is tested separately).
        DB::table('ad_language')->insert(['id' => 3, 'code' => 'ja', 'name' => '日本語', 'is_default' => false, 'ord' => 3]);
        LocaleRegistry::flush();
        config([
            'i18n.frontend_manifest.urls' => [self::WEB_A, self::WEB_B],
            'i18n.frontend_manifest.secret' => 'manifest-test-secret',
            'i18n.content_api.unknown_language' => 'not_found',
            // Mechanics of new languages are tested with the hold-back released; it is tested on its own below.
            'i18n.allow_public_new_locales' => true,
            'services.content_api.key' => 'content-test-key',
        ]);
        FrontendManifest::flush();
        Http::fake(function ($request) {
            $answer = $this->instances[$request->url()] ?? Http::response('no instance', 500);

            return $answer instanceof \Closure ? $answer() : $answer;
        });

        Route::get('/_test/lang', function (Request $request) {
            return response()->json(['code' => (new LangProbe())->resolve($request)->code]);
        });
        Route::post('/_test/lang-lenient', function (Request $request) {
            return response()->json(['code' => (new LangProbe())->resolve($request, (string) $request->input('lang'), true)->code]);
        });
    }

    private function migration()
    {
        return require database_path('migrations/2026_11_01_000001_add_locale_registry_to_ad_language.php');
    }

    private function setStatus(string $code, string $status): void
    {
        DB::table('ad_language')->where('code', $code)->update(['site_status' => $status]);
        LocaleRegistry::flush();
    }

    private function manifest(array $codes, string $build = 'build-1', int $schema = 1): array
    {
        return ['schema' => $schema, 'buildId' => $build, 'locales' => array_map(fn ($c) => ['code' => $c, 'routes' => true, 'dictionary' => true], $codes)];
    }

    /** @var array<string, mixed> What each web instance answers; one Http::fake reads it (stubs cannot be replaced). */
    private array $instances = [];

    private function fakeInstances($a, $b): void
    {
        $this->instances = [self::WEB_A => $a, self::WEB_B => $b] + $this->instances;
    }

    // --- Migration and registry state -----------------------------------------------------------

    public function test_migration_backfills_en_and_vi_to_todays_behaviour(): void
    {
        $en = LocaleRegistry::byCode('en');
        $vi = LocaleRegistry::byCode('vi');
        $this->assertSame(['public', 'legacy', '', 'en', 'en_US', 2, 1, false], [
            $en->site_status, $en->route_style, $en->url_prefix, $en->hreflang, $en->og_locale, (int) $en->price_list_language_id, (int) $en->xdefault_priority, (bool) $en->workflow_controlled,
        ]);
        $this->assertSame(['public', 'legacy', 'vi', 'vi', 'vi_VN', 1, 2, false], [
            $vi->site_status, $vi->route_style, $vi->url_prefix, $vi->hreflang, $vi->og_locale, (int) $vi->price_list_language_id, (int) $vi->xdefault_priority, (bool) $vi->workflow_controlled,
        ]);
        $this->assertSame(['en', 'vi', 'ja'], LocaleRegistry::all()->pluck('code')->all(), 'admin order (ord) kept');
    }

    public function test_a_new_language_starts_disabled_and_intl(): void
    {
        $ja = LocaleRegistry::byCode('ja');
        $this->assertSame('disabled', LocaleRegistry::status($ja));
        $this->assertFalse(LocaleRegistry::isLegacy($ja));
        $this->assertFalse(LocaleRegistry::isEditable($ja));
        $this->assertSame(['en', 'vi'], LocaleRegistry::effectivePublic()->pluck('code')->all());
    }

    public function test_creating_a_language_beyond_en_vi_is_blocked_until_phase_1c(): void
    {
        try {
            AdLanguage::create(['code' => 'fr', 'name' => 'Français', 'ord' => 4]);
            $this->fail('expected the creation guard');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('blocked until phase 1C', $e->getMessage());
        }
        $this->assertNull(DB::table('ad_language')->where('code', 'fr')->first());

        config(['i18n.allow_new_locales' => true]);
        AdLanguage::create(['code' => 'fr', 'name' => 'Français', 'ord' => 4]);
        $this->assertSame('disabled', LocaleRegistry::status(LocaleRegistry::byCode('fr')), 'new rows default to disabled');
    }

    public function test_down_migration_refuses_while_another_language_exists(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->migration()->down();
    }

    public function test_registry_cache_is_flushed_when_a_row_is_saved(): void
    {
        $this->assertSame('disabled', LocaleRegistry::status(LocaleRegistry::byCode('ja')));
        DB::table('ad_language')->where('code', 'ja')->update(['site_status' => 'editing']);
        $this->assertSame('disabled', LocaleRegistry::status(LocaleRegistry::byCode('ja')), 'raw SQL is not seen until a flush (bounded by registry_ttl)');

        $model = AdLanguage::find(3);
        $model->site_status = 'public';
        $model->save();
        $this->assertSame('public', LocaleRegistry::status(LocaleRegistry::byCode('ja')), 'an Eloquent save flushes at once');
    }

    // --- Effective public: status AND every web instance ----------------------------------------

    public function test_editing_is_never_public(): void
    {
        $this->setStatus('ja', 'editing');
        $this->fakeInstances(Http::response($this->manifest(['en', 'vi', 'ja'])), Http::response($this->manifest(['en', 'vi', 'ja'])));
        $this->assertTrue(LocaleRegistry::isEditable(LocaleRegistry::byCode('ja')));
        $this->assertFalse(LocaleRegistry::isEffectivePublic(LocaleRegistry::byCode('ja')));
    }

    public function test_public_needs_every_instance_to_support_the_language(): void
    {
        $this->setStatus('ja', 'public');
        $ja = fn () => LocaleRegistry::isEffectivePublic(LocaleRegistry::byCode('ja'));

        $this->fakeInstances(Http::response($this->manifest(['en', 'vi', 'ja'])), Http::response($this->manifest(['en', 'vi', 'ja'])));
        $this->assertTrue($ja());
        $this->assertSame(['en', 'vi', 'ja'], LocaleRegistry::effectivePublic()->pluck('code')->all());
    }

    /** Rolling deploy: one instance still runs the previous build without ja. */
    public function test_mixed_version_rollout_is_not_ready(): void
    {
        $this->setStatus('ja', 'public');
        $this->fakeInstances(Http::response($this->manifest(['en', 'vi', 'ja'], 'new')), Http::response($this->manifest(['en', 'vi'], 'old')));
        $this->assertFalse(LocaleRegistry::isEffectivePublic(LocaleRegistry::byCode('ja')));
        $this->assertSame(['en', 'vi'], LocaleRegistry::effectivePublic()->pluck('code')->all());
    }

    public function test_an_unreachable_failing_or_malformed_instance_fails_closed_for_new_languages_only(): void
    {
        $this->setStatus('ja', 'public');
        $cases = [
            'unreachable' => fn () => throw new \Illuminate\Http\Client\ConnectionException('down'),
            'http 500' => Http::response('oops', 500),
            'unauthorized' => Http::response(['error' => 'unauthorized'], 401),
            'schema 2' => Http::response($this->manifest(['en', 'vi', 'ja'], 'b', 2)),
            'no build id' => Http::response(['schema' => 1, 'locales' => [['code' => 'ja', 'routes' => true, 'dictionary' => true]]]),
            'not ready' => Http::response(['schema' => 1, 'buildId' => 'b', 'locales' => [['code' => 'ja', 'routes' => true, 'dictionary' => false]]]),
        ];
        foreach ($cases as $label => $bad) {
            FrontendManifest::flush();
            $this->fakeInstances(Http::response($this->manifest(['en', 'vi', 'ja'])), $bad);
            $this->assertFalse(LocaleRegistry::isEffectivePublic(LocaleRegistry::byCode('ja')), $label);
            $this->assertSame(['en', 'vi'], LocaleRegistry::effectivePublic()->pluck('code')->all(), "$label: en/vi never depend on the manifest");
        }
    }

    public function test_no_configured_instance_or_secret_means_no_new_language(): void
    {
        $this->setStatus('ja', 'public');
        $this->fakeInstances(Http::response($this->manifest(['en', 'vi', 'ja'])), Http::response($this->manifest(['en', 'vi', 'ja'])));
        config(['i18n.frontend_manifest.urls' => []]);
        $this->assertFalse(LocaleRegistry::isEffectivePublic(LocaleRegistry::byCode('ja')));
        config(['i18n.frontend_manifest.urls' => [self::WEB_A], 'i18n.frontend_manifest.secret' => '']);
        $this->assertFalse(LocaleRegistry::isEffectivePublic(LocaleRegistry::byCode('ja')));
        Http::assertNothingSent();
    }

    /** Web image rollback: once the cached answer expires (ttl), the rolled-back build's manifest drops ja. */
    public function test_rollback_drops_the_language_within_the_manifest_ttl(): void
    {
        $this->setStatus('ja', 'public');
        Carbon::setTestNow('2026-11-01 10:00:00');
        $this->fakeInstances(Http::response($this->manifest(['en', 'vi', 'ja'], 'new')), Http::response($this->manifest(['en', 'vi', 'ja'], 'new')));
        $ja = fn () => LocaleRegistry::isEffectivePublic(LocaleRegistry::byCode('ja'));

        $this->assertTrue($ja());
        // Both instances are rolled back to a build without ja.
        $this->fakeInstances(Http::response($this->manifest(['en', 'vi'], 'rolled-back')), Http::response($this->manifest(['en', 'vi'], 'rolled-back')));
        Carbon::setTestNow('2026-11-01 10:00:30');
        $this->assertTrue($ja(), 'cached answer within ttl (60 s)');
        Carbon::setTestNow('2026-11-01 10:01:01');
        $this->assertFalse($ja(), 'after ttl the rolled-back build is asked again');
        Carbon::setTestNow();
    }

    public function test_manifest_is_requested_with_the_shared_secret(): void
    {
        $this->setStatus('ja', 'public');
        $this->fakeInstances(Http::response($this->manifest(['en', 'vi', 'ja'])), Http::response($this->manifest(['en', 'vi', 'ja'])));
        LocaleRegistry::isEffectivePublic(LocaleRegistry::byCode('ja'));
        Http::assertSent(fn ($request) => $request->hasHeader('X-Manifest-Key', 'manifest-test-secret'));
    }

    // --- Content API language resolution ---------------------------------------------------------

    public function test_content_api_serves_only_effectively_public_languages(): void
    {
        $this->getJson('/_test/lang')->assertOk()->assertJson(['code' => 'en'], 'absent lang: legacy default');
        $this->getJson('/_test/lang?lang=')->assertOk()->assertJson(['code' => 'en']);
        $this->getJson('/_test/lang?lang=en')->assertOk()->assertJson(['code' => 'en']);
        $this->getJson('/_test/lang?lang=vi')->assertOk()->assertJson(['code' => 'vi']);
        $this->getJson('/_test/lang?lang=VI')->assertOk()->assertJson(['code' => 'vi']);

        foreach (['xx', 'ja', 'zh-Hant', '../en'] as $code) {
            $this->getJson('/_test/lang?lang=' . urlencode($code))->assertNotFound()->assertExactJson(['error' => 'unknown_language']);
        }
        $this->getJson('/_test/lang?lang[]=en')->assertNotFound();

        $this->setStatus('ja', 'editing');
        $this->fakeInstances(Http::response($this->manifest(['en', 'vi', 'ja'])), Http::response($this->manifest(['en', 'vi', 'ja'])));
        $this->getJson('/_test/lang?lang=ja')->assertNotFound();

        $this->setStatus('ja', 'public');
        $this->getJson('/_test/lang?lang=ja')->assertOk()->assertJson(['code' => 'ja']);
    }

    public function test_legacy_english_fallback_is_an_explicit_switch(): void
    {
        config(['i18n.content_api.unknown_language' => 'legacy_en']);
        $this->getJson('/_test/lang?lang=xx')->assertOk()->assertJson(['code' => 'en']);
    }

    public function test_contact_form_style_writes_never_lose_the_message(): void
    {
        $this->postJson('/_test/lang-lenient', ['lang' => 'ja'])->assertOk()->assertJson(['code' => 'en']);
        $this->postJson('/_test/lang-lenient', ['lang' => 'vi'])->assertOk()->assertJson(['code' => 'vi']);
    }

    // --- /locales contract ------------------------------------------------------------------------

    public function test_locales_endpoint_lists_effectively_public_languages(): void
    {
        $headers = ['X-Content-Key' => 'content-test-key'];
        $this->setStatus('ja', 'editing');
        $this->fakeInstances(Http::response($this->manifest(['en', 'vi'])), Http::response($this->manifest(['en', 'vi'])));

        $response = $this->getJson('/api/v1/content/locales', $headers)->assertOk();
        $this->assertSame('en', $response->json('default'));
        $this->assertSame(['en', 'vi'], array_column($response->json('locales'), 'code'));
        $this->assertSame([
            'code' => 'en', 'hreflang' => 'en', 'nativeName' => 'English', 'englishName' => 'English', 'urlPrefix' => '',
            'routeStyle' => 'legacy', 'ogLocale' => 'en_US', 'intlTag' => 'en-US', 'direction' => 'ltr', 'isDefault' => true,
            'priceList' => 'en', 'xDefaultPriority' => 1, 'status' => 'public', 'effectivePublic' => true, 'sections' => null,
        ], $response->json('locales.0'));

        $editable = $this->getJson('/api/v1/content/locales?scope=editable', $headers)->assertOk();
        $this->assertSame(['en', 'vi', 'ja'], array_column($editable->json('locales'), 'code'));
        $this->assertSame(['editing', false], [$editable->json('locales.2.status'), $editable->json('locales.2.effectivePublic')]);

        $this->getJson('/api/v1/content/locales')->assertUnauthorized();
    }

    public function test_locales_lists_the_sections_every_instance_serves_for_an_intl_language(): void
    {
        // Phase 1D-b: each web instance's route gate reads this, so it agrees with the CMS (sitemaps, data 404s).
        $headers = ['X-Content-Key' => 'content-test-key'];
        $with = fn (array $sections) => Http::response(['schema' => 1, 'buildId' => 'b', 'locales' => [
            ['code' => 'en', 'routes' => true, 'dictionary' => true, 'sections' => ['home']],
            ['code' => 'vi', 'routes' => true, 'dictionary' => true, 'sections' => ['home']],
            ['code' => 'ja', 'routes' => true, 'dictionary' => true, 'sections' => $sections],
        ]]);
        $this->setStatus('ja', 'public');
        $this->fakeInstances($with(['home', 'cruise', 'blog']), $with(['home', 'cruise']));
        $locales = $this->getJson('/api/v1/content/locales', $headers)->assertOk()->json('locales');
        $this->assertSame([null, null, ['home', 'cruise']], array_column($locales, 'sections'));

        $this->setStatus('ja', 'editing');
        $editable = $this->getJson('/api/v1/content/locales?scope=editable', $headers)->assertOk()->json('locales');
        $this->assertSame([], $editable[2]['sections'], 'not effectively public: nothing served');
    }

    // --- Readiness change notifier (i18n:locale manifest --notify, scheduled) ---------------------

    private function fakeWebsite(int $status = 200): void
    {
        config(['services.frontend_revalidate.url' => self::REVALIDATE, 'services.frontend_revalidate.secret' => 'rv']);
        $this->instances[self::REVALIDATE] = function () use ($status) {
            return Http::response(['ok' => true], $status);
        };
    }

    private function notify(): \Illuminate\Testing\PendingCommand
    {
        return $this->artisan('i18n:locale', ['action' => 'manifest', '--notify' => true]);
    }

    private function sent(): array
    {
        return collect(Http::recorded())->filter(fn ($pair) => $pair[0]->url() === self::REVALIDATE)->map(fn ($pair) => $pair[0]->data())->values()->all();
    }

    public function test_notifier_is_a_no_op_without_a_public_intl_language(): void
    {
        $this->setStatus('ja', 'editing');
        $this->fakeWebsite();
        $this->fakeInstances(Http::response($this->manifest(['en', 'vi', 'ja'])), Http::response($this->manifest(['en', 'vi', 'ja'])));
        $this->notify()->assertSuccessful();
        $this->notify()->assertSuccessful();
        $this->assertSame([], $this->sent());
        $this->assertCount(0, collect(Http::recorded())->filter(fn ($pair) => str_contains($pair[0]->url(), '/api/i18n/manifest')), 'no request to the web instances');
    }

    public function test_notifier_expires_the_website_once_per_readiness_change(): void
    {
        $with = fn (array $sections) => Http::response(['schema' => 1, 'buildId' => 'b', 'locales' => [
            ['code' => 'en', 'routes' => true, 'dictionary' => true, 'sections' => ['home']],
            ['code' => 'vi', 'routes' => true, 'dictionary' => true, 'sections' => ['home']],
            ['code' => 'ja', 'routes' => true, 'dictionary' => true, 'sections' => $sections],
        ]]);
        $this->setStatus('ja', 'public');
        $this->fakeWebsite();
        $this->fakeInstances($with(['home', 'cruise']), $with(['home', 'cruise']));
        $this->notify()->assertSuccessful();
        $this->notify()->assertSuccessful();
        $this->assertSame([['secret' => 'rv', 'tags' => ['content'], 'expire' => true]], $this->sent(), 'ja became linkable: one expiry');

        // Instance B drops the cruise route (partial deploy or rollback): another expiry.
        $this->fakeInstances($with(['home', 'cruise']), $with(['home']));
        $this->notify()->assertSuccessful();
        $this->assertCount(2, $this->sent());

        // Rollback, and the website cannot be reached: the change stays pending and is retried on the next run.
        $this->fakeWebsite(500);
        $this->fakeInstances(Http::response($this->manifest(['en', 'vi'])), Http::response($this->manifest(['en', 'vi'])));
        $this->notify()->assertFailed();
        $this->fakeWebsite();
        $this->notify()->assertSuccessful();
        $this->notify()->assertSuccessful();
        $this->assertCount(4, $this->sent(), 'open, partial, failed attempt, retry; then unchanged');
    }

    public function test_content_revalidation_keeps_stale_while_revalidate_by_default(): void
    {
        $this->fakeWebsite();
        \App\Support\FrontendRevalidator::send(['cabin:24']);
        \App\Support\FrontendRevalidator::send(['content'], true);
        $this->assertSame([['secret' => 'rv', 'tags' => ['cabin:24']], ['secret' => 'rv', 'tags' => ['content'], 'expire' => true]], $this->sent());
    }

    // --- State transitions (artisan i18n:locale) -------------------------------------------------

    public function test_status_transitions_are_guarded(): void
    {
        $reason = '--reason=Phase 1B transition test';
        $this->fakeInstances(Http::response($this->manifest(['en', 'vi'])), Http::response($this->manifest(['en', 'vi'])));

        $this->artisan('i18n:locale', ['action' => 'status', 'code' => 'ja', 'status' => 'public', '--reason' => 'Phase 1B transition test'])->assertFailed();
        $this->artisan('i18n:locale', ['action' => 'status', 'code' => 'ja', 'status' => 'editing'])->assertFailed(); // no reason
        $this->artisan('i18n:locale', ['action' => 'status', 'code' => 'ja', 'status' => 'editing', '--reason' => 'Phase 1B transition test', '--dry-run' => true])->assertSuccessful();
        $this->assertSame('disabled', LocaleRegistry::status(LocaleRegistry::byCode('ja')), 'dry run changes nothing');

        $this->artisan('i18n:locale', ['action' => 'status', 'code' => 'ja', 'status' => 'editing', '--reason' => 'Phase 1B transition test'])->assertSuccessful();
        $this->assertSame('editing', LocaleRegistry::status(LocaleRegistry::byCode('ja')));

        $this->artisan('i18n:locale', ['action' => 'status', 'code' => 'ja', 'status' => 'public', '--reason' => 'Phase 1B transition test'])->assertFailed();
        $this->assertSame('editing', LocaleRegistry::status(LocaleRegistry::byCode('ja')), 'refused: the website does not serve ja');

        FrontendManifest::flush();
        $this->fakeInstances(Http::response($this->manifest(['en', 'vi', 'ja'])), Http::response($this->manifest(['en', 'vi', 'ja'])));
        $this->artisan('i18n:locale', ['action' => 'status', 'code' => 'ja', 'status' => 'public', '--reason' => 'Phase 1B transition test'])->assertSuccessful();
        $this->assertTrue(LocaleRegistry::isEffectivePublic(LocaleRegistry::byCode('ja')));

        $this->artisan('i18n:locale', ['action' => 'status', 'code' => 'ja', 'status' => 'disabled', '--reason' => 'Phase 1B transition test'])->assertSuccessful();
        $this->artisan('i18n:locale', ['action' => 'status', 'code' => 'en', 'status' => 'editing', '--reason' => 'Phase 1B transition test'])->assertFailed();
        $this->assertSame('public', LocaleRegistry::status(LocaleRegistry::byCode('en')), 'en/vi stay public');
        unset($reason);
    }

    public function test_price_list_reads_the_registry(): void
    {
        DB::table('ad_language')->where('code', 'ja')->update(['price_list_language_id' => 2]);
        LocaleRegistry::flush();
        config(['booking.price_list_for_locale' => []]);
        $this->assertSame('en', PriceList::inventoryLanguageCode('ja'));
        $this->assertSame('vi', PriceList::inventoryLanguageCode('vi'));
        $this->assertSame('USD', PriceList::displayCurrency('ja'));
    }

    // --- Phase 1C: public-locale gate (sitemaps, hreflang, legacy public routes) ----------------------

    private function sitemapSample(): array
    {
        $alt = fn (array $codes) => array_merge(
            array_map(fn ($c) => ['hreflang' => $c, 'url' => 'https://greenrubycruises.com' . ($c === 'en' ? '' : "/$c") . '/cruise/x.html'], $codes),
            [['hreflang' => 'x-default', 'url' => 'https://greenrubycruises.com/cruise/x.html']]
        );

        return [
            ['loc' => 'https://greenrubycruises.com/cruise/x.html', 'alternates' => $alt(['en', 'vi', 'ja'])],
            ['loc' => 'https://greenrubycruises.com/vi/cruise/x.html', 'alternates' => $alt(['en', 'vi', 'ja'])],
            ['loc' => 'https://greenrubycruises.com/ja/cruise/x.html', 'alternates' => $alt(['en', 'vi', 'ja'])],
            // An EN page whose only counterpart is Japanese.
            ['loc' => 'https://greenrubycruises.com/cabin/y.html', 'alternates' => [
                ['hreflang' => 'en', 'url' => 'https://greenrubycruises.com/cabin/y.html'],
                ['hreflang' => 'ja', 'url' => 'https://greenrubycruises.com/ja/cabin/y.html'],
                ['hreflang' => 'x-default', 'url' => 'https://greenrubycruises.com/cabin/y.html'],
            ]],
        ];
    }

    public function test_language_of_a_url_comes_from_its_prefix(): void
    {
        DB::table('ad_language')->where('code', 'ja')->update(['url_prefix' => 'ja']);
        LocaleRegistry::flush();
        $code = fn ($u) => PublicLocaleGate::languageOfUrl($u)?->code;
        $this->assertSame('en', $code('https://greenrubycruises.com/'));
        $this->assertSame('en', $code('https://greenrubycruises.com/cruise/green-ruby-1-3.html'));
        $this->assertSame('en', $code('/vietnam-guide'));
        $this->assertSame('vi', $code('https://greenrubycruises.com/vi/du-thuyen/x.html'));
        $this->assertSame('vi', $code('/vi'));
        $this->assertSame('ja', $code('http://127.0.0.1:8091/ja/cruise/x.html'));
    }

    public function test_sitemaps_hide_a_language_until_it_is_effectively_public(): void
    {
        $locs = fn ($entries) => array_map(fn ($e) => [parse_url($e['loc'], PHP_URL_PATH), array_column($e['alternates'], 'hreflang')], $entries);
        $hidden = [
            ['/cruise/x.html', ['en', 'vi', 'x-default']],
            ['/vi/cruise/x.html', ['en', 'vi', 'x-default']],
            ['/cabin/y.html', ['en']],                      // single language left: no x-default (existing rule)
        ];
        $shown = [
            ['/cruise/x.html', ['en', 'vi', 'ja', 'x-default']],
            ['/vi/cruise/x.html', ['en', 'vi', 'ja', 'x-default']],
            ['/ja/cruise/x.html', ['en', 'vi', 'ja', 'x-default']],
            ['/cabin/y.html', ['en', 'ja', 'x-default']],
        ];
        $both = Http::response($this->manifest(['en', 'vi', 'ja']));
        $gate = fn () => $locs(PublicLocaleGate::sitemapEntries($this->sitemapSample()));

        $this->assertSame($hidden, $gate(), 'disabled');
        $this->setStatus('ja', 'editing');
        $this->fakeInstances($both, $both);
        $this->assertSame($hidden, $gate(), 'editing');
        $this->setStatus('ja', 'public');
        // Phase 1C-5: even when ja is effectively public, entries and alternates built by Laravel's shared routes
        // are never used for it; ja entries come only from IntlSitemap (tested in IntlSitemapTest).
        $this->assertSame($hidden, $gate(), 'public: legacy-built ja entries still dropped');

        FrontendManifest::flush();
        $this->fakeInstances($both, Http::response($this->manifest(['en', 'vi'], 'old')));
        $this->assertSame($hidden, $gate(), 'rolling deploy: one instance still on the old build');
        FrontendManifest::flush();
        $this->fakeInstances($both, fn () => throw new \Illuminate\Http\Client\ConnectionException('down'));
        $this->assertSame($hidden, $gate(), 'one instance not answering');

        FrontendManifest::flush();
        $this->fakeInstances($both, $both);
        $this->assertSame($hidden, $gate());
        unset($shown);
        $model = AdLanguage::find(3);
        $model->site_status = 'editing';
        $model->save();
        $this->assertSame($hidden, $gate(), 'public → editing takes effect at once (registry flushed on save)');
    }

    public function test_the_gate_changes_nothing_for_en_and_vi(): void
    {
        $enVi = array_map(function ($e) {
            $e['alternates'] = array_values(array_filter($e['alternates'], fn ($a) => $a['hreflang'] !== 'ja'));

            return $e;
        }, array_slice($this->sitemapSample(), 0, 2));
        // x-default in an unusual position stays exactly where it was.
        $enVi[] = ['loc' => 'https://greenrubycruises.com/z', 'alternates' => [
            ['hreflang' => 'x-default', 'url' => 'https://greenrubycruises.com/z'],
            ['hreflang' => 'en', 'url' => 'https://greenrubycruises.com/z'],
            ['hreflang' => 'vi', 'url' => 'https://greenrubycruises.com/vi/z'],
        ]];
        $enVi[] = ['loc' => 'https://greenrubycruises.com/vi/only', 'alternates' => [['hreflang' => 'vi', 'url' => 'https://greenrubycruises.com/vi/only']]];
        $this->assertSame($enVi, PublicLocaleGate::sitemapEntries($enVi));
    }

    public function test_public_code_check(): void
    {
        $this->assertTrue(PublicLocaleGate::isPublicCode('en'));
        $this->assertTrue(PublicLocaleGate::isPublicCode('VI'));
        $this->assertFalse(PublicLocaleGate::isPublicCode('ja'));
        $this->assertFalse(PublicLocaleGate::isPublicCode('xx'));
        $this->assertFalse(PublicLocaleGate::isPublicCode(null));
    }

    public function test_new_languages_are_held_back_until_phase_1c_is_complete(): void
    {
        config(['i18n.allow_public_new_locales' => false]);
        $this->setStatus('ja', 'public');
        $both = Http::response($this->manifest(['en', 'vi', 'ja']));
        $this->fakeInstances($both, $both);
        $this->assertFalse(LocaleRegistry::isEffectivePublic(LocaleRegistry::byCode('ja')), 'status public + every instance ready is not enough');
        $this->assertSame(['en', 'vi'], LocaleRegistry::effectivePublic()->pluck('code')->all());
        $this->assertSame(3, count(array_filter(PublicLocaleGate::sitemapEntries($this->sitemapSample()), fn ($e) => true)));
        $this->getJson('/_test/lang?lang=ja')->assertNotFound();

        $this->setStatus('ja', 'editing');
        $this->artisan('i18n:locale', ['action' => 'status', 'code' => 'ja', 'status' => 'public', '--reason' => 'Phase 1C hold-back test'])->assertFailed();
        $this->assertSame('editing', LocaleRegistry::status(LocaleRegistry::byCode('ja')));
    }

    // --- Phase 1C-5: per-section readiness ------------------------------------------------------

    public function test_sections_are_intersected_across_instances_and_missing_lists_fail_closed(): void
    {
        $this->setStatus('ja', 'public');
        $with = fn (array $sections) => Http::response(['schema' => 1, 'buildId' => 'b', 'locales' => [
            ['code' => 'en', 'routes' => true, 'dictionary' => true, 'sections' => ['home', 'cruise']],
            ['code' => 'vi', 'routes' => true, 'dictionary' => true, 'sections' => ['home', 'cruise']],
            ['code' => 'ja', 'routes' => true, 'dictionary' => true, 'sections' => $sections],
        ]]);
        $ja = fn () => LocaleRegistry::byCode('ja');

        $this->fakeInstances($with(['home', 'cruise', 'blog']), $with(['home', 'cruise']));
        $this->assertSame(['home', 'cruise'], FrontendManifest::supportedSections('ja'));
        $this->assertTrue(LocaleRegistry::sectionReady($ja(), 'cruise'));
        $this->assertFalse(LocaleRegistry::sectionReady($ja(), 'blog'), 'one instance does not serve blog');

        FrontendManifest::flush();
        $this->fakeInstances($with(['home', 'cruise']), Http::response($this->manifest(['en', 'vi', 'ja']))); // older build: no sections
        $this->assertTrue(LocaleRegistry::isEffectivePublic($ja()), 'the language itself is served');
        $this->assertSame([], FrontendManifest::supportedSections('ja'));
        $this->assertFalse(LocaleRegistry::sectionReady($ja(), 'home'), 'sections unknown: fail closed');

        $this->assertTrue(LocaleRegistry::sectionReady(LocaleRegistry::byCode('en'), 'anything'), 'en/vi never depend on the manifest');
        $this->setStatus('ja', 'editing');
        $this->assertFalse(LocaleRegistry::sectionReady($ja(), 'home'));
    }
}

class LangProbe
{
    use ResolvesContentLanguage;

    public function resolve(Request $request, ?string $code = null, bool $lenient = false): AdLanguage
    {
        return $this->resolveLanguage($request, $code, $lenient);
    }
}
