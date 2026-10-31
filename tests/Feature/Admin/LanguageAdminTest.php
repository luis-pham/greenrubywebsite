<?php

namespace Tests\Feature\Admin;

use App\Services\Locale\LocaleRegistry;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Helpers\ContentLanguages;
use Modules\BackEnd\Helpers\LanguageUtils;

/** Phase 1E-a: Languages screen, content-language selector, guarded status changes. */
class LanguageAdminTest extends AdminTestCase
{
    public function test_languages_screen_lists_native_names_and_statuses(): void
    {
        $html = $this->get('/admincp/language')->assertOk()->getContent();
        foreach (['日本語', '한국어', 'Đang biên tập', 'Tắt', 'Công khai', 'Luôn hỗ trợ'] as $text) {
            $this->assertStringContainsString($text, $html, $text);
        }
        $this->assertStringContainsString('khóa công khai', $html, 'hold-back notice while I18N_ALLOW_PUBLIC_NEW_LOCALES is off');
    }

    public function test_content_language_selector_lists_editable_languages_only_and_keeps_the_section(): void
    {
        $this->assertSame(['en', 'vi', 'ja'], ContentLanguages::editable()->pluck('code')->all(), 'ko is disabled');
        $html = $this->get('/admincp/language')->getContent();
        $this->assertStringContainsString('class="dropdown-item px-3 py-2" lang="ja"', $html);
        $this->assertStringNotContainsString('class="dropdown-item px-3 py-2" lang="ko"', $html);

        $ja = LocaleRegistry::byCode('ja');
        $this->get('/admincp/changeLanguage?languageId=3&return=' . urlencode('/admincp/article/edit/24'))->assertRedirect('/admincp/ja/article');
        $this->assertSame('ja', LanguageUtils::getCurrentLanguage()->code);
        $this->assertSame('/admincp/vi/cabin-manager', ContentLanguages::sectionPathFor('/admincp/ja/cabin-manager/edit/90', LocaleRegistry::byCode('vi')));
        // A section without a language-aware route falls back to the dashboard.
        $this->get('/admincp/changeLanguage?languageId=3&return=' . urlencode('/admincp/translation'))->assertRedirect('/admincp/ja');
        $this->assertSame('/admincp/category/article', ContentLanguages::sectionPathFor('/admincp/ja/category/article/show/5', LocaleRegistry::byCode('en')));
        $this->assertSame('/admincp/ja', ContentLanguages::sectionPathFor('https://evil.example/x', $ja), 'only /admincp paths');

        // A disabled language cannot be selected and has no admin routes.
        $this->get('/admincp/changeLanguage?languageId=4')->assertRedirect();
        $this->assertSame('ja', LanguageUtils::getCurrentLanguage()->code, 'unchanged');
        $this->get('/admincp/ko')->assertNotFound();
    }

    public function test_switching_the_content_language_never_changes_a_status(): void
    {
        $before = DB::table('ad_language')->pluck('site_status', 'code')->all();
        $this->get('/admincp/changeLanguage?languageId=3');
        $this->get('/admincp/changeLanguage?languageId=1');
        $this->get('/admincp/changeLanguage?languageId=2');
        $this->assertSame($before, DB::table('ad_language')->pluck('site_status', 'code')->all());
    }

    public function test_status_change_needs_the_typed_code_a_reason_and_the_registry_rules(): void
    {
        $status = fn () => DB::table('ad_language')->where('code', 'ja')->value('site_status');
        $this->post('/admincp/language/3/status', ['status' => 'disabled', 'confirm_code' => 'en', 'reason' => 'Pause the Japanese work'])->assertSessionHasErrors();
        $this->post('/admincp/language/3/status', ['status' => 'disabled', 'confirm_code' => 'ja', 'reason' => 'short'])->assertSessionHasErrors();
        $this->assertSame('editing', $status());

        // Hold-back on: public is refused even with a valid confirmation.
        $this->post('/admincp/language/3/status', ['status' => 'public', 'confirm_code' => 'ja', 'reason' => 'Try to publish Japanese'])->assertSessionHasErrors();
        $this->assertSame('editing', $status());

        // Released, but no web instance supports ja: still refused (manifest readiness).
        config(['i18n.allow_public_new_locales' => true, 'i18n.frontend_manifest.urls' => []]);
        $this->post('/admincp/language/3/status', ['status' => 'public', 'confirm_code' => 'ja', 'reason' => 'Try to publish Japanese'])->assertSessionHasErrors();
        $this->assertSame('editing', $status());

        // disabled → public is never a single step; en/vi never leave public.
        $this->post('/admincp/language/4/status', ['status' => 'public', 'confirm_code' => 'ko', 'reason' => 'Skip the editing stage'])->assertSessionHasErrors();
        $this->post('/admincp/language/1/status', ['status' => 'editing', 'confirm_code' => 'vi', 'reason' => 'Withdraw Vietnamese now'])->assertSessionHasErrors();
        $this->assertSame('public', DB::table('ad_language')->where('code', 'vi')->value('site_status'));

        $this->post('/admincp/language/3/status', ['status' => 'disabled', 'confirm_code' => 'JA', 'reason' => 'Pause the Japanese work'])->assertRedirect('/admincp/language');
        $this->assertSame('disabled', $status());
    }

    public function test_status_routes_are_post_only(): void
    {
        $this->get('/admincp/language/3/status?status=disabled&confirm_code=ja&reason=xxxxxxxxxxxx')->assertStatus(405);
        $this->assertSame('editing', DB::table('ad_language')->where('code', 'ja')->value('site_status'));
    }

    public function test_display_names_are_editable_but_code_and_prefix_are_not(): void
    {
        $this->post('/admincp/language/3/display', ['native_name' => '日本語（JP）', 'code' => 'jp', 'url_prefix' => 'jp'])->assertRedirect('/admincp/language');
        $row = DB::table('ad_language')->where('id', 3)->first();
        $this->assertSame(['日本語（JP）', 'ja', 'ja'], [$row->native_name, $row->code, $row->url_prefix]);
    }

    public function test_admin_labels_for_a_new_content_language_use_the_admin_ui_language(): void
    {
        $this->assertSame('en', ContentLanguages::labelLocale('en'));
        $this->assertSame('vi', ContentLanguages::labelLocale('vi'));
        $this->assertSame('vi', ContentLanguages::labelLocale('ja'));
    }
}
