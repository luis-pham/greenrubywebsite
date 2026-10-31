<?php

namespace App\Payments;

use App\Services\Content\ContentTranslations;
use App\Services\Locale\LocaleRegistry;
use Illuminate\Support\Facades\DB;

/**
 * Which price list (and therefore which currency and which bookable rows) a language uses.
 *
 * Prices, departures and booking ids live on the rows of a *price-list language* (en = USD, vi = VND,
 * config/booking.php `price_lists`). Any other display language borrows one of them
 * (`price_list_for_locale`, e.g. ja → en): its own cabin/itinerary rows are text only and never become
 * booking inventory. Currency is therefore taken from the price list, never from the display language.
 */
final class PriceList
{
    /** Currency (lower case) of a price-list language code, or null when the code has no price list of its own. */
    public static function currencyFor(string $code): ?string
    {
        $currency = config('booking.price_lists.' . strtolower($code));

        return is_string($currency) && $currency !== '' ? strtolower($currency) : null;
    }

    /** Currency of the rows stored in this language (ad_language.id), or null when it has no price list. */
    public static function currencyForLanguageId(int $languageId): ?string
    {
        $code = (string) DB::table('ad_language')->where('id', $languageId)->value('code');

        return $code === '' ? null : self::currencyFor($code);
    }

    /**
     * The price-list language whose rows a page in $code books and prices: itself when it has a price list,
     * else the configured one (ja → en). Codes that are neither are returned unchanged, so callers keep their
     * existing fallback for them.
     */
    public static function inventoryLanguageCode(string $code): string
    {
        $code = strtolower(trim($code));
        if (self::currencyFor($code) !== null) {
            return $code;
        }
        // The registry (ad_language.price_list_language_id, phase 1B) first; config for older schemas and tests.
        $borrowed = (LocaleRegistry::migrated() ? LocaleRegistry::priceListCode($code) : null)
            ?? config('booking.price_list_for_locale.' . $code);

        return is_string($borrowed) && self::currencyFor($borrowed) !== null ? strtolower($borrowed) : $code;
    }

    /** Display currency (upper case) for pages in $code; USD when nothing is configured, as before. */
    public static function displayCurrency(string $code): string
    {
        return strtoupper(self::currencyFor(self::inventoryLanguageCode($code)) ?? 'usd');
    }

    /**
     * The bookable row behind a displayed row: the row itself when its language has a price list, else its
     * explicitly linked translation in the inventory language, or null when there is none. Heuristic pairing
     * is never used here: a guessed pair must not decide what a guest books and pays.
     */
    public static function inventoryId(string $entityType, int $entityId, int $languageId): ?int
    {
        $code = (string) DB::table('ad_language')->where('id', $languageId)->value('code');
        if ($code !== '' && self::currencyFor($code) !== null) {
            return $entityId;
        }
        $inventoryCode = self::inventoryLanguageCode($code);
        $target = self::currencyFor($inventoryCode) === null ? null : DB::table('ad_language')->where('code', $inventoryCode)->value('id');

        return $target === null ? null : ContentTranslations::linkedId($entityType, $entityId, (int) $target);
    }
}
