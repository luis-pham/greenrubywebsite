<?php

namespace Tests\Feature\Publication;

use App\Services\Content\PathBuilder;
use App\Services\Content\SlugHistory;
use App\Services\Content\SlugValidator;
use App\Services\Content\Slugs;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppCruise;

/**
 * Stable slugs, slug history and PathBuilder (phase 1C-3, owner decision #4; greenruby-web docs/i18n-audit/phase-1/
 * 1c-3-report.md). The fixture-wide PathBuilder check is scripts/i18n/cms/pathbuilder-check.php (213 paths).
 */
class SlugsTest extends I18nContentTestCase
{
    public function test_backfill_stores_todays_derived_slug(): void
    {
        $this->assertSame('green-ruby-1', DB::table('app_cruise')->where('id', 3)->value('slug'));
        $this->assertSame('imperial-suite', DB::table('app_cabin')->where('id', 24)->value('slug'));
    }

    public function test_reader_prefers_the_stored_slug_and_falls_back_to_the_old_derivation(): void
    {
        $this->assertSame('kept', Slugs::of((object) ['slug' => 'kept', 'name' => 'Renamed Ship']));
        $this->assertSame('green-ruby-1', Slugs::of((object) ['slug' => null, 'name' => 'Green Ruby 1']));
        $this->assertSame('hang-sung-sot', Slugs::of(['slug' => '', 'name' => 'Hang Sửng Sốt']));
        $this->assertSame('', Slugs::derive('鍾乳洞'), 'a Japanese name derives no slug');
        $this->assertSame('green-ruby-1', Slugs::forId('app_cruise', 3, 'Snapshot Name'), 'stored slug wins over a snapshot name');
        $this->assertSame('snapshot-name', Slugs::forId('app_cruise', 999, 'Snapshot Name'));
    }

    public function test_slug_is_set_once_and_survives_a_rename(): void
    {
        $ship = AppCruise::create(['language_id' => 2, 'name' => 'Green Ruby 3']);
        $this->assertSame('green-ruby-3', $ship->fresh()->slug);
        $ship->name = 'Green Ruby Explorer';
        $ship->save();
        $this->assertSame('green-ruby-3', $ship->fresh()->slug, 'renaming never changes the URL');
        $this->assertNull(AppCruise::create(['language_id' => 3, 'name' => 'グリーンルビー'])->fresh()->slug, 'no slug from a Japanese name');
        $explicit = new AppCruise();
        $explicit->forceFill(['language_id' => 2, 'name' => 'X', 'slug' => 'explicit'])->save();
        $this->assertSame('explicit', $explicit->fresh()->slug, 'an explicit slug is kept');
    }

    public function test_validator_rules(): void
    {
        $en = ['language_id' => 2];
        $cases = [
            '' => 'slug_empty', 'Sung Sot' => 'slug_format', 'sung--sot' => 'slug_format', '-sung' => 'slug_format',
            'sung-' => 'slug_format', '鍾乳洞' => 'slug_format', 'café' => 'slug_format', str_repeat('a', 161) => 'slug_too_long',
            'faq' => 'slug_reserved', 'blog' => 'slug_reserved', 'api' => 'slug_reserved', 'vi' => 'slug_reserved', 'ja' => 'slug_reserved',
            'page-3' => 'slug_reserved', 'trang-2' => 'slug_reserved', 'dia-diem-tham-quan' => 'slug_reserved',
            'thien-cung-cave' => 'slug_taken', 'sung-sot-grotto' => null,
        ];
        foreach ($cases as $slug => $expected) {
            $this->assertSame($expected, SlugValidator::check('destination', (string) $slug, $en, 1), "'$slug'");
        }
        $this->assertNull(SlugValidator::check('destination', 'sung-sot-cave', $en, 1), 'its own live slug');
        $this->assertNull(SlugValidator::check('destination', 'thien-cung-cave', ['language_id' => 1], 2), 'uniqueness is per language');
        $this->assertNull(SlugValidator::check('cabin', 'imperial-suite', $en, 72), 'id-based URLs need no uniqueness (A-5)');
    }

