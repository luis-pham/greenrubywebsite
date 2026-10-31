<?php

namespace Tests\Feature\Editorial;

use App\Services\Content\JsonPath;
use App\Services\Content\TranslationFactory;
use Illuminate\Support\Facades\DB;

/** Phase 2B: i18n:import-drafts (CMS configuration drafts into the hidden ja rows). */
class ImportDraftsTest extends EditorialTestCase
{
    private string $dir;
    private array $sources = [];
    private array $drafts = [];

    protected function setUp(): void
    {
        parent::setUp();
        (require database_path('migrations/2026_11_04_000001_create_content_revision_and_preview_tables.php'))->up();
        $menu = json_encode([
            ['id' => 'a1', 'name' => 'Home', 'url' => '/', 'ord' => 1, 'parent_id' => null],
            ['id' => 'b2', 'name' => 'Cruises', 'url' => '/cruise', 'ord' => 2, 'parent_id' => null],
        ], JSON_UNESCAPED_SLASHES);
        DB::table('app_menu_front_end')->insert(['id' => 2, 'language_id' => 2, 'code' => 'primary-menu', 'name' => 'Primary', 'menu' => $menu, 'created_at' => now(), 'created_by' => 1]);
        DB::table('app_page_config')->insert(['id' => 50, 'page_id' => 1, 'section_id' => 1, 'label' => 'Facts', 'key' => 'home-facts', 'type' => 6, 'ord' => 2,
            'value' => json_encode([['id' => 1372, 'title' => 'Solar Power', 'link' => '/a.jpg'], ['id' => 1373, 'title' => 'Hybrid engines', 'link' => '/b.jpg']], JSON_UNESCAPED_SLASHES)]);
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
        $this->dir = sys_get_temp_dir() . '/import-drafts-' . uniqid();
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir . '/*') ?: []);
        @rmdir($this->dir);
        parent::tearDown();
    }

    private function draft(string $table, int $rowId, string $field, string $en, string $ja, string $path = '', string $status = 'PENDING_REVIEW'): string
    {
        $id = "CMS-{$table}-{$rowId}-{$field}" . ($path ? "-{$path}" : '');
        $this->sources[] = ['id' => $id, 'table' => $table, 'row_id' => $rowId, 'field' => $field, 'json_path' => $path, 'en' => $en, 'scope' => 'in', 'reason' => ''];
        $this->drafts[] = ['id' => $id, 'ja' => $ja, 'status' => $status, 'risk' => '', 'note' => ''];

        return $id;
    }

    /** Runs the command; returns [exit code, report rows keyed by id, counts]. */
    private function import(array $options = []): array
    {
        file_put_contents("{$this->dir}/source.json", json_encode(['rows' => $this->sources]));
        file_put_contents("{$this->dir}/drafts.json", json_encode(['rows' => $this->drafts]));
        $report = "{$this->dir}/report-" . uniqid() . '.json';
        $code = \Illuminate\Support\Facades\Artisan::call('i18n:import-drafts', ['code' => 'ja', '--source' => "{$this->dir}/source.json",
            '--drafts' => "{$this->dir}/drafts.json", '--report' => $report] + $options);
        if (!is_file($report)) {
            return [$code, [], []];
        }
        $data = json_decode(file_get_contents($report), true);

        return [$code, collect($data['rows'])->keyBy('id')->all(), $data['counts']];
    }

    private function jaPage(): object
    {
        return DB::table('app_page')->where('language_id', 3)->where('code', 'homepage')->first();
    }

    private function jaConfig(string $key): object
    {
        return DB::table('app_page_config')->where('page_id', $this->jaPage()->id)->where('key', $key)->first();
    }

    private function snapshot(): array
    {
        $out = [];
        foreach (['app_page', 'app_page_config', 'ad_config', 'app_menu_front_end', 'app_group', 'translation_meta', 'editorial_audit_log', 'content_revision', 'content_translations'] as $table) {
            $out[$table] = DB::table($table)->get()->map(fn ($r) => (array) $r)->all();
        }

        return $out;
    }

    public function test_dry_run_writes_nothing_and_reports_what_it_would_do(): void
    {
        $title = $this->draft('app_page', 1, 'title:homepage', 'Home', 'ホーム');
        $config = $this->draft('ad_config', 1, 'website-name-en', 'Green Ruby Cruises', 'グリーンルビー');
        $before = $this->snapshot();
        [$code, $rows, $counts] = $this->import();
        $this->assertSame(0, $code);
        $this->assertSame(['updated', 'updated'], [$rows[$title]['status'], $rows[$config]['status']]);
        $this->assertSame(2, $counts['updated']);
        $this->assertEquals($before, $this->snapshot(), 'a dry run writes nothing');
    }

    public function test_apply_updates_an_unpublished_seeded_row_with_audit_and_translator(): void
    {
        $title = $this->draft('app_page', 1, 'title:homepage', 'Home', 'ホーム');
        $banner = $this->draft('app_page_config', 1, 'banner_title', 'Sail Ha Long', 'ハロン湾をクルーズ');
        $site = $this->draft('ad_config', 1, 'website-name-en', 'Green Ruby Cruises', 'グリーンルビー・クルーズ');
        $item = $this->draft('app_page_config', 50, 'home-facts', 'Solar Power', '太陽光発電', '[1372].title');
        [, $rows, $counts] = $this->import(['--apply' => true, '--actor' => 2]);
        $this->assertSame(4, $counts['updated'], json_encode($rows));
        $this->assertSame('ホーム', $this->jaPage()->title);
        $this->assertSame(0, (int) $this->jaPage()->is_visible, 'publication untouched');
        $this->assertSame('ハロン湾をクルーズ', $this->jaConfig('banner_title')->value);
        $this->assertSame('グリーンルビー・クルーズ', DB::table('ad_config')->where('key', 'website-name-ja')->value('value'));
        $facts = json_decode($this->jaConfig('home-facts')->value, true);
        $this->assertSame([['id' => 1372, 'title' => '太陽光発電', 'link' => '/a.jpg'], ['id' => 1373, 'title' => 'Hybrid engines', 'link' => '/b.jpg']], $facts, 'only that item field');
        $this->assertSame('Home', DB::table('app_page')->find(1)->title, 'English untouched');
        $meta = DB::table('translation_meta')->where('entity_type', 'page')->where('entity_id', $this->jaPage()->id)->first();
        $this->assertSame([2, 'draft'], [(int) $meta->translated_by, $meta->workflow]);
        $audit = DB::table('editorial_audit_log')->where('action', 'draft_import')->orderBy('id')->get();
        $this->assertCount(4, $audit);
        $this->assertEqualsCanonicalizing([$title, $banner, $site, $item], $audit->pluck('detail')->all());
        $this->assertSame([2], $audit->pluck('actor')->map(fn ($a) => (int) $a)->unique()->values()->all());
        $this->assertSame(0, DB::table('content_revision')->count());
    }

    public function test_conflict_when_the_english_source_changed(): void
    {
        $id = $this->draft('app_page_config', 1, 'banner_title', 'Sail Ha Long', 'ハロン湾をクルーズ');
        DB::table('app_page_config')->where('id', 1)->update(['value' => 'Sail Lan Ha Bay']);
        $gone = $this->draft('app_page_config', 50, 'home-facts', 'Wind', '風力', '[9999].title');
        [, $rows, $counts] = $this->import(['--apply' => true]);
        $this->assertSame('conflict', $rows[$id]['status']);
        $this->assertStringContainsString('English source changed', $rows[$id]['reason']);
        $this->assertSame('conflict', $rows[$gone]['status']);
        $this->assertSame(2, $counts['conflict']);
        $this->assertSame('Sail Ha Long', $this->jaConfig('banner_title')->value, 'ja untouched');
        $this->assertSame(0, DB::table('editorial_audit_log')->where('action', 'draft_import')->count());
    }

    public function test_second_run_is_idempotent(): void
    {
        $this->draft('app_page', 1, 'title:homepage', 'Home', 'ホーム');
        $this->draft('app_menu_front_end', 2, 'menu:primary-menu', 'Home', 'ホーム', '[a1].name');
        $this->draft('ad_config', 1, 'website-name-en', 'Green Ruby Cruises', 'Green Ruby Cruises'); // brand: equal already
        [, , $first] = $this->import(['--apply' => true, '--actor' => 2]);
        $this->assertSame(['updated' => 2, 'skipped' => 1], array_filter($first));
        $after = $this->snapshot();
        [, $rows, $second] = $this->import(['--apply' => true, '--actor' => 2]);
        $this->assertSame(['skipped' => 3], array_filter($second));
        $this->assertStringContainsString('unchanged', reset($rows)['reason']);
        $this->assertEquals($after, $this->snapshot(), 'nothing written the second time');
    }

    public function test_published_target_gets_a_pending_revision_or_is_refused(): void
    {
        $group = TranslationFactory::create('faq_group', 57, $this->ja(), 1)['id'];
        DB::table('app_group')->where('id', $group)->update(['publish_state' => 'published']); // fixture: an already published translation
        DB::table('translation_meta')->where('entity_type', 'faq_group')->where('entity_id', $group)->update(['workflow' => 'approved']);
        $faq = $this->draft('app_group', 57, 'name:faq_group', 'Bookings', 'ご予約');
        DB::table('app_page')->where('id', $this->jaPage()->id)->update(['is_visible' => 1]);
        $page = $this->draft('app_page', 1, 'title:homepage', 'Home', 'ホーム');
        $structural = $this->draft('app_group', 29, 'name:group_type2', 'Accommodation', '客室');

        [, $rows] = $this->import(['--apply' => true, '--actor' => 2]);
        $this->assertSame('updated', $rows[$faq]['status']);
        $this->assertSame('Bookings', DB::table('app_group')->find($group)->name, 'live text unchanged');
        $revision = DB::table('content_revision')->where('entity_type', 'faq_group')->where('entity_id', $group)->first();
        $this->assertSame(['ご予約', 'draft', 2], [json_decode($revision->fields, true)['name'], $revision->workflow, (int) $revision->created_by]);
        $this->assertSame((int) $revision->id, $rows[$faq]['revision_id']);
        $this->assertSame(['published', 'approved'], [DB::table('app_group')->find($group)->publish_state,
            DB::table('translation_meta')->where('entity_type', 'faq_group')->where('entity_id', $group)->value('workflow')]);
        $this->assertSame((int) $revision->id, (int) DB::table('editorial_audit_log')->where('action', 'draft_import')->value('revision_id'));

        $this->assertSame('needs_review', $rows[$page]['status']);
        $this->assertStringContainsString('target is public', $rows[$page]['reason']);
        $this->assertSame('Home', $this->jaPage()->title);
        // Phase 2C: seeded structural groups are unpublished drafts (Workflow type 'group'): imported directly.
        $this->assertSame('updated', $rows[$structural]['status'], json_encode($rows[$structural]));
        $jaGroup = TranslationFactory::structuralGroup(29, 3);
        $this->assertSame(['客室', 'unpublished'], [DB::table('app_group')->find($jaGroup)->name, DB::table('app_group')->find($jaGroup)->publish_state]);
        // A published structural group is never edited live by the importer.
        DB::table('app_group')->where('id', $jaGroup)->update(['publish_state' => 'published', 'name' => 'Accommodation']);
        [, $published] = $this->import(['--apply' => true, '--actor' => 2]);
        $this->assertSame('needs_review', $published[$structural]['status']);
        $this->assertStringContainsString('target is public', $published[$structural]['reason']);
        $this->assertSame('Accommodation', DB::table('app_group')->find($jaGroup)->name);

        [, $again] = $this->import(['--apply' => true, '--actor' => 2]);
        $this->assertSame('skipped', $again[$faq]['status'], 'the pending revision already holds the draft');
        $this->assertSame(1, DB::table('content_revision')->count());
    }

    public function test_flagged_drafts_are_skipped_unless_included_and_needs_context_never(): void
    {
        $commercial = $this->draft('app_page', 1, 'title:homepage', 'Home', 'ホーム', '', 'COMMERCIAL_REVIEW_REQUIRED');
        $context = $this->draft('app_page_config', 1, 'banner_title', 'Sail Ha Long', 'ハロン湾', '', 'NEEDS_CONTEXT');
        [, $rows] = $this->import(['--apply' => true]);
        $this->assertSame(['needs_review', 'needs_review'], [$rows[$commercial]['status'], $rows[$context]['status']]);
        $this->assertSame('Home', $this->jaPage()->title);
        [, $rows] = $this->import(['--apply' => true, '--include-flagged' => true]);
        $this->assertSame(['updated', 'needs_review'], [$rows[$commercial]['status'], $rows[$context]['status']]);
        $this->assertSame('Sail Ha Long', $this->jaConfig('banner_title')->value);
    }

    public function test_menu_item_name_is_updated_and_url_and_structure_kept(): void
    {
        $id = $this->draft('app_menu_front_end', 2, 'menu:primary-menu', 'Cruises', 'クルーズ', '[b2].name');
        [, $rows] = $this->import(['--apply' => true]);
        $this->assertSame('updated', $rows[$id]['status']);
        $menu = json_decode(DB::table('app_menu_front_end')->where('language_id', 3)->where('code', 'primary-menu')->value('menu'), true);
        $this->assertSame([
            ['id' => 'a1', 'name' => 'Home', 'url' => '/', 'ord' => 1, 'parent_id' => null],
            ['id' => 'b2', 'name' => 'クルーズ', 'url' => '/cruise', 'ord' => 2, 'parent_id' => null],
        ], $menu);
        $this->assertSame('Cruises', JsonPath::get(DB::table('app_menu_front_end')->find(2)->menu, '[b2].name'), 'English menu untouched');
    }

    public function test_missing_target_and_create_missing_through_the_factory(): void
    {
        $faq = $this->draft('app_group', 57, 'name:faq_group', 'Bookings', 'ご予約');
        $amenity = $this->draft('app_amenity', 7, 'name', 'Wi-Fi', 'Wi-Fi（無料）');
        [, $rows] = $this->import(['--apply' => true]);
        $this->assertSame(['missing_target', 'missing_target'], [$rows[$faq]['status'], $rows[$amenity]['status']]);
        $this->assertStringContainsString('translation matrix', $rows[$faq]['reason']);
        $this->assertSame(0, DB::table('app_group')->where('language_id', 3)->where('type', 1)->count(), 'nothing created');

        [, $dry] = $this->import(['--create-missing' => true]);
        $this->assertSame('created', $dry[$faq]['status']);
        $this->assertSame(0, DB::table('app_group')->where('language_id', 3)->where('type', 1)->count(), 'dry run creates nothing');

        [, $rows] = $this->import(['--apply' => true, '--create-missing' => true, '--actor' => 2]);
        $this->assertSame(['created', 'created'], [$rows[$faq]['status'], $rows[$amenity]['status']]);
        $row = DB::table('app_group')->find($rows[$faq]['target']['id']);
        $this->assertSame(['ご予約', 'unpublished', 3], [$row->name, $row->publish_state, (int) $row->language_id]);
        $this->assertEquals([2 => 57, 1 => 58, 3 => $row->id], $this->groupOf('faq_group', 57), 'linked by the factory');
        $this->assertSame(2, (int) DB::table('translation_meta')->where('entity_type', 'faq_group')->where('entity_id', $row->id)->value('translated_by'));
        [, $again, $counts] = $this->import(['--apply' => true, '--create-missing' => true, '--actor' => 2]);
        $this->assertSame(['skipped' => 2], array_filter($counts));
    }

    public function test_a_japanese_target_already_edited_is_not_overwritten(): void
    {
        DB::table('app_page')->where('id', $this->jaPage()->id)->update(['title' => '手書きのタイトル']);
        $id = $this->draft('app_page', 1, 'title:homepage', 'Home', 'ホーム');
        [, $rows] = $this->import(['--apply' => true]);
        $this->assertSame('conflict', $rows[$id]['status']);
        $this->assertSame('手書きのタイトル', $this->jaPage()->title);
    }

    public function test_legacy_and_non_editing_languages_are_refused(): void
    {
        $this->draft('app_page', 1, 'title:homepage', 'Home', 'ホーム');
        file_put_contents("{$this->dir}/source.json", json_encode(['rows' => $this->sources]));
        file_put_contents("{$this->dir}/drafts.json", json_encode(['rows' => $this->drafts]));
        foreach (['en', 'vi', 'ko'] as $code) {
            $this->artisan('i18n:import-drafts', ['code' => $code, '--source' => "{$this->dir}/source.json", '--drafts' => "{$this->dir}/drafts.json", '--apply' => true])
                ->assertFailed();
        }
        $this->assertSame('Home', $this->jaPage()->title);
        $this->assertSame(0, DB::table('editorial_audit_log')->where('action', 'draft_import')->count());
    }
}
