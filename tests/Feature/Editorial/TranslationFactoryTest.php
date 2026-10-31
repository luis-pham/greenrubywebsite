<?php

namespace Tests\Feature\Editorial;

use App\Services\Content\BusinessFields;
use App\Services\Content\ContentTranslations;
use App\Services\Content\Freshness;
use App\Services\Content\TranslationFactory;
use App\Services\Locale\LocaleRegistry;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppCabin;
use Modules\BackEnd\Services\AppCabinService;
use Modules\BackEnd\Services\AppCruiseService;

/** Phase 1E-b: "Create Japanese translation", business-field protection, freshness, locale seeding. */
class TranslationFactoryTest extends EditorialTestCase
{
    private function create(string $type, int $id): int
    {
        return TranslationFactory::create($type, $id, $this->ja(), 1)['id'];
    }

    public function test_cruise_translation_is_an_unpublished_text_draft_joined_to_the_en_vi_group(): void
    {
        $pricesBefore = DB::table('app_cabin_price')->get()->toArray();
        $id = $this->create('cruise', 3);
        $row = DB::table('app_cruise')->find($id);
        $this->assertSame([3, 'Green Ruby 1', 'EN body', 'unpublished', null], [(int) $row->language_id, $row->name, $row->content, $row->publish_state, $row->slug]);
        $this->assertEquals([2 => 3, 1 => 1, 3 => $id], $this->groupOf('cruise', 3), 'EN/VI members untouched, ja added');
        $this->assertEquals([1 => 1, 2 => 3, 3 => $id], $this->groupOf('cruise', 1));
        $meta = DB::table('translation_meta')->where('entity_type', 'cruise')->where('entity_id', $id)->first();
        $this->assertSame(['draft', 3, 2], [$meta->workflow, (int) $meta->source_entity_id, (int) $meta->source_language_id]);
        $this->assertSame(Freshness::hash('cruise', 3), $meta->source_hash);
        $this->assertSame(['attach'], DB::table('content_translation_log')->pluck('action')->all());
        $this->assertEquals($pricesBefore, DB::table('app_cabin_price')->get()->toArray(), 'no inventory written');
    }

    public function test_cabin_needs_its_cruise_and_group_translated_first_and_never_gets_prices(): void
    {
        $this->expectRefusal(fn () => $this->create('cabin', 24), 'Du thuyền #3');
        $cruise = $this->create('cruise', 3);
        $this->expectRefusal(fn () => $this->create('cabin', 24), 'i18n:seed-locale ja');
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
        $cabin = $this->create('cabin', 24);
        $row = DB::table('app_cabin')->find($cabin);
        $groupJa = DB::table('app_group')->where('language_id', 3)->where('slug', 'accommodation')->value('id');
        $this->assertSame([$cruise, (int) $groupJa, 'unpublished'], [(int) $row->cruise_id, (int) $row->group_id, $row->publish_state]);
        $this->assertSame(['Bedroom'], DB::table('app_cabin_room')->where('cabin_id', $cabin)->pluck('title')->all());
        $this->assertSame(0, DB::table('app_cabin_price')->where('cabin_id', $cabin)->count());
        $this->assertEquals([2 => 24, 1 => 38, 3 => $cabin], $this->groupOf('cabin', 24));
    }

    public function test_itinerary_copies_days_and_only_a_past_relation_marker_never_departures(): void
    {
        $cruise = $this->create('cruise', 3);
        $id = $this->create('itinerary', 3);
        $this->assertSame(['Boarding'], DB::table('app_itinerary_day_detail')->join('app_itinerary_day', 'app_itinerary_day.id', '=', 'itinerary_day_id')->where('itinerary_id', $id)->pluck('title')->all());
        $relations = DB::table('app_cruise_itinerary')->where('itinerary_id', $id)->get();
        $this->assertCount(1, $relations);
        $this->assertSame([$cruise, TranslationFactory::RELATION_MARKER], [(int) $relations[0]->cruise_id, $relations[0]->start_at]);
        $this->assertSame(3, DB::table('app_cruise_itinerary')->count() - 1, 'EN/VI departures unchanged');
    }

    public function test_article_needs_its_category_and_the_category_joins_the_nested_set(): void
    {
        $this->expectRefusal(fn () => $this->create('article', 100), 'Chuyên mục bài viết #10');
        $category = $this->create('article_category', 10);
        $row = DB::table('app_category')->find($category);
        $root = DB::table('app_category')->find($row->parent_id);
        $this->assertSame(['root', 3, 'unpublished', ''], [$root->slug, (int) $root->language_id, $row->publish_state, $row->slug]);
        $this->assertTrue($row->lft > $root->lft && $row->rgt < $root->rgt);
        $article = $this->create('article', 100);
        $a = DB::table('app_article')->find($article);
        $this->assertSame([$category, 0, 'Best time to visit'], [(int) $a->category_id, (int) $a->is_published, $a->title]);
    }

    public function test_faq_is_tracked_through_translation_meta_and_needs_its_group(): void
    {
        $this->expectRefusal(fn () => $this->create('faq', 1), 'Nhóm câu hỏi thường gặp #57');
        $group = $this->create('faq_group', 57);
        $faq = $this->create('faq', 1);
        $this->assertSame([$group, 'unpublished'], [(int) DB::table('app_faq')->where('id', $faq)->value('group_id'), DB::table('app_faq')->where('id', $faq)->value('publish_state')]);
        $this->assertFalse(ContentTranslations::isLinked('faq_group', 999));
        $this->assertSame($faq, TranslationFactory::existingTranslation('faq', 1, 3));
        $this->expectRefusal(fn () => $this->create('faq', 1), 'đã có bản ja');
    }

