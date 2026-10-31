<?php

namespace Tests\Feature\Publication;

use App\Services\Locale\LocaleRegistry;
use App\Services\Publication\PublicationContext;
use App\Services\Publication\PublicationPolicy;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Bootstrap\BootProviders;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\BackEnd\Entities\AppCabin;
use Modules\BackEnd\Entities\AppCruise;
use Modules\BackEnd\Entities\AppFaq;

/**
 * Row-level publication (phase 1C-2, greenruby-web docs/i18n-audit/phase-1/1c-2-report.md): public reads hide
 * unpublished rows and rows of unpublished parents; the admin/CLI see everything; workflow gates publishing for
 * workflow-controlled languages; en/vi rows are never touched by the guard.
 */
class PublicationTest extends I18nContentTestCase
{
    private function ids($models): array
    {
        return collect($models)->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
    }

    public function test_migration_leaves_every_existing_row_published(): void
    {
        foreach (['app_cruise', 'app_cabin', 'app_itinerary', 'app_exp_activity', 'app_faq', 'app_group', 'app_category'] as $table) {
            $this->assertSame(0, DB::table($table)->where('publish_state', '<>', 'published')->count(), $table);
        }
        $this->assertSame([3, 4], $this->ids(PublicationContext::asPublic(fn () => AppCruise::all())));
    }

    public function test_public_reads_hide_unpublished_rows_and_children_but_not_orphans(): void
    {
        DB::table('app_cruise')->where('id', 3)->update(['publish_state' => 'unpublished']);

        $this->assertSame([3, 4], $this->ids(AppCruise::all()), 'admin/CLI (no public context) see everything');
        $this->assertSame([24, 72, 99], $this->ids(AppCabin::all()));

        PublicationContext::asPublic(function () {
            $this->assertSame([4], $this->ids(AppCruise::all()));
            $this->assertNull(AppCruise::find(3));
            $this->assertSame([72, 99], $this->ids(AppCabin::all()), 'cabin of an unpublished cruise hidden; orphan kept');
            $this->assertNull(AppCabin::find(24)?->cruise, 'relations are scoped too');
        });
    }

    public function test_faq_of_an_unpublished_group_is_hidden(): void
    {
        DB::table('app_group')->where('id', 57)->update(['publish_state' => 'unpublished']);
        $this->assertSame([2], $this->ids(PublicationContext::asPublic(fn () => AppFaq::all())));
    }

    public function test_raw_queries_and_post_filters_follow_the_same_rule_and_keep_order(): void
    {
        $rows = collect([(object) ['id' => 72, 'cruise_id' => 4], (object) ['id' => 24, 'cruise_id' => 3], (object) ['id' => 99, 'cruise_id' => 0]]);
        PublicationContext::asPublic(function () use ($rows) {
            $this->assertSame($rows, PublicationPolicy::withoutUnpublished($rows, 'app_cabin', 'id', 'cruise_id'), 'nothing hidden: the same object, order untouched');
        });
        DB::table('app_cruise')->where('id', 3)->update(['publish_state' => 'unpublished']);
        $this->assertSame($rows, PublicationPolicy::withoutUnpublished($rows, 'app_cabin', 'id', 'cruise_id'), 'outside a public read: untouched');
        PublicationContext::asPublic(function () use ($rows) {
            $this->assertSame([72, 99], PublicationPolicy::withoutUnpublished($rows, 'app_cabin', 'id', 'cruise_id')->pluck('id')->all());
            $raw = PublicationPolicy::wherePublished(DB::table('app_cabin'), ['app_cabin'])->orderBy('id')->pluck('id')->map(fn ($i) => (int) $i)->all();
            $this->assertSame([72, 99], $raw);
        });
    }

