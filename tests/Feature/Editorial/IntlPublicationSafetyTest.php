<?php

namespace Tests\Feature\Editorial;

use App\Services\Content\ExperienceContentService;
use App\Services\Content\ItineraryContentService;
use App\Services\Content\PageConfigContentService;
use App\Services\Content\ServiceContentService;
use App\Services\Content\TranslationFactory;
use App\Services\Content\TranslationMatrix;
use App\Services\Content\Workflow;
use App\Services\Locale\FrontendManifest;
use App\Services\Locale\LocaleRegistry;
use App\Services\Publication\IntlVisibility;
use App\Services\Publication\PublicationContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\BackEnd\Constants\PageConfigConsts;
use Modules\BackEnd\Entities\AppAmenity;
use Modules\BackEnd\Entities\AppCruise;
use Modules\BackEnd\Entities\AppGroup;
use Modules\BackEnd\Entities\AppService;

/**
 * Phase 2C safety fixes 1 and 2: structural groups seeded by i18n:seed-locale stay unpublished (text never shown)
 * until the workflow publishes them; amenities gain publish_state (2026_11_07_000001), services' status is honoured
 * for a new language. en/vi rows: unchanged output.
 * Fixture: EN cruise #3, cabin #24 (group #29 Accommodation), service #5 (Spa), amenity #7 (Wi-Fi).
 */
class IntlPublicationSafetyTest extends EditorialTestCase
{
    private array $content = ['X-Content-Key' => 'content-test-key'];

    protected function setUp(): void
    {
        parent::setUp();
        (require database_path('migrations/2026_11_04_000001_create_content_revision_and_preview_tables.php'))->up();
        (require database_path('migrations/2026_11_07_000001_add_publish_state_to_app_amenity.php'))->up();
        config(['services.content_api.key' => 'content-test-key']);
        $stamp = ['created_at' => now(), 'created_by' => 1];
        DB::table('app_group')->insert([
            ['id' => 40, 'language_id' => 2, 'name' => 'Onboard', 'slug' => 'onboard', 'ord' => 1, 'type' => 3, 'tab' => 1, 'publish_state' => 'published'] + $stamp,
            ['id' => 41, 'language_id' => 2, 'name' => 'Wellness', 'slug' => 'wellness', 'ord' => 1, 'type' => 4, 'tab' => null, 'publish_state' => 'published'] + $stamp,
            ['id' => 42, 'language_id' => 2, 'name' => 'Families', 'slug' => 'families', 'ord' => 1, 'type' => 5, 'tab' => null, 'publish_state' => 'published'] + $stamp,
        ]);
    }

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

    /** Walks a seeded/created ja row through the review steps (operator CLI: actor null, audited as such). */
    private function approveAndPublish(string $type, int $id): string
    {
        Workflow::apply($type, $id, 'submit');
        Workflow::apply($type, $id, 'approve_translation');
        Workflow::apply($type, $id, 'approve_seo');

        return Workflow::apply($type, $id, 'publish');
    }

    private function jaGroup(int $sourceId): object
    {
        return DB::table('app_group')->find(TranslationFactory::structuralGroup($sourceId, 3));
    }

    public function test_seed_locale_creates_structural_groups_unpublished_in_draft(): void
    {
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
        foreach ([29, 40, 41, 42] as $source) {
            $group = $this->jaGroup($source);
            $this->assertSame('unpublished', $group->publish_state, "group from #{$source}");
            $this->assertSame('draft', DB::table('translation_meta')->where('entity_type', 'group')->where('entity_id', $group->id)->value('workflow'));
        }
        $this->assertSame(['published'], DB::table('app_group')->whereIn('language_id', [1, 2])->distinct()->pluck('publish_state')->all(), 'en/vi untouched');
        $this->assertSame(0, DB::table('app_group')->where('language_id', 3)->where('type', 1)->count(), 'FAQ groups are content: not seeded');
    }

