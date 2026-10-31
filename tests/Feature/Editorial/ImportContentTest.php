<?php

namespace Tests\Feature\Editorial;

use App\Services\Content\TranslationFactory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Phase 2C safety fix 5: i18n:import-content (entity content drafts: cruise, cabin + rooms, itinerary + programme
 * lines, destination) and fix 3 (article categories need --actor). Fixture: EN cruise #3, cabin #24 (room "Bedroom"),
 * itinerary #3 (day 1, 12:00 "Boarding"), destination #1, category #10.
 */
class ImportContentTest extends EditorialTestCase
{
    private string $dir;
    private array $items = [];

    protected function setUp(): void
    {
        parent::setUp();
        (require database_path('migrations/2026_11_04_000001_create_content_revision_and_preview_tables.php'))->up();
        $this->artisan('i18n:seed-locale', ['code' => 'ja'])->assertSuccessful();
        $this->dir = sys_get_temp_dir() . '/import-content-' . uniqid();
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir . '/*') ?: []);
        @rmdir($this->dir);
        parent::tearDown();
    }

    /** Adds an item whose source hashes are taken from the current English rows. */
    private function item(string $type, int $enId, array $fields, array $children = [], string $status = 'PENDING_REVIEW', ?string $slug = null, array $extra = []): void
    {
        $table = ['cruise' => 'app_cruise', 'cabin' => 'app_cabin', 'itinerary' => 'app_itinerary', 'destination' => 'app_destination',
            'experience' => 'app_exp_activity', 'faq_group' => 'app_group', 'faq' => 'app_faq', 'article_category' => 'app_category', 'article' => 'app_article'][$type];
        $en = DB::table($table)->find($enId);
        $hashes = [];
        foreach ($fields as $column => $value) {
            $hashes[$column] = sha1((string) ($en->{$column} ?? ''));
        }
        $kids = [];
        foreach ($children as [$childTable, $childId, $childFields]) {
            $row = DB::table($childTable)->find($childId);
            $kids[] = ['table' => $childTable, 'en_id' => $childId, 'fields' => $childFields,
                'source_hash' => array_map(fn ($c) => sha1((string) $row->{$c}), array_combine(array_keys($childFields), array_keys($childFields)))];
        }
        $this->items[] = ['type' => $type, 'en_id' => $enId, 'slug' => $slug, 'fields' => $fields, 'source_hash' => $hashes, 'children' => $kids,
            'status' => $status, 'risk' => [], 'flags' => []];
        $this->items[array_key_last($this->items)] = array_merge(end($this->items), $extra);
    }

    private function import(array $options = []): array
    {
        file_put_contents("{$this->dir}/content.json", json_encode(['schema' => 1, 'language' => 'ja', 'items' => $this->items]));
        $report = "{$this->dir}/report-" . uniqid() . '.json';
        $code = Artisan::call('i18n:import-content', ['code' => 'ja', '--file' => "{$this->dir}/content.json", '--report' => $report] + $options);
        if (!is_file($report)) {
            return [$code, [], []];
        }
        $data = json_decode(file_get_contents($report), true);

        return [$code, collect($data['items'])->keyBy('id')->all(), array_filter($data['counts'])];
    }

    private function snapshot(): array
    {
        $out = [];
        foreach (['app_cruise', 'app_cabin', 'app_cabin_room', 'app_itinerary', 'app_itinerary_day', 'app_itinerary_day_detail', 'app_destination',
            'translation_meta', 'editorial_audit_log', 'content_revision', 'content_translations', 'app_slug_history'] as $table) {
            $out[$table] = DB::table($table)->get()->map(fn ($r) => (array) $r)->all();
        }

        return $out;
    }

    private function fullSet(): void
    {
        $this->item('cruise', 3, ['name' => 'グリーンルビー1', 'summary' => 'ハロン湾のエコラグジュアリー', 'capacity' => 200], [], 'PENDING_REVIEW', 'green-ruby-1');
        $this->item('cabin', 24, ['name' => 'インペリアルスイート', 'view' => 'オーシャンビュー'], [['app_cabin_room', 1, ['title' => '寝室', 'description' => 'キングベッド']]], 'PENDING_REVIEW', 'imperial-suite');
        $this->item('itinerary', 3, ['name' => 'ハロン・イマージョン', 'description' => '3日間'], [['app_itinerary_day_detail', 1, ['title' => '乗船', 'description' => 'ウェルカムドリンク', 'time' => '13:00']]], 'PENDING_REVIEW', 'the-halong-immersion');
        DB::table('app_destination')->where('id', 1)->update(['facts' => '[{"label":"Hours","value":"7:30"}]']);
        $this->item('destination', 1, ['name' => 'スンソット洞窟', 'facts' => '[{"label":"営業時間","value":"7:30"}]'], [], 'PENDING_REVIEW', 'sung-sot-cave');
    }

    public function test_dry_run_writes_nothing_then_apply_creates_drafts_and_a_second_run_writes_nothing(): void
    {
        $this->fullSet();
        [, $rows] = $this->import();
        $this->assertSame(['missing_target'], array_values(array_unique(array_column($rows, 'status'))), 'no ja rows and no --create-missing');

        $before = $this->snapshot();
        [$code, $rows, $counts] = $this->import(['--create-missing' => true]);
        $this->assertSame(0, $code);
        $this->assertSame(['created' => 4], $counts, json_encode($rows, JSON_UNESCAPED_UNICODE));
        $this->assertEquals($before, $this->snapshot(), 'a dry run writes nothing');

        [, $rows, $counts] = $this->import(['--create-missing' => true, '--apply' => true, '--actor' => 2]);
        $this->assertSame(['created' => 4], $counts, json_encode($rows, JSON_UNESCAPED_UNICODE));
        $cruise = DB::table('app_cruise')->find($rows['cruise:3']['target']['id']);
        $this->assertSame(['グリーンルビー1', 'ハロン湾のエコラグジュアリー', 'unpublished', 'green-ruby-1', 92], [$cruise->name, $cruise->summary, $cruise->publish_state, $cruise->slug, (int) $cruise->capacity]);
        $this->assertContains('ignored (not translatable text): capacity', $rows['cruise:3']['notes']);
        $cabin = DB::table('app_cabin')->find($rows['cabin:24']['target']['id']);
        $this->assertSame(['インペリアルスイート', 'オーシャンビュー', 'unpublished', (int) $cruise->id], [$cabin->name, $cabin->view, $cabin->publish_state, (int) $cabin->cruise_id]);
        $room = DB::table('app_cabin_room')->where('cabin_id', $cabin->id)->first();
        $this->assertSame(['寝室', 'キングベッド'], [$room->title, $room->description]);
        $itinerary = $rows['itinerary:3']['target']['id'];
        $detail = DB::table('app_itinerary_day_detail')->join('app_itinerary_day', 'app_itinerary_day.id', '=', 'app_itinerary_day_detail.itinerary_day_id')
            ->where('app_itinerary_day.itinerary_id', $itinerary)->first(['app_itinerary_day_detail.*']);
        $this->assertSame(['乗船', 'ウェルカムドリンク', '12:00'], [$detail->title, $detail->description, substr((string) $detail->time, 0, 5)], 'time is structure: not written');
        $destination = DB::table('app_destination')->find($rows['destination:1']['target']['id']);
        $this->assertSame(['スンソット洞窟', 'draft', 'sung-sot-cave'], [$destination->name, $destination->status, $destination->slug]);
        $this->assertSame('[{"label":"営業時間","value":"7:30"}]', $destination->facts, 'JSON column: the draft text exactly (not re-encoded by the model cast)');
        $this->assertSame('Green Ruby 1', DB::table('app_cruise')->find(3)->name, 'English untouched');
        foreach ($rows as $row) {
            $meta = DB::table('translation_meta')->where('entity_type', $row['type'])->where('entity_id', $row['target']['id'])->first();
            $this->assertSame(['draft', 2], [$meta->workflow, (int) $meta->translated_by], $row['id']);
        }
        $audit = DB::table('editorial_audit_log')->whereIn('action', ['translation_created', 'draft_import'])->get();
        $this->assertSame(8, $audit->count());
        $this->assertSame([2], $audit->pluck('actor')->map(fn ($a) => (int) $a)->unique()->values()->all());
        $this->assertSame(4, DB::table('editorial_audit_log')->where('action', 'slug_change')->count());

        $after = $this->snapshot();
        [, , $counts] = $this->import(['--create-missing' => true, '--apply' => true, '--actor' => 2]);
        $this->assertSame(['skipped' => 4], $counts);
        $this->assertEquals($after, $this->snapshot(), 'idempotent: nothing written the second time');
    }

    public function test_experiences_faq_groups_and_faq_items_are_imported_as_unpublished_drafts(): void
    {
        DB::table('app_exp_activity')->insert(['id' => 8, 'language_id' => 2, 'cruise_id' => 3, 'name' => 'Kayaking', 'summary' => 'Paddle the lagoons',
            'note' => '["Relaxing"]', 'duration' => '60', 'start_time' => '15:30', 'end_time' => '16:30', 'slug' => 'kayaking', 'publish_state' => 'published',
            'created_at' => now(), 'created_by' => 1]);
        DB::table('app_exp_activity')->insert(['id' => 9, 'language_id' => 1, 'cruise_id' => 1, 'name' => 'Chèo kayak', 'summary' => 'Chèo thuyền',
            'note' => '["Thư giãn"]', 'duration' => '60', 'start_time' => '15:30', 'end_time' => '16:30', 'slug' => 'cheo-kayak', 'publish_state' => 'published',
            'created_at' => now(), 'created_by' => 1]);
        \App\Services\Content\ContentTranslations::link('experience', [8, 9]);
        $this->item('experience', 8, ['name' => 'カヤック', 'summary' => 'ラグーンを漕ぐ', 'note' => '["リラックス"]'], [], 'PENDING_REVIEW', 'kayaking');
        $this->item('faq_group', 57, ['name' => 'ご予約'], [], 'PENDING_REVIEW', 'bookings');
        $this->item('faq', 1, ['question' => 'キャンセルできますか？', 'answer' => 'はい']);

        [, $rows, $counts] = $this->import(['--create-missing' => true]);
        $this->assertSame(['created' => 3], $counts, json_encode($rows, JSON_UNESCAPED_UNICODE));

        [, $rows, $counts] = $this->import(['--create-missing' => true, '--apply' => true, '--actor' => 2]);
        $this->assertSame(['created' => 3], $counts, json_encode($rows, JSON_UNESCAPED_UNICODE));
        $exp = DB::table('app_exp_activity')->find($rows['experience:8']['target']['id']);
        $this->assertSame(['カヤック', 'ラグーンを漕ぐ', '["リラックス"]', 'unpublished', 'kayaking', '15:30'],
            [$exp->name, $exp->summary, $exp->note, $exp->publish_state, $exp->slug, substr((string) $exp->start_time, 0, 5)]);
        $group = DB::table('app_group')->find($rows['faq_group:57']['target']['id']);
        $this->assertSame(['ご予約', 'unpublished', 'bookings', 1], [$group->name, $group->publish_state, $group->slug, (int) $group->type]);
        $faq = DB::table('app_faq')->find($rows['faq:1']['target']['id']);
        $this->assertSame(['キャンセルできますか？', 'はい', 'unpublished', (int) $group->id], [$faq->question, $faq->answer, $faq->publish_state, (int) $faq->group_id]);
        $this->assertSame('Kayaking', DB::table('app_exp_activity')->find(8)->name, 'English untouched');

        [, , $counts] = $this->import(['--create-missing' => true, '--apply' => true, '--actor' => 2]);
        $this->assertSame(['skipped' => 3], $counts);
    }

    public function test_a_blog_category_and_article_are_imported_as_unpublished_drafts(): void
    {
        $this->item('article_category', 10, ['name' => '旅のヒント'], [], 'PENDING_REVIEW', 'travel-tips');
        $this->item('article', 100, ['title' => 'ハロン湾のベストシーズン', 'lead' => 'リード', 'content' => '<p>本文</p>'], [], 'PENDING_REVIEW', 'best-time');

        [, $rows, $counts] = $this->import(['--create-missing' => true, '--actor' => 2]);
        $this->assertSame(['created' => 2], $counts, json_encode($rows, JSON_UNESCAPED_UNICODE));

        [, $rows, $counts] = $this->import(['--create-missing' => true, '--apply' => true, '--actor' => 2]);
        $this->assertSame(['created' => 2], $counts, json_encode($rows, JSON_UNESCAPED_UNICODE));
        $category = DB::table('app_category')->find($rows['article_category:10']['target']['id']);
        $this->assertSame(['旅のヒント', 'travel-tips', 3], [$category->name, $category->slug, (int) $category->language_id]);
        $article = DB::table('app_article')->find($rows['article:100']['target']['id']);
        $this->assertSame(['ハロン湾のベストシーズン', '<p>本文</p>', 0, (int) $category->id, 'best-time'],
            [$article->title, $article->content, (int) $article->is_published, (int) $article->category_id, $article->slug]);
        $this->assertSame('Best time to visit', DB::table('app_article')->find(100)->title, 'English untouched');

        [, , $counts] = $this->import(['--create-missing' => true, '--apply' => true, '--actor' => 2]);
        $this->assertSame(['skipped' => 2], $counts);
    }

    public function test_conflict_when_the_english_source_changed_nothing_of_the_item_is_written(): void
    {
        TranslationFactory::create('cruise', 3, $this->ja(), 1);
        $ja = TranslationFactory::create('cabin', 24, $this->ja(), 1);
        $this->item('cabin', 24, ['name' => 'インペリアルスイート'], [['app_cabin_room', 1, ['title' => '寝室']]]);
        DB::table('app_cabin_room')->where('id', 1)->update(['title' => 'Master bedroom']);
        [, $rows, $counts] = $this->import(['--apply' => true]);
        $this->assertSame(['conflict' => 1], $counts);
        $this->assertStringContainsString('app_cabin_room#1.title', $rows['cabin:24']['reason']);
        $this->assertSame('Imperial Suite', DB::table('app_cabin')->find($ja['id'])->name, 'all or nothing');
    }

    public function test_flagged_items_need_the_option_and_blocked_never_imports(): void
    {
        $cruise = TranslationFactory::create('cruise', 3, $this->ja(), 1)['id'];
        $destination = TranslationFactory::create('destination', 1, $this->ja(), 1)['id'];
        $this->item('cruise', 3, ['name' => 'グリーンルビー1'], [], 'COMMERCIAL_REVIEW_REQUIRED');
        $this->item('destination', 1, ['name' => 'スンソット洞窟'], [], 'PENDING_REVIEW', null, ['risk' => ['BLOCKED']]);
        [, $rows] = $this->import(['--apply' => true]);
        $this->assertSame(['needs_review', 'needs_review'], [$rows['cruise:3']['status'], $rows['destination:1']['status']]);
        [, $rows] = $this->import(['--apply' => true, '--include-flagged' => true]);
        $this->assertSame(0, DB::table('app_cruise')->where('language_id', 3)->where('name', 'Green Ruby 1')->count(), 'written');
        $this->assertSame(['updated', 'needs_review'], [$rows['cruise:3']['status'], $rows['destination:1']['status']]);
        $this->assertStringContainsString('BLOCKED', $rows['destination:1']['reason']);
        $this->assertSame(['グリーンルビー1', 'Sung Sot Cave'], [DB::table('app_cruise')->find($cruise)->name, DB::table('app_destination')->find($destination)->name]);
    }

    public function test_an_edited_or_in_review_target_is_not_overwritten(): void
    {
        $cruise = TranslationFactory::create('cruise', 3, $this->ja(), 1)['id'];
        DB::table('app_cruise')->where('id', $cruise)->update(['summary' => '編集者の文']);
        $this->item('cruise', 3, ['name' => 'グリーンルビー1', 'summary' => 'ドラフト']);
        [, $rows] = $this->import(['--apply' => true]);
        $this->assertSame('conflict', $rows['cruise:3']['status']);
        $this->assertSame(['Green Ruby 1', '編集者の文'], [DB::table('app_cruise')->find($cruise)->name, DB::table('app_cruise')->find($cruise)->summary]);
        DB::table('app_cruise')->where('id', $cruise)->update(['summary' => 'Ha Long eco-luxury']);
        DB::table('translation_meta')->where('entity_type', 'cruise')->where('entity_id', $cruise)->update(['workflow' => 'translation_review']);
        [, $rows] = $this->import(['--apply' => true]);
        $this->assertSame('needs_review', $rows['cruise:3']['status']);
        $this->assertStringContainsString('translation_review', $rows['cruise:3']['reason']);
    }

    public function test_a_published_target_only_gets_a_pending_revision(): void
    {
        TranslationFactory::create('cruise', 3, $this->ja(), 1);
        $cabin = TranslationFactory::create('cabin', 24, $this->ja(), 1)['id'];
        DB::table('app_cabin')->where('id', $cabin)->update(['publish_state' => 'published', 'slug' => 'imperial-suite']);
        $this->item('cabin', 24, ['name' => 'インペリアルスイート'], [['app_cabin_room', 1, ['title' => '寝室']]], 'PENDING_REVIEW', 'other-slug');
        [, $rows] = $this->import(['--apply' => true, '--actor' => 2]);
        $this->assertSame('updated', $rows['cabin:24']['status'], json_encode($rows, JSON_UNESCAPED_UNICODE));
        $this->assertSame(['Imperial Suite', 'imperial-suite', 'published'], [DB::table('app_cabin')->find($cabin)->name, DB::table('app_cabin')->find($cabin)->slug, DB::table('app_cabin')->find($cabin)->publish_state]);
        $this->assertSame('Bedroom', DB::table('app_cabin_room')->where('cabin_id', $cabin)->value('title'), 'live child text unchanged');
        $revision = DB::table('content_revision')->where('entity_type', 'cabin')->where('entity_id', $cabin)->first();
        $fields = json_decode($revision->fields, true);
        $roomId = (int) DB::table('app_cabin_room')->where('cabin_id', $cabin)->value('id');
        $this->assertSame(['インペリアルスイート', '寝室', 'draft', 2], [$fields['name'], $fields['_children']['app_cabin_room'][$roomId]['title'], $revision->workflow, (int) $revision->created_by]);
        $this->assertSame((int) $revision->id, $rows['cabin:24']['revision_id']);
        [, $again] = $this->import(['--apply' => true, '--actor' => 2]);
        $this->assertSame('skipped', $again['cabin:24']['status'], 'the pending revision holds the draft');
        $this->assertSame(1, DB::table('content_revision')->count());
    }

    public function test_a_cabin_without_its_ja_cruise_and_legacy_languages_are_refused(): void
    {
        $this->item('cabin', 24, ['name' => 'インペリアルスイート']);
        [, $rows] = $this->import(['--apply' => true, '--create-missing' => true]);
        $this->assertSame('missing_target', $rows['cabin:24']['status']);
        $this->assertStringContainsString('cruise #3', $rows['cabin:24']['reason']);
        foreach (['en', 'vi', 'ko'] as $code) {
            $this->artisan('i18n:import-content', ['code' => $code, '--file' => "{$this->dir}/content.json", '--apply' => true])->assertFailed();
        }
        $this->artisan('i18n:import-content', ['code' => 'ja', '--file' => "{$this->dir}/content.json", '--actor' => 999])->assertFailed();
        $this->assertSame(0, DB::table('app_cabin')->where('language_id', 3)->count());
    }

    public function test_an_invalid_slug_is_reported_and_not_set(): void
    {
        $this->item('destination', 1, ['name' => 'スンソット洞窟'], [], 'PENDING_REVIEW', 'スンソット');
        [, $rows] = $this->import(['--apply' => true, '--create-missing' => true]);
        $this->assertSame('created', $rows['destination:1']['status']);
        $this->assertStringContainsString('slug_format', implode(' ', $rows['destination:1']['notes']));
        $this->assertSame('', (string) DB::table('app_destination')->find($rows['destination:1']['target']['id'])->slug);
    }

    public function test_a_json_column_draft_is_written_as_its_exact_text_and_is_idempotent(): void
    {
        // green_technology is cast to an object by AppCruise: compared and written as raw JSON text.
        DB::table('app_cruise')->where('id', 3)->update(['green_technology' => '{"title":"Green"}']);
        $this->item('cruise', 3, ['name' => 'グリーンルビー1', 'green_technology' => '{"title":"グリーン"}']);
        [, $rows, $counts] = $this->import(['--apply' => true, '--create-missing' => true]);
        $this->assertSame(['created' => 1], $counts, json_encode($rows, JSON_UNESCAPED_UNICODE));
        $id = $rows['cruise:3']['target']['id'];
        $this->assertSame('{"title":"グリーン"}', DB::table('app_cruise')->find($id)->green_technology);
        [, , $again] = $this->import(['--apply' => true, '--create-missing' => true]);
        $this->assertSame(['skipped' => 1], $again);
    }

    public function test_a_slug_longer_than_the_audit_columns_is_recorded_in_full_in_the_detail(): void
    {
        $slug = 'the-lan-ha-immersion-3-days-2-nights'; // 36 characters; from_state/to_state hold 32
        $this->item('destination', 1, ['name' => 'スンソット洞窟'], [], 'PENDING_REVIEW', $slug);
        [, $rows] = $this->import(['--apply' => true, '--create-missing' => true]);
        $this->assertSame('created', $rows['destination:1']['status'], json_encode($rows, JSON_UNESCAPED_UNICODE));
        $audit = DB::table('editorial_audit_log')->where('action', 'slug_change')->first();
        $this->assertSame(substr($slug, 0, 32), $audit->to_state);
        $this->assertStringContainsString("to={$slug}", $audit->detail);
    }

    // Fix 3: article categories need --actor (an existing, active admin user) to be created from the CLI.

    private function categoryDraft(): array
    {
        $sources = [['id' => 'CMS-cat-10-name', 'table' => 'app_category', 'row_id' => 10, 'field' => 'name', 'json_path' => '', 'en' => 'Travel tips', 'scope' => 'in', 'reason' => '']];
        $drafts = [['id' => 'CMS-cat-10-name', 'ja' => '旅のヒント', 'status' => 'PENDING_REVIEW']];
        file_put_contents("{$this->dir}/s.json", json_encode(['rows' => $sources]));
        file_put_contents("{$this->dir}/d.json", json_encode(['rows' => $drafts]));

        return ['code' => 'ja', '--source' => "{$this->dir}/s.json", '--file' => "{$this->dir}/d.json", '--create-missing' => true, '--apply' => true];
    }

    public function test_category_creation_without_actor_is_refused_with_a_clear_message(): void
    {
        $report = "{$this->dir}/r.json";
        Artisan::call('i18n:import-drafts', $this->categoryDraft() + ['--report' => $report]);
        $row = json_decode(file_get_contents($report), true)['rows'][0];
        $this->assertSame('missing_target', $row['status']);
        $this->assertStringContainsString('--actor', $row['reason']);
        $this->assertSame(0, DB::table('app_category')->where('language_id', 3)->count());
        try {
            \Illuminate\Support\Facades\Auth::guard('admin')->forgetUser();
            TranslationFactory::create('article_category', 10, $this->ja());
            $this->fail('created without a user');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('--actor', $e->getMessage());
        }
    }

    public function test_category_creation_with_a_valid_actor_is_recorded(): void
    {
        DB::table('ad_user')->where('id', 4)->update(['status' => 2]); // locked
        $this->artisan('i18n:import-drafts', $this->categoryDraft() + ['--actor' => 4])->assertFailed();
        $this->artisan('i18n:import-drafts', $this->categoryDraft() + ['--actor' => 'abc'])->assertFailed();
        $report = "{$this->dir}/r.json";
        Artisan::call('i18n:import-drafts', $this->categoryDraft() + ['--actor' => 2, '--report' => $report]);
        $row = json_decode(file_get_contents($report), true)['rows'][0];
        $this->assertSame('created', $row['status'], json_encode($row, JSON_UNESCAPED_UNICODE));
        $category = DB::table('app_category')->find($row['target']['id']);
        $this->assertSame(['旅のヒント', 'unpublished', 2, 3], [$category->name, $category->publish_state, (int) $category->created_by, (int) $category->language_id]);
        $audit = DB::table('editorial_audit_log')->where('entity_type', 'article_category')->where('entity_id', $category->id)->orderBy('id')->get();
        $this->assertSame([['translation_created', 2], ['draft_import', 2]], $audit->map(fn ($a) => [$a->action, (int) $a->actor])->all());
    }
}
