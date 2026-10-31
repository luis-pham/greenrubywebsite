<?php

namespace App\Services\Locale;

use App\Support\FrontendRevalidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\BackEnd\Entities\AdLanguage;

/**
 * The only way a language's site_status changes (phase 1E-a): `php artisan i18n:locale status` and the admin
 * Languages screen both call it, so the guards are the same everywhere:
 *   disabled ⇄ editing ⇄ public (disabled → public refused: a language is prepared first);
 *   en/vi (legacy) never leave 'public';
 *   'public' needs the hold-back released (I18N_ALLOW_PUBLIC_NEW_LOCALES) and every web instance's manifest;
 *   a reason of at least 10 characters, logged with the actor.
 */
final class LocaleStatusService
{
    public const ALLOWED = [
        'disabled' => ['editing'],
        'editing' => ['disabled', 'public'],
        'public' => ['editing', 'disabled'],
    ];

    /** Why the transition is refused, or null when it is allowed. Reads state only. */
    public static function refusal(?AdLanguage $language, string $target, string $reason): ?string
    {
        if (!LocaleRegistry::migrated()) {
            return 'Registry columns missing: run the 2026_11_01_000001 migration first.';
        }
        if (!$language) {
            return 'Unknown language.';
        }
        $code = (string) $language->code;
        if (!in_array($target, LocaleRegistry::STATUSES, true)) {
            return 'Status must be one of: ' . implode(', ', LocaleRegistry::STATUSES) . '.';
        }
        if (mb_strlen(trim($reason)) < 10) {
            return 'Give a reason of at least 10 characters (it is logged).';
        }
        $current = LocaleRegistry::status($language);
        if ($current === $target) {
            return null;
        }
        if (LocaleRegistry::isLegacy($language)) {
            return "{$code} is a legacy language of the existing site and stays public.";
        }
        if (!in_array($target, self::ALLOWED[$current], true)) {
            return "{$code}: {$current} → {$target} is not allowed (prepare it as 'editing' first).";
        }
        if ($target === 'public' && !config('i18n.allow_public_new_locales')) {
            return "{$code} cannot go public yet: new languages are held back (I18N_ALLOW_PUBLIC_NEW_LOCALES).";
        }
        if ($target === 'public' && !FrontendManifest::supports($code, true)) {
            return "{$code} cannot go public: not every web instance supports it (php artisan i18n:locale manifest).";
        }

        return null;
    }

    /**
     * Applies the transition. @return array{changed: bool, from: string, to: string, revalidated: bool}
     * @throws \RuntimeException when refused
     */
    public static function change(AdLanguage $language, string $target, string $reason, ?int $actor = null, string $via = 'cli'): array
    {
        if ($refusal = self::refusal($language, $target, $reason)) {
            throw new \RuntimeException($refusal);
        }
        $current = LocaleRegistry::status($language);
        if ($current === $target) {
            return ['changed' => false, 'from' => $current, 'to' => $target, 'revalidated' => false];
        }
        $model = AdLanguage::find($language->id);
        $model->setAttribute('site_status', $target);
        $model->save();
        LocaleRegistry::flush();
        FrontendManifest::flush();
        Log::notice('Locale status changed', ['code' => $language->code, 'from' => $current, 'to' => $target, 'reason' => trim($reason), 'actor' => $actor, 'via' => $via]);
        \App\Services\Content\EditorialAudit::record('locale', (int) $language->id, 'status', $current, $target, $actor, null, trim($reason));
        // Expire at once: every page that links the language (hreflang, switcher) must change in one pass.
        $revalidated = FrontendRevalidator::send(['content'], true);

        return ['changed' => true, 'from' => $current, 'to' => $target, 'revalidated' => $revalidated];
    }

    /** Display fields an administrator may edit on the Languages screen (never code, prefix, route style or status). */
    public const DISPLAY_FIELDS = ['native_name', 'english_name'];

    public static function updateDisplay(AdLanguage $language, array $input, ?int $actor = null): void
    {
        $values = [];
        foreach (self::DISPLAY_FIELDS as $field) {
            if (array_key_exists($field, $input)) {
                $value = trim((string) $input[$field]);
                $values[$field] = $value === '' ? null : mb_substr($value, 0, 64);
            }
        }
        if ($values === []) {
            return;
        }
        DB::table('ad_language')->where('id', $language->id)->update($values);
        LocaleRegistry::flush();
        Log::notice('Locale display names changed', ['code' => $language->code, 'values' => $values, 'actor' => $actor]);
    }
}