    public function test_an_unpublished_structural_group_never_shows_its_text_in_a_public_read(): void
    {
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
        DB::table('app_group')->where('language_id', 3)->update(['name' => DB::raw("'JA-' || name")]); // translated, not reviewed
        $ja = $this->ja();
        $onboard = $this->jaGroup(40);
        $wellness = $this->jaGroup(41);

        // Experience tabs and service groups (Eloquent: PublishedScope).
        PublicationContext::asPublic(function () use ($ja) {
            $this->assertCount(0, ExperienceContentService::groups($ja));
            $this->assertCount(0, ServiceContentService::groups($ja));
        });
        // Raw join of the cruise page (facility cabin + its group name): CruiseContentService hides the name by id.
        // (The cruise detail endpoint itself uses MySQL's CURRENT_DATE(): exercised on the MySQL clone rehearsal.)
        PublicationContext::asPublic(function () use ($wellness) {
            $this->assertSame([(int) $wellness->id], IntlVisibility::hiddenIds('group', [$wellness->id, 29], 3));
            $this->assertSame([], IntlVisibility::hiddenIds('group', [29, 8], 2), 'en: never filtered');
        });
        $this->assertSame([], IntlVisibility::hiddenIds('group', [$wellness->id], 3), 'outside a public read (admin): not filtered');

        // Published through the workflow: shown.
        $this->assertSame('published', $this->approveAndPublish('group', (int) $wellness->id));
        $this->assertSame('published', DB::table('app_group')->find($wellness->id)->publish_state);
        PublicationContext::asPublic(function () use ($ja, $wellness) {
            $this->assertSame([], IntlVisibility::hiddenIds('group', [$wellness->id], 3));
            $this->assertSame([(int) $wellness->id], ServiceContentService::groups($ja)->keys()->all());
            $this->assertSame(['JA-Wellness'], ServiceContentService::groups($ja)->pluck('name')->all());
            $this->assertCount(0, ExperienceContentService::groups($ja), 'the other group stays hidden');
        });
        $log = DB::table('editorial_audit_log')->where('entity_type', 'group')->where('entity_id', $wellness->id)->orderBy('id')->pluck('action')->all();
        $this->assertSame(['submit', 'approve_translation', 'approve_seo', 'publish'], $log);
        $this->assertStringNotContainsString('JA-', json_encode(PublicationContext::asPublic(fn () => ExperienceContentService::groups($ja))));
    }

    public function test_a_published_structural_group_is_edited_through_a_revision(): void
    {
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
        $group = $this->jaGroup(29);
        $this->approveAndPublish('group', (int) $group->id);
        $this->actingAs(\Modules\BackEnd\Entities\AdUser::find(2), 'admin');
        $model = AppGroup::withoutGlobalScopes()->find($group->id);
        $model->name = '客室';
        $model->save();
        $this->assertSame('Accommodation', DB::table('app_group')->find($group->id)->name, 'live text unchanged');
        $revision = DB::table('content_revision')->where('entity_type', 'group')->where('entity_id', $group->id)->first();
        $this->assertSame('客室', json_decode($revision->fields, true)['name']);
        Workflow::apply('group', (int) $group->id, 'submit');
        Workflow::apply('group', (int) $group->id, 'approve_translation');
        $this->assertSame('revision_applied', Workflow::apply('group', (int) $group->id, 'approve_seo'));
        $this->assertSame('客室', DB::table('app_group')->find($group->id)->name);
    }

    public function test_the_structural_group_form_shows_the_workflow_panel(): void
    {
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
        $panel = \Modules\BackEnd\Helpers\TranslationPanel::for('faq_group', $this->jaGroup(29));
        $this->assertSame(['group', false, 'draft'], [$panel['type'], $panel['published'], $panel['workflow']]);
        $this->assertSame(['submit'], array_column($panel['actions'], 'key'));
        $this->assertNull(\Modules\BackEnd\Helpers\TranslationPanel::for('faq_group', DB::table('app_group')->find(29)), 'en: no panel, as before');
    }

