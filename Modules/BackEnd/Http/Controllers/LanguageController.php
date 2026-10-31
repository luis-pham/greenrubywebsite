<?php
namespace Modules\BackEnd\Http\Controllers;

use App\Services\Locale\FrontendManifest;
use App\Services\Locale\LocaleRegistry;
use App\Services\Locale\LocaleStatusService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\BackEnd\Entities\AdLanguage;
use Modules\BackEnd\Helpers\Logging;

/**
 * "Ngôn ngữ" (phase 1E-a): the locale registry for administrators. Shows every registered language with its native
 * name, site status (disabled / editing / public), whether the deployed website can serve it, and its price list.
 * Status changes go through LocaleStatusService (same guards as `php artisan i18n:locale status`) and need a POST
 * with the language code typed again and a reason, so no navigation or stray click can change a public status.
 */
class LanguageController extends Controller
{
    private $baseView = 'backend::language.';

    public function index(Request $request)
    {
        $title = 'Ngôn ngữ';
        \SEO::setTitle($title);
        $fresh = $request->boolean('check');
        $migrated = LocaleRegistry::migrated();
        $languages = LocaleRegistry::all()->map(fn (AdLanguage $l) => (object) [
            'model' => $l,
            'status' => LocaleRegistry::status($l),
            'legacy' => LocaleRegistry::isLegacy($l),
            'effectivePublic' => LocaleRegistry::isEffectivePublic($l, $fresh),
            'webSupports' => LocaleRegistry::isLegacy($l) || FrontendManifest::supports((string) $l->code, $fresh),
            'sections' => LocaleRegistry::isLegacy($l) ? null : FrontendManifest::supportedSections((string) $l->code),
            'priceList' => LocaleRegistry::priceListCode((string) $l->code),
            'transitions' => LocaleRegistry::isLegacy($l) ? [] : (LocaleStatusService::ALLOWED[LocaleRegistry::status($l)] ?? []),
        ]);
        $holdBack = !config('i18n.allow_public_new_locales');

        return view($this->baseView . __FUNCTION__, compact('title', 'languages', 'migrated', 'holdBack', 'fresh'));
    }

    public function updateDisplay(Request $request, $id)
    {
        $language = LocaleRegistry::byId((int) $id);
        abort_unless($language, 404);
        $request->validate(['native_name' => 'nullable|string|max:64', 'english_name' => 'nullable|string|max:64']);
        LocaleStatusService::updateDisplay($language, $request->only(LocaleStatusService::DISPLAY_FIELDS), Auth::id());
        Logging::LogInfo('Sửa tên hiển thị ngôn ngữ.', "code = {$language->code}");

        return redirect(route('backend.language.index'))->with('flash-message', "Đã lưu tên hiển thị của {$language->code}.");
    }

    public function updateStatus(Request $request, $id)
    {
        $language = LocaleRegistry::byId((int) $id);
        abort_unless($language, 404);
        $target = (string) $request->input('status');
        // The code typed again: a deliberate act, never a mis-click.
        if (strtolower(trim((string) $request->input('confirm_code'))) !== strtolower((string) $language->code)) {
            return redirect()->back()->withErrors("Gõ lại mã ngôn ngữ \"{$language->code}\" để xác nhận.");
        }
        $reason = (string) $request->input('reason');
        if ($refusal = LocaleStatusService::refusal($language, $target, $reason)) {
            return redirect()->back()->withErrors('Không đổi trạng thái: ' . $refusal);
        }
        try {
            $result = LocaleStatusService::change($language, $target, $reason, Auth::id(), 'admin');
        } catch (\RuntimeException $e) {
            return redirect()->back()->withErrors('Không đổi trạng thái: ' . $e->getMessage());
        }
        Logging::LogInfo('Đổi trạng thái ngôn ngữ.', "code = {$language->code}, {$result['from']} → {$result['to']}, reason = " . trim($reason));
        $message = $result['changed']
            ? "{$language->code}: {$result['from']} → {$result['to']}." . ($result['revalidated'] ? ' Đã làm mới website.' : ' Chưa làm mới được website (kiểm tra FRONTEND_REVALIDATE_URL).')
            : "{$language->code} đã ở trạng thái {$target}.";

        return redirect(route('backend.language.index'))->with('flash-message', $message);
    }
}
