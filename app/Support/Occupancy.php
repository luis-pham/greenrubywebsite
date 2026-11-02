<?php

namespace App\Support;

/**
 * Maximum guest combinations of a cabin, stored in app_cabin.occupancy_limits as "adults+children" pairs, e.g.
 * "2+2, 3+0": at most 2 adults with 2 children, or 3 adults alone. Children count every age, infants included.
 * Mirrored by greenruby-web src/views/booking/pricing.ts (occupancyLimits): keep both sides identical.
 */
class Occupancy
{
    public const PATTERN = '/^\s*\d{1,2}\s*\+\s*\d{1,2}\s*(,\s*\d{1,2}\s*\+\s*\d{1,2}\s*)*$/';

    /** Whether app_cabin has the column (code deployed before its migration, or rolled back past it, must still book). */
    public static function available(): bool
    {
        if (!app()->bound('occupancy.available')) {
            app()->instance('occupancy.available', \Illuminate\Support\Facades\Schema::hasColumn('app_cabin', 'occupancy_limits'));
        }
        return app('occupancy.available');
    }

    /** @return list<array{adults: int, children: int}> empty when unset or malformed */
    public static function parse(?string $value): array
    {
        if ($value === null || !preg_match(self::PATTERN, $value)) {
            return [];
        }
        return array_map(function (string $pair) {
            [$adults, $children] = array_map('intval', explode('+', $pair));
            return ['adults' => $adults, 'children' => $children];
        }, explode(',', str_replace(' ', '', $value)));
    }

    /** Whether the guests fit one of the combinations. */
    public static function fits(array $limits, int $adults, int $children): bool
    {
        foreach ($limits as $limit) {
            if ($adults <= $limit['adults'] && $children <= $limit['children']) {
                return true;
            }
        }
        return false;
    }

    /** Canonical form for storage ("2+2, 3+0"); null when empty or malformed. */
    public static function normalize(?string $value): ?string
    {
        $limits = self::parse($value);
        return $limits === [] ? null : implode(', ', array_map(fn ($l) => "{$l['adults']}+{$l['children']}", $limits));
    }
}