    public function test_is_visible_combines_locale_flag_and_parent(): void
    {
        $en = (object) ['id' => 72, 'language_id' => 2, 'cruise_id' => 4, 'publish_state' => 'published'];
        $this->assertTrue(PublicationPolicy::isVisible('cabin', $en));
        $this->assertFalse(PublicationPolicy::isVisible('cabin', (object) ['language_id' => 3, 'cruise_id' => 4, 'publish_state' => 'published']), 'ja is editing');
        $this->assertFalse(PublicationPolicy::isVisible('cabin', (object) ['language_id' => 2, 'cruise_id' => 4, 'publish_state' => 'unpublished']));
        DB::table('app_cruise')->where('id', 4)->update(['publish_state' => 'unpublished']);
        $this->assertFalse(PublicationPolicy::isVisible('cabin', $en), 'parent cruise unpublished');
        $this->assertTrue(PublicationPolicy::isVisible('article', (object) ['language_id' => 2, 'is_published' => 1, 'publish_date' => null, 'category_id' => null]));
        $this->assertFalse(PublicationPolicy::isVisible('article', (object) ['language_id' => 2, 'is_published' => 1, 'publish_date' => '2999-01-01', 'category_id' => null]), 'scheduled');
        $this->assertFalse(PublicationPolicy::isVisible('destination', (object) ['language_id' => 2, 'status' => 'review']));
    }

    public function test_workflow_gates_publishing_for_controlled_languages_only(): void
    {
        $en = AppCruise::create(['language_id' => 2, 'name' => 'New EN ship']);
        $this->assertSame('published', $en->fresh()->publish_state, 'en: legacy behaviour, untouched');
        $en->publish_state = 'unpublished';
        $en->save();
        $en->publish_state = 'published';
        $en->save();
        $this->assertSame('published', $en->fresh()->publish_state, 'en can be republished without workflow');

        $ja = AppCruise::create(['language_id' => 3, 'name' => 'グリーンルビー', 'publish_state' => 'published']);
        $this->assertSame('unpublished', $ja->fresh()->publish_state, 'ja rows are created unpublished');

        $ja->publish_state = 'published';
        try {
            $ja->save();
            $this->fail('expected the publish guard');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('not approved', $e->getMessage());
        }
        $this->assertSame('unpublished', $ja->fresh()->publish_state);

        DB::table('translation_meta')->insert(['entity_type' => 'cruise', 'entity_id' => $ja->id, 'language_id' => 3, 'workflow' => 'approved', 'updated_at' => now()]);
        DB::table('app_cruise')->where('id', $ja->id)->update(['slug' => 'green-ruby-ja']); // 1C-3: a valid slug is required too
        $ja = AppCruise::find($ja->id);
        $ja->publish_state = 'published';
        $ja->save();
        $this->assertSame('published', $ja->fresh()->publish_state, 'approved translation may be published');
    }

    public function test_legal_types_also_need_legal_approval(): void
    {
        $faq = AppFaq::create(['language_id' => 3, 'group_id' => 57, 'question' => '予約']);
        DB::table('translation_meta')->insert(['entity_type' => 'faq', 'entity_id' => $faq->id, 'language_id' => 3, 'workflow' => 'approved', 'updated_at' => now()]);
        $faq = AppFaq::find($faq->id);
        $faq->publish_state = 'published';
        $this->expectExceptionMessage('legal approval is missing');
        $faq->save();
    }

    public function test_publish_command_uses_the_guard(): void
    {
        $this->artisan('content:publish', ['type' => 'cruise', 'id' => 3, 'state' => 'unpublished', '--reason' => 'Phase 1C-2 command test'])->assertSuccessful();
        $this->assertSame('unpublished', DB::table('app_cruise')->where('id', 3)->value('publish_state'));
        $this->artisan('content:publish', ['type' => 'cruise', 'id' => 3, 'state' => 'published'])->assertFailed(); // no reason
        $this->artisan('content:publish', ['type' => 'article', 'id' => 1, 'state' => 'published', '--reason' => 'Phase 1C-2 command test'])->assertFailed();
        $ja = AppCruise::create(['language_id' => 3, 'name' => 'ja']);
        $this->artisan('content:publish', ['type' => 'cruise', 'id' => $ja->id, 'state' => 'published', '--reason' => 'Phase 1C-2 command test'])->assertFailed();
    }
}
