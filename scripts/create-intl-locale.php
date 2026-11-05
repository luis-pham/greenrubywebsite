<?php
/**
 * Registers a new intl content language (an ad_language row) the way `ja` is registered: /{code} prefix, English
 * path segments, prices and terms from the English rows, workflow-controlled, status `disabled`. Nothing becomes
 * public: next steps are `i18n:locale status {code} editing` and `i18n:seed-locale {code}`.
 *
 *   I18N_ALLOW_NEW_LOCALES=true php scripts/create-intl-locale.php fr Français French fr_FR fr-FR
 *
 * Refuses a code that already exists.
 */

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AdLanguage;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

[, $code, $native, $english, $ogLocale, $intlTag] = $argv + array_fill(0, 6, null);
if (!$code || !preg_match('/^[a-z]{2}$/', $code) || !$native || !$english || !$ogLocale || !$intlTag) {
    fwrite(STDERR, "usage: php scripts/create-intl-locale.php <code> <native name> <English name> <og_locale> <intl tag>\n");
    exit(1);
}
if (AdLanguage::where('code', $code)->exists()) {
    fwrite(STDERR, "Language '$code' already exists.\n");
    exit(1);
}

$english_ = AdLanguage::where('code', 'en')->firstOrFail();
$language = new AdLanguage();
$language->code = $code;
$language->name = $native;
$language->short_name = strtoupper($code);
$language->is_default = 0;
$language->ord = (int) AdLanguage::max('ord') + 1;
$language->save(); // AppServiceProvider refuses this unless I18N_ALLOW_NEW_LOCALES is on

DB::table('ad_language')->where('id', $language->id)->update([
    'site_status' => 'disabled',
    'native_name' => $native,
    'english_name' => $english,
    'url_prefix' => $code,
    'route_style' => 'intl',
    'hreflang' => $code,
    'og_locale' => $ogLocale,
    'intl_tag' => $intlTag,
    'direction' => 'ltr',
    'price_list_language_id' => $english_->id,
    'ui_fallback_language_id' => null,
    'xdefault_priority' => null,
    'workflow_controlled' => 1,
]);
\App\Services\Locale\LocaleRegistry::flush();
echo "Created language '$code' (#{$language->id}), status disabled.\n";