    public function test_amenity_migration_keeps_en_vi_visible_and_new_rows_hidden(): void
    {
        $this->assertSame('published', DB::table('app_amenity')->find(7)->publish_state, 'existing en row');
        // A ja row that existed before the migration is set unpublished by it.
        (require database_path('migrations/2026_11_07_000001_add_publish_state_to_app_amenity.php'))->down();
        DB::table('app_amenity')->insert(['id' => 70, 'language_id' => 3, 'name' => 'Wi-Fi（無料）', 'ord' => 1, 'created_at' => now(), 'created_by' => 1]);
        (require database_path('migrations/2026_11_07_000001_add_publish_state_to_app_amenity.php'))->up();
        $this->assertSame(['published', 'unpublished'], [DB::table('app_amenity')->find(7)->publish_state, DB::table('app_amenity')->find(70)->publish_state]);

        // Factory and admin form: unpublished in ja; en created by the admin stays published.
        $created = TranslationFactory::create('amenity', 7, $this->ja(), 1)['id'];
        $this->assertSame('unpublished', DB::table('app_amenity')->find($created)->publish_state);
        $form = new AppAmenity(['language_id' => 3, 'name' => 'サウナ']);
        $form->ord = 2;
        $form->save();
        $this->assertSame('unpublished', DB::table('app_amenity')->find($form->id)->publish_state);
        $en = new AppAmenity(['language_id' => 2, 'name' => 'Sauna']);
        $en->ord = 2;
        $en->save();
        $this->assertSame('published', DB::table('app_amenity')->find($en->id)->publish_state);
        $this->assertFalse(TranslationMatrix::published('amenity', DB::table('app_amenity')->find($created)));
    }

    public function test_an_amenity_or_service_is_published_only_from_approved(): void
    {
        $amenity = TranslationFactory::create('amenity', 7, $this->ja(), 1)['id'];
        $service = TranslationFactory::create('service', 5, $this->ja(), 1)['id'];
        foreach ([[AppAmenity::class, $amenity, 'publish_state', 'published'], [AppService::class, $service, 'status', 1]] as [$class, $id, $column, $on]) {
            $row = $class::find($id);
            $row->{$column} = $on;
            try {
                $row->save();
                $this->fail("{$class} published without approval");
            } catch (\App\Services\Content\EditorialRefusal $e) {
                $this->assertStringContainsString('not approved', $e->getMessage());
            }
        }
        $this->assertSame('published', $this->approveAndPublish('amenity', $amenity));
        $this->assertSame('published', DB::table('app_amenity')->find($amenity)->publish_state);
        $this->assertSame('published', $this->approveAndPublish('service', $service));
        $this->assertSame(1, (int) DB::table('app_service')->find($service)->status);
        $this->assertSame('publish', DB::table('editorial_audit_log')->where('entity_type', 'amenity')->where('entity_id', $amenity)->orderByDesc('id')->value('action'));
    }