    public function test_history_redirects_in_one_hop_without_loops_and_never_shadows_a_live_slug(): void
    {
        $change = fn (string $slug) => $this->artisan('content:slug', ['type' => 'destination', 'id' => 1, 'slug' => $slug, '--reason' => 'Phase 1C-3 history test'])->assertSuccessful();
        $change('sung-sot-grotto');
        $change('surprise-cave');
        $this->assertSame('surprise-cave', SlugHistory::movedSlug('destination', 2, 'sung-sot-cave'));
        $this->assertSame('surprise-cave', SlugHistory::movedSlug('destination', 2, 'sung-sot-grotto'), 'one hop, never a chain');
        $change('sung-sot-cave'); // back to the first slug: A → B → C → A
        $this->assertNull(SlugHistory::movedSlug('destination', 2, 'sung-sot-cave'), 'a live slug is never redirected (no loop)');
        $this->assertSame('sung-sot-cave', SlugHistory::movedSlug('destination', 2, 'surprise-cave'));
        $this->assertNull(SlugHistory::movedSlug('destination', 1, 'surprise-cave'), 'history is per language');
        $this->assertSame('slug_taken', SlugValidator::check('destination', 'surprise-cave', ['language_id' => 2], 17), 'another place may not take an old slug that still redirects');
        $this->assertSame(3, DB::table('app_slug_history')->where('entity_type', 'destination')->where('entity_id', 1)->count());
    }

    public function test_slug_command_refuses_invalid_changes_and_supports_dry_run(): void
    {
        $this->artisan('content:slug', ['type' => 'destination', 'id' => 1, 'slug' => 'faq', '--reason' => 'Phase 1C-3 command test'])->assertFailed();
        $this->artisan('content:slug', ['type' => 'destination', 'id' => 1, 'slug' => 'new-slug'])->assertFailed(); // no reason
        $this->artisan('content:slug', ['type' => 'nope', 'id' => 1, 'slug' => 'x', '--reason' => 'Phase 1C-3 command test'])->assertFailed();
        $this->artisan('content:slug', ['type' => 'destination', 'id' => 1, 'slug' => 'new-slug', '--reason' => 'Phase 1C-3 command test', '--dry-run' => true])->assertSuccessful();
        $this->assertSame('sung-sot-cave', DB::table('app_destination')->where('id', 1)->value('slug'));
        $this->assertSame(0, DB::table('app_slug_history')->count());
    }

    public function test_path_builder_keeps_en_vi_and_gives_intl_languages_english_segments(): void
    {
        DB::table('ad_language')->where('code', 'ja')->update(['url_prefix' => 'ja']);
        \App\Services\Locale\LocaleRegistry::flush();
        $this->assertSame('/', PathBuilder::home('en'));
        $this->assertSame('/vi', PathBuilder::home('vi'));
        $this->assertSame('/ja', PathBuilder::home('ja'));
        $this->assertSame('/cruise/green-ruby-1-3.html', PathBuilder::cruise('en', 'green-ruby-1', 3));
        $this->assertSame('/vi/du-thuyen/green-ruby-1-1.html', PathBuilder::cruise('vi', 'green-ruby-1', 1));
        $this->assertSame('/ja/cruise/green-ruby-1-1003.html', PathBuilder::cruise('ja', 'green-ruby-1', 1003));
        $this->assertSame('/ja/faq', PathBuilder::section('ja', 'faq'), 'never the Vietnamese segment');
        $this->assertSame('/vi/cau-hoi-thuong-gap', PathBuilder::section('vi', 'faq'));
        $this->assertSame('/ja/itinerary/x-3-3.html', PathBuilder::itinerary('ja', 'x', 3, 3));
        $this->assertSame('/vi/tin-tuc/a.html', PathBuilder::article('vi', 'tin-tuc', 'a'));
        $this->assertSame('/ja/faq', PathBuilder::normalizeIntlPath('/ja/cau-hoi-thuong-gap', 'ja'));
        $this->assertSame('/ja/about-us/brand-facts', PathBuilder::normalizeIntlPath('/ja/gioi-thieu/thong-tin-thuong-hieu', 'ja'), 'longest segment first');
        $this->assertSame('/ja/cruise/x-1003.html', PathBuilder::normalizeIntlPath('/ja/du-thuyen/x-1003.html', 'ja'));
        $this->assertSame('/vi/cau-hoi-thuong-gap', PathBuilder::normalizeIntlPath('/vi/cau-hoi-thuong-gap', 'vi'), 'legacy paths untouched');
    }
}