    public function test_refusals_keep_en_vi_pairing_and_disabled_languages_out(): void
    {
        $this->expectRefusal(fn () => TranslationFactory::create('cruise', 3, LocaleRegistry::byCode('vi'), 1), 'EN/VI giữ cách ghép cũ');
        $this->expectRefusal(fn () => TranslationFactory::create('cruise', 3, LocaleRegistry::byCode('ko'), 1), 'EN/VI giữ cách ghép cũ');
        DB::table('app_cruise')->insert(['id' => 9, 'language_id' => 2, 'name' => 'Lone', 'summary' => 's', 'image_link' => 'x', 'capacity' => 1, 'total_floor' => 1, 'dimension_length' => 1, 'year_built' => 2020, 'created_at' => now(), 'created_by' => 1]);
        $this->expectRefusal(fn () => $this->create('cruise', 9), 'chưa thuộc nhóm');
        $this->assertFalse(ContentTranslations::isLinked('cruise', 9), 'the factory never creates a group for an unpaired EN row');
        $this->create('cruise', 3);
        $this->expectRefusal(fn () => $this->create('cruise', 1), 'đã có bản ja');
        $this->assertSame(0, DB::table('app_cruise')->where('language_id', 3)->count() - 1);
    }

    public function test_business_fields_of_a_text_only_language_are_read_only_server_side(): void
    {
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
        $cruise = $this->create('cruise', 3);
        $cabin = $this->create('cabin', 24);

        $model = AppCabin::withoutGlobalScopes()->find($cabin);
        $model->capacity = 9;
        $this->expectRefusal(fn () => $model->save(), 'capacity');
        $model = AppCabin::withoutGlobalScopes()->find($cabin);
        $model->name = 'インペリアルスイート';
        $model->save();
        $this->assertSame('インペリアルスイート', DB::table('app_cabin')->where('id', $cabin)->value('name'), 'text stays editable');

        $this->expectRefusal(fn () => AppCabinService::update(['id' => $cabin, 'price' => [2 => [2 => 1]]], 3), 'bảng giá cabin');
        $this->expectRefusal(fn () => AppCruiseService::storeItinerary($cruise, 3, ['itinerary_id' => 3, 'start_at' => '2027-05-01']), 'lịch khởi hành');
        $this->assertSame(0, DB::table('app_cabin_price')->where('cabin_id', $cabin)->count());

        // en/vi unaffected.
        $en = AppCabin::withoutGlobalScopes()->find(24);
        $en->capacity = 4;
        $en->save();
        $this->assertSame(4.0, (float) DB::table('app_cabin')->where('id', 24)->value('capacity'));

        // Reading: ja shows the EN values whatever its mirror holds.
        DB::table('app_cabin')->where('id', $cabin)->update(['capacity' => 99]);
        $this->assertSame(4.0, (float) BusinessFields::overlay('cabin', AppCabin::withoutGlobalScopes()->find($cabin))->capacity);
        $this->assertSame(99.0, (float) AppCabin::withoutGlobalScopes()->find($cabin)->capacity, 'overlay returns a clone');
        $this->assertSame(BusinessFields::overlay('cabin', $en), $en, 'en/vi rows unchanged');
    }

    public function test_freshness_follows_source_text_not_business_data(): void
    {
        $id = $this->create('cruise', 3);
        $this->assertSame('current', Freshness::status('cruise', $id));
        DB::table('app_cruise')->where('id', 3)->update(['capacity' => 80]);
        $this->assertSame('current', Freshness::status('cruise', $id), 'business data is not text');
        DB::table('app_cruise')->where('id', 3)->update(['summary' => 'Ha Long eco-luxury, rewritten']);
        $this->assertSame('outdated', Freshness::status('cruise', $id));
        $this->assertSame('unpublished', DB::table('app_cruise')->where('id', $id)->value('publish_state'), 'freshness never changes publication');
        Freshness::confirm('cruise', $id, 1);
        $this->assertSame('current', Freshness::status('cruise', $id));
        $this->assertSame('legacy', Freshness::status('cruise', 1), 'VI rows have no workflow');
    }

    public function test_seed_locale_creates_hidden_pages_groups_and_config_once(): void
    {
        $this->artisan('i18n:seed-locale', ['code' => 'ja', '--dry-run' => true])->assertSuccessful();
        $this->assertSame(0, DB::table('app_page')->where('language_id', 3)->count(), 'dry run writes nothing');
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
        $page = DB::table('app_page')->where('language_id', 3)->where('code', 'homepage')->first();
        $this->assertSame(0, (int) $page->is_visible);
        $this->assertSame(['Sail Ha Long'], DB::table('app_page_config')->where('page_id', $page->id)->pluck('value')->all());
        $this->assertSame(['accommodation'], DB::table('app_group')->where('language_id', 3)->pluck('slug')->all(), 'structural groups only; FAQ groups are content');
        $this->assertSame(['website-name-ja'], DB::table('ad_config')->where('language_id', 3)->pluck('key')->all(), 'per-language keys get the new suffix; shared keys are not copied');
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
        $this->assertSame(1, DB::table('app_page')->where('language_id', 3)->count(), 'idempotent');
        $this->artisan('i18n:seed-locale', ['code' => 'vi'])->assertFailed();
        $this->artisan('i18n:seed-locale', ['code' => 'ko'])->assertFailed();
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