    public function test_unpublished_ja_amenities_and_services_are_hidden_from_public_reads_en_unchanged(): void
    {
        $ja = $this->ja();
        $en = LocaleRegistry::byCode('en');
        $amenity = TranslationFactory::create('amenity', 7, $ja, 1)['id'];
        $service = TranslationFactory::create('service', 5, $ja, 1)['id'];
        $cruise = TranslationFactory::create('cruise', 3, $ja, 1)['id'];
        DB::table('app_cruise')->where('id', $cruise)->update(['slug' => 'green-ruby-1', 'publish_state' => 'published']);
        DB::table('app_cruise_amenity')->insert([['cruise_id' => $cruise, 'amenity_id' => $amenity, 'ord' => 1], ['cruise_id' => 3, 'amenity_id' => 7, 'ord' => 1]]);
        DB::table('app_cruise_service')->insert([['cruise_id' => $cruise, 'service_id' => $service, 'ord' => 1], ['cruise_id' => 3, 'service_id' => 5, 'ord' => 1]]);
        DB::table('app_service')->where('id', 5)->update(['status' => 0]); // an en service switched off: en shows it today, as before

        PublicationContext::asPublic(function () use ($ja, $en, $amenity, $service) {
            $this->assertSame([], PageConfigContentService::sourceData([$amenity], PageConfigConsts::SOURCE_DATA_TYPE_AMENITY, $ja));
            $this->assertSame([], ServiceContentService::presentMany(AppService::whereIn('id', [$service])->get(), $ja));
            $this->assertSame([], ItineraryContentService::services(AppService::whereIn('id', [$service])->get())['included']->all());
            $this->assertSame(['Wi-Fi'], array_column(PageConfigContentService::sourceData([7], PageConfigConsts::SOURCE_DATA_TYPE_AMENITY, $en), 'name'));
            $this->assertSame(['Spa'], array_column(ServiceContentService::presentMany(AppService::whereIn('id', [5])->get(), $en), 'name'), 'en status is not read (unchanged behaviour)');
        });
        // The cruise page's amenity and service lists (pivot relations; CruiseContentService / ItineraryContentService).
        $read = fn (int $id) => PublicationContext::asPublic(fn () => [
            IntlVisibility::keep('amenity', AppCruise::find($id)->cruiseAmenities)->pluck('name')->all(),
            collect(ItineraryContentService::services(AppCruise::find($id)->cruiseServices)['included'])->pluck('name')->all(),
        ]);
        $this->assertSame([[], []], $read($cruise));
        $enRelation = AppCruise::find(3)->cruiseAmenities;
        $this->assertSame($enRelation, IntlVisibility::keep('amenity', $enRelation), 'en: the same collection, untouched');
        $this->assertSame([['Wi-Fi'], ['Spa']], $read(3));

        $this->approveAndPublish('amenity', $amenity);
        $this->approveAndPublish('service', $service);
        $this->assertSame([['Wi-Fi'], ['Spa']], $read($cruise), 'published: shown (text still the English copy here)');
    }

    public function test_a_ja_cabin_hides_unpublished_amenities_and_audience_groups(): void
    {
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
        $ja = $this->ja();
        $cruise = TranslationFactory::create('cruise', 3, $ja, 1)['id'];
        DB::table('app_cruise')->where('id', $cruise)->update(['slug' => 'green-ruby-1', 'publish_state' => 'published']);
        $cabin = TranslationFactory::create('cabin', 24, $ja, 1)['id'];
        DB::table('app_cabin')->where('id', $cabin)->update(['slug' => 'imperial-suite', 'publish_state' => 'published']);
        $amenity = TranslationFactory::create('amenity', 7, $ja, 1)['id'];
        $audience = $this->jaGroup(42);
        DB::table('app_cabin_amenity')->insert(['cabin_id' => $cabin, 'amenity_id' => $amenity, 'ord' => 1]);
        DB::table('app_cabin_suitable_audience')->insert(['cabin_id' => $cabin, 'group_id' => $audience->id, 'ord' => 1]);
        $this->makeJaPublic();

        $res = $this->getJson("/api/v1/content/cabins/{$cabin}?lang=ja", $this->content)->assertOk();
        $this->assertSame([[], []], [$res->json('amenities'), $res->json('suitableFor')]);
        $this->approveAndPublish('amenity', $amenity);
        $this->approveAndPublish('group', (int) $audience->id);
        $res = $this->getJson("/api/v1/content/cabins/{$cabin}?lang=ja", $this->content)->assertOk();
        $this->assertSame([['Wi-Fi'], ['Families']], [collect($res->json('amenities'))->pluck('name')->all(), collect($res->json('suitableFor'))->pluck('name')->all()]);
    }
}
