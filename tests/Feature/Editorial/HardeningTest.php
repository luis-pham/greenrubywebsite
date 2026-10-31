<?php

namespace Tests\Feature\Editorial;

use App\Services\Content\ChildRevisions;
use App\Services\Content\RevisionStore;
use App\Services\Content\SlugService;
use App\Services\Content\TranslationFactory;
use App\Services\Content\Workflow;
use App\Services\Preview\Preview;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AdUser;
use Modules\BackEnd\Entities\AppCabinRoom;
use Modules\BackEnd\Entities\AppItineraryDayDetail;
use Modules\BackEnd\Services\AppCabinService;

/** 1E final hardening: pending child revisions (A) and separated editorial permissions (B). */
class HardeningTest extends EditorialTestCase
{
    private int $cruise;

    protected function setUp(): void
    {
        parent::setUp();
        (require database_path('migrations/2026_11_04_000001_create_content_revision_and_preview_tables.php'))->up();
        $this->seedTeam();
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
        $this->cruise = TranslationFactory::create('cruise', 3, $this->ja(), 2)['id'];
    }

    private function as(int $user): void
    {
        $this->actingAs(AdUser::find($user), 'admin');
    }

    /** A published ja translation, written by the translator (2), reviewed by 3, published by 5. */
    private function published(string $type, int $sourceId, string $slug): int
    {
        $id = TranslationFactory::create($type, $sourceId, $this->ja(), 2)['id'];
        SlugService::change($type, $id, $slug, 'Set the Japanese URL', 3);
        Workflow::apply($type, $id, 'submit', 2);
        Workflow::apply($type, $id, 'approve_translation', 3);
        Workflow::apply($type, $id, 'approve_seo', 3);
        Workflow::apply($type, $id, 'publish', 5);

        return $id;
    }

    public function test_room_text_of_a_published_cabin_waits_in_the_revision_and_applies_atomically(): void
    {
        $cabin = $this->published('cabin', 24, 'imperial-suite');
        $room = DB::table('app_cabin_room')->where('cabin_id', $cabin)->first();
        $this->as(2);
        AppCabinService::update(['id' => $cabin, 'name' => 'インペリアル', 'room_title' => ['寝室'], 'room_description' => ['キングベッド']], 3);
        $this->assertSame(['Imperial Suite', 'Bedroom', 'King bed'], [DB::table('app_cabin')->where('id', $cabin)->value('name'), DB::table('app_cabin_room')->where('id', $room->id)->value('title'), DB::table('app_cabin_room')->where('id', $room->id)->value('description')], 'public version unchanged');
        $revision = RevisionStore::open('cabin', $cabin);
        $this->assertSame(['name' => 'インペリアル', '_children' => ['app_cabin_room' => [$room->id => ['title' => '寝室', 'description' => 'キングベッド']]]], json_decode($revision->fields, true));
        $this->assertSame(2, (int) $revision->updated_by);

        // Preview shows the pending child text (nothing saved).
        Preview::enter((object) ['table' => 'app_cabin', 'entity_id' => $cabin, 'language_id' => 3, 'revision_id' => $revision->id]);
        $this->assertSame('寝室', AppCabinRoom::find($room->id)->title);
        Preview::leave();
        $this->assertSame('Bedroom', AppCabinRoom::find($room->id)->title);

        // Structure is the source's: adding a room from a published translation is refused.
        $this->expectRefusal(fn () => AppCabinService::update(['id' => $cabin, 'room_title' => ['寝室', '浴室'], 'room_description' => ['', '']], 3), 'không thêm/bớt phòng');

        Workflow::apply('cabin', $cabin, 'submit', 2);
        Workflow::apply('cabin', $cabin, 'approve_translation', 3);
        $this->assertSame('Bedroom', DB::table('app_cabin_room')->where('id', $room->id)->value('title'), 'still pending');
        Workflow::apply('cabin', $cabin, 'approve_seo', 3);
        $this->assertSame(['インペリアル', '寝室', 'キングベッド'], [DB::table('app_cabin')->where('id', $cabin)->value('name'), DB::table('app_cabin_room')->where('id', $room->id)->value('title'), DB::table('app_cabin_room')->where('id', $room->id)->value('description')], 'parent and children applied together');
        $this->assertNull(RevisionStore::open('cabin', $cabin));
    }

