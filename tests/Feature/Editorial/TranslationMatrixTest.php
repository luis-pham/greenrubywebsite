<?php

namespace Tests\Feature\Editorial;

use App\Services\Content\ContentTranslations;
use App\Services\Content\TranslationMatrix;
use Illuminate\Support\Facades\DB;

/** Phase 1E-b: the N-language matrix and its actions on EN/VI/JA groups. */
class TranslationMatrixTest extends EditorialTestCase
{
    public function test_matrix_shows_vi_and_ja_columns_with_separate_dimensions(): void
    {
        $html = $this->get('/admincp/translation?type=cruise')->assertOk()->getContent();
        foreach (['Tiếng Việt', '日本語', 'Đang biên tập', 'Tạo bản 日本語', 'Đã xuất bản'] as $text) {
            $this->assertStringContainsString($text, $html, $text);
        }
        $this->assertStringNotContainsString('한국어', $html, 'disabled languages have no column');

        $this->post('/admincp/translation/create', ['type' => 'cruise', 'source_id' => 3, 'language' => 'ja'])->assertRedirect();
        $ja = (int) DB::table('app_cruise')->where('language_id', 3)->value('id');
        $cell = TranslationMatrix::build('cruise')['rows'][0]->cells['ja'];
        $this->assertSame([$ja, 'draft', false, false], [$cell->id, $cell->state, $cell->published, $cell->outdated]);
        DB::table('app_cruise')->where('id', 3)->update(['content' => 'EN body, revised']);
        $this->assertTrue(TranslationMatrix::build('cruise')['rows'][0]->cells['ja']->outdated);
        $this->assertSame('published', TranslationMatrix::build('cruise')['rows'][0]->cells['vi']->state, 'VI: legacy, no workflow');
        $this->assertCount(1, TranslationMatrix::build('cruise', ['outdated' => 1])['rows']);
        $this->assertCount(0, TranslationMatrix::build('cruise', ['state' => 'approved'])['rows']);
        $this->assertStringContainsString('Bản gốc đã đổi', $this->get('/admincp/translation?type=cruise')->getContent());
    }

    public function test_detach_and_attach_never_touch_the_en_vi_pair(): void
    {
        $this->post('/admincp/translation/create', ['type' => 'cruise', 'source_id' => 3, 'language' => 'ja']);
        $ja = (int) DB::table('app_cruise')->where('language_id', 3)->value('id');
        $this->post('/admincp/translation/detach', ['type' => 'cruise', 'id' => $ja, 'reason' => 'Wrong ship text'])->assertRedirect();
        $this->assertEquals([1 => 1, 2 => 3], $this->groupOf('cruise', 3), 'EN/VI pair intact');
        $this->assertFalse(ContentTranslations::isLinked('cruise', $ja));

        $this->post('/admincp/translation/attach', ['type' => 'cruise', 'source_id' => 3, 'target_id' => $ja, 'reason' => 'Re-attach after review'])->assertRedirect();
        $this->assertEquals([1 => 1, 2 => 3, 3 => $ja], $this->groupOf('cruise', 3));

        // A VI member is detached only with an explicit confirmation, and then only VI leaves.
        $this->post('/admincp/translation/detach', ['type' => 'cruise', 'id' => 1, 'reason' => 'Testing a VI split'])->assertSessionHasErrors();
        $this->assertEquals([1 => 1, 2 => 3, 3 => $ja], $this->groupOf('cruise', 3));
        $this->post('/admincp/translation/detach', ['type' => 'cruise', 'id' => 1, 'reason' => 'Testing a VI split', 'confirm_legacy' => 1])->assertRedirect();
        $this->assertEquals([2 => 3, 3 => $ja], $this->groupOf('cruise', 3));

        // Attaching a second VI row to a group that has one is refused by the service.
        $this->post('/admincp/translation/attach', ['type' => 'cruise', 'source_id' => 3, 'target_id' => 1, 'reason' => 'Put VI back now'])->assertRedirect();
        $this->assertEquals([1 => 1, 2 => 3, 3 => $ja], $this->groupOf('cruise', 3));
        $this->assertSame(['attach', 'detach', 'attach', 'detach', 'attach'], DB::table('content_translation_log')->orderBy('id')->pluck('action')->all());
        $this->post('/admincp/translation/detach', ['type' => 'cruise', 'id' => 1, 'reason' => 'short'])->assertStatus(422);
    }

    public function test_history_and_single_entry_undo(): void
    {
        $this->post('/admincp/translation/create', ['type' => 'cruise', 'source_id' => 3, 'language' => 'ja']);
        $ja = (int) DB::table('app_cruise')->where('language_id', 3)->value('id');
        $this->post('/admincp/translation/detach', ['type' => 'cruise', 'id' => $ja, 'reason' => 'Detach for the history test']);
        $html = $this->get("/admincp/translation/history?type=cruise&id=3")->assertOk()->getContent();
        $this->assertStringContainsString('Detach for the history test', $html);

        $detach = (int) DB::table('content_translation_log')->where('action', 'detach')->value('id');
        $attach = (int) DB::table('content_translation_log')->where('action', 'attach')->value('id');
        // The attach entry is no longer the current state of the row: refused.
        $this->post('/admincp/translation/undo', ['type' => 'cruise', 'id' => 3, 'log_id' => $attach, 'reason' => 'Undo the old attach'])->assertSessionHasErrors();
        $this->post('/admincp/translation/undo', ['type' => 'cruise', 'id' => 3, 'log_id' => $detach, 'reason' => 'Undo the detach now'])->assertRedirect();
        $this->assertEquals([1 => 1, 2 => 3, 3 => $ja], $this->groupOf('cruise', 3));
    }

    public function test_legacy_en_vi_pairs_view_still_works(): void
    {
        $this->get('/admincp/translation?type=cruise&view=pairs')->assertOk()->assertSee('ghép EN/VI', false);
        $this->post('/admincp/translation', ['type' => 'cruise', 'pair' => [3 => '1']])->assertRedirect('/admincp/translation?type=cruise&view=pairs');
        $this->assertEquals([1 => 1, 2 => 3], $this->groupOf('cruise', 3));
    }

    public function test_every_type_renders(): void
    {
        foreach (array_keys(\App\Services\Content\TranslatableFields::TYPES) as $type) {
            $this->get("/admincp/translation?type={$type}")->assertOk();
        }
    }
}
