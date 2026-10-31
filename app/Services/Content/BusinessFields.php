<?php

namespace App\Services\Content;

use App\Payments\PriceList;
use Illuminate\Support\Facades\DB;

/**
 * Business data of text-only languages (phase 1E-b/c; 12 §9, I-9, I-10). A language without its own price list
 * (ja → en) owns only text: prices, capacities, specifications and times come from its linked row in the
 * price-list language (the "inventory row"). This class finds that row, overlays its business values for reading
 * (Content API, admin read-only panels), and refuses business writes on text-only rows (BusinessFieldsObserver).
 *
 * en/vi rows have their own price list: every method returns them unchanged, so their output never changes.
 */
final class BusinessFields
{
    /** True when rows in this language carry their own business data (en, vi). */
    public static function ownsBusinessData(int $languageId): bool
    {
        return PriceList::currencyForLanguageId($languageId) !== null;
    }

    /**
     * The price-list row behind $id: grouped types through the explicit translation link (PriceList::inventoryId),
     * the other types through translation_meta (the source the row was translated from, when it is in the
     * price-list language). Null when there is none.
     */
    public static function inventoryId(string $type, int $id, int $languageId): ?int
    {
        if (self::ownsBusinessData($languageId)) {
            return $id;
        }
        $meta = TranslatableFields::of($type);
        if ($meta['grouped']) {
            return PriceList::inventoryId($type, $id, $languageId);
        }
        $source = DB::table('translation_meta')->where('entity_type', $type)->where('entity_id', $id)->first();
        if (!$source || !$source->source_entity_id || !self::ownsBusinessData((int) $source->source_language_id)) {
            return null;
        }

        return (int) $source->source_entity_id;
    }

    /**
     * $row with its business attributes read from the inventory row (a clone; $row itself is never modified).
     * Returns $row itself for en/vi, for types without business fields, and when no inventory row is linked
     * (the caller then shows the row's own mirror values, which TranslationFactory copied from the source).
     */
    public static function overlay(string $type, $row)
    {
        if (!$row || !TranslatableFields::has($type) || TranslatableFields::business($type) === []) {
            return $row;
        }
        $languageId = (int) ($row->language_id ?? 0);
        if (self::ownsBusinessData($languageId)) {
            return $row;
        }
        $inventoryId = self::inventoryId($type, (int) $row->id, $languageId);
        $inventory = $inventoryId ? DB::table(TranslatableFields::of($type)['table'])->where('id', $inventoryId)->first() : null;
        if (!$inventory) {
            return $row;
        }
        $copy = clone $row;
        foreach (TranslatableFields::business($type) as $column) {
            if (property_exists($inventory, $column)) {
                $copy->{$column} = $inventory->{$column};
            }
        }

        return $copy;
    }

    /**
     * Refuses inventory writes (cabin prices, departures) for a text-only language. Called by the admin services that
     * write those tables in bulk (query-builder deletes bypass model events).
     */
    public static function assertInventoryWritable(int $languageId, string $what): void
    {
        if ($languageId && !self::ownsBusinessData($languageId)) {
            throw new \App\Services\Content\EditorialRefusal("Không sửa được {$what} ở ngôn ngữ này: dữ liệu đặt chỗ lấy từ bản EN (sửa ở bản EN).");
        }
    }

    /** Business columns of $type that $changes tries to write on a text-only row (empty = allowed). */
    public static function forbiddenChanges(string $type, int $languageId, array $changes): array
    {
        if (!TranslatableFields::has($type) || self::ownsBusinessData($languageId)) {
            return [];
        }

        return array_values(array_intersect(array_keys($changes), TranslatableFields::business($type)));
    }
}