    public function test_programme_text_of_a_published_itinerary_and_structure_protection(): void
    {
        $it = $this->published('itinerary', 3, 'halong-immersion');
        $day = DB::table('app_itinerary_day')->where('itinerary_id', $it)->first();
        $line = DB::table('app_itinerary_day_detail')->where('itinerary_day_id', $day->id)->first();
        $form = fn (string $time, string $title) => [['id' => $day->id, 'itinerary_day_details' => [['id' => $line->id, 'time' => $time, 'title' => $title, 'description' => 'ウェルカムドリンク']]]];
        $this->as(2);
        $this->expectRefusal(fn () => ChildRevisions::itineraryDays($it, $form('13:00', '乗船')), 'giờ của chương trình');
        $this->assertTrue(ChildRevisions::itineraryDays($it, $form('12:00', '乗船')));
        $this->assertSame('Boarding', DB::table('app_itinerary_day_detail')->where('id', $line->id)->value('title'));
        Preview::enter((object) ['table' => 'app_itinerary', 'entity_id' => $it, 'language_id' => 3, 'revision_id' => RevisionStore::open('itinerary', $it)->id]);
        $this->assertSame('乗船', AppItineraryDayDetail::find($line->id)->title);
        Preview::leave();

        // Rejection keeps the public version; discarding drops the revision and changes nothing public.
        Workflow::apply('itinerary', $it, 'submit', 2);
        Workflow::apply('itinerary', $it, 'reject', 3);
        $this->assertSame('Boarding', DB::table('app_itinerary_day_detail')->where('id', $line->id)->value('title'));
        Workflow::apply('itinerary', $it, 'discard_revision', 2);
        $this->assertNull(RevisionStore::open('itinerary', $it));
        $this->assertSame(['Boarding', 'published'], [DB::table('app_itinerary_day_detail')->where('id', $line->id)->value('title'), DB::table('app_itinerary')->where('id', $it)->value('publish_state')]);
    }

    public function test_concurrent_editors_cannot_overwrite_each_others_revision(): void
    {
        $cabin = $this->published('cabin', 24, 'imperial-suite');
        $this->as(2);
        AppCabinService::update(['id' => $cabin, 'summary' => '翻訳者の版'], 3);
        $this->as(3);
        $this->expectRefusal(fn () => AppCabinService::update(['id' => $cabin, 'summary' => 'レビュアーの版'], 3), 'người dùng #2');
        $this->assertSame('翻訳者の版', json_decode(RevisionStore::open('cabin', $cabin)->fields, true)['summary']);
        // Editing a revision in review sends it back to draft (earlier approvals were for other text).
        Workflow::apply('cabin', $cabin, 'submit', 2);
        $this->as(2);
        AppCabinService::update(['id' => $cabin, 'summary' => '翻訳者の版 2'], 3);
        $this->assertSame('draft', RevisionStore::open('cabin', $cabin)->workflow);
    }

    public function test_en_vi_child_editing_is_unchanged(): void
    {
        $this->as(6);
        AppCabinService::update(['id' => 24, 'room_title' => ['Master bedroom', 'Bathroom'], 'room_description' => ['King bed', 'Tub']], 2);
        $this->assertSame(['Master bedroom', 'Bathroom'], DB::table('app_cabin_room')->where('cabin_id', 24)->orderBy('ord')->pluck('title')->all());
        $this->assertSame(0, DB::table('content_revision')->count());
    }

    public function test_each_workflow_action_needs_its_own_privilege_and_no_self_approval(): void
    {
        $id = $this->cruise;
        SlugService::change('cruise', $id, 'green-ruby-1', 'Set the Japanese URL', 3);
        $this->assertNotNull(Workflow::refusal('cruise', $id, 'submit', 6), 'legacy editor: no translation-edit');
        Workflow::apply('cruise', $id, 'submit', 2);
        $this->assertStringContainsString('translation-approve', Workflow::refusal('cruise', $id, 'approve_translation', 2));
        $this->assertStringContainsString('translation-approve', Workflow::refusal('cruise', $id, 'approve_translation', 5));
        // The reviewer wrote the text: may not approve it.
        DB::table('translation_meta')->where('entity_type', 'cruise')->where('entity_id', $id)->update(['translated_by' => 3]);
        $this->assertStringContainsString('Không tự duyệt', Workflow::refusal('cruise', $id, 'approve_translation', 3));
        config(['i18n.workflow.allow_self_approval' => true]);
        $this->assertNull(Workflow::refusal('cruise', $id, 'approve_translation', 3), 'explicit policy switch');
        config(['i18n.workflow.allow_self_approval' => false]);
        DB::table('translation_meta')->where('entity_type', 'cruise')->where('entity_id', $id)->update(['translated_by' => 2]);
        Workflow::apply('cruise', $id, 'approve_translation', 3);
        Workflow::apply('cruise', $id, 'approve_seo', 3);
        $this->assertStringContainsString('translation-publish', Workflow::refusal('cruise', $id, 'publish', 3));
        Workflow::apply('cruise', $id, 'publish', 5);

        $log = DB::table('editorial_audit_log')->where('entity_type', 'cruise')->where('entity_id', $id)->orderBy('id')->get();
        $this->assertSame(['slug_change', 'submit', 'approve_translation', 'approve_seo', 'publish'], $log->pluck('action')->all());
        $this->assertSame([3, 2, 3, 3, 5], $log->pluck('actor')->map(fn ($a) => (int) $a)->all());
        $this->assertNotNull($log->last()->created_at);
    }

    public function test_routes_follow_the_privileges_and_nobody_gained_one(): void
    {
        // The migration created privileges and roles but assigned nothing: the existing editor role gained nothing.
        foreach (['edit', 'approve', 'seo-approve', 'legal-approve', 'publish'] as $privilege) {
            $this->assertFalse(AdUser::find(6)->hasAccess('translation', $privilege), $privilege);
        }
        $this->assertFalse(AdUser::find(6)->hasAccess('locale', 'update'));
        $this->assertFalse(AdUser::find(6)->hasAccess('slug', 'update'));
        $editorial = DB::table('ad_role')->whereIn('name', array_keys((require database_path('migrations/2026_11_05_000001_add_editorial_permissions_and_audit.php'))::ROLES))->pluck('id');
        $this->assertSame(0, DB::table('ad_role_privilege')->whereIn('role_id', [1, 2])->whereIn('privilege_id', DB::table('ad_privilege')->join('ad_resource', 'ad_resource.id', '=', 'ad_privilege.resource_id')->whereIn('ad_resource.alias', ['translation', 'locale', 'slug'])->pluck('ad_privilege.id'))->count(), 'existing roles untouched');
        $this->assertSame([2, 3, 4, 5, 7], DB::table('ad_user_role')->whereIn('role_id', $editorial)->distinct()->orderBy('user_id')->pluck('user_id')->map(fn ($u) => (int) $u)->all(), 'only what the test assigned');

        $this->as(6); // legacy EN/VI editor: config-update only
        $this->get('/admincp/translation?type=cruise')->assertForbidden();
        $this->get('/admincp/translation?type=cruise&view=pairs')->assertOk();
        $this->get('/admincp/language')->assertForbidden();
        $this->post('/admincp/slug', ['type' => 'cruise', 'id' => $this->cruise, 'slug' => 'x-y', 'reason' => 'Should be refused'])->assertForbidden();

        $this->as(2); // translator
        $this->get('/admincp/translation?type=cruise')->assertOk();
        $this->get('/admincp/translation?type=cruise&view=pairs')->assertForbidden();
        $this->post('/admincp/workflow', ['type' => 'cruise', 'id' => $this->cruise, 'action' => 'submit'])->assertSessionHasNoErrors();
        $this->post('/admincp/workflow', ['type' => 'cruise', 'id' => $this->cruise, 'action' => 'approve_translation'])->assertSessionHasErrors();
        $this->post('/admincp/slug', ['type' => 'cruise', 'id' => $this->cruise, 'slug' => 'x-y', 'reason' => 'Should be refused'])->assertForbidden();
        $this->get('/admincp/language')->assertForbidden();

        $this->as(7); // locale administrator
        $this->get('/admincp/language')->assertOk();
        $this->as(3); // reviewer, also slug-update (Duyệt SEO)
        $this->post('/admincp/slug', ['type' => 'cruise', 'id' => $this->cruise, 'slug' => 'green-ruby-1', 'reason' => 'Set the Japanese URL'])->assertSessionHasNoErrors();
        $this->assertSame('green-ruby-1', DB::table('app_cruise')->where('id', $this->cruise)->value('slug'));
    }

    private function expectRefusal(callable $action, string $message): void
    {
        try {
            $action();
        } catch (\Throwable $e) {
            $this->assertStringContainsString($message, $e->getMessage());

            return;
        }
        $this->fail("Expected a refusal mentioning: {$message}");
    }
}
