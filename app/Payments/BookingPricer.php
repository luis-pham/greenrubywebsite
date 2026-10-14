<?php

namespace App\Payments;

use Illuminate\Support\Facades\DB;

/**
 * Prices a booking entirely from the database. Nothing the browser sends about prices is trusted:
 * only ids, quantities and guest counts are read from the request.
 *
 * price() returns null (and sets $error) instead of guessing when anything is missing or unknown,
 * so a payment can never be initialised for less than the real price.
 */
class BookingPricer
{
    public const MAX_QUANTITY = 10;

    /** Machine-readable reason for the last failed price() call. */
    public ?string $error = null;

    /**
     * @return array{
     *   currency: string,
     *   cabins: array<int, array{cabin_id:int, unit_price:float, quantity:int, total_price:float}>,
     *   amenities: array<int, array{amenity_id:int, unit_price:float, quantity:int, total_price:float}>,
     *   subtotal_cabins: float, subtotal_amenities: float, total: float
     * }|null
     */
    public function price(array $bookingData): ?array
    {
        $this->error = null;

        $duration = $this->itineraryDuration($bookingData['itinerary_id'] ?? null);
        if ($duration === null) {
            return $this->fail('itinerary_unknown');
        }

        $rows = $bookingData['cabins'] ?? [];
        if (!is_array($rows) || $rows === []) {
            return $this->fail('no_cabins');
        }

        $cabinIds = [];
        foreach ($rows as $row) {
            $id = $row['cabin_id'] ?? null;
            if (!is_numeric($id)) {
                return $this->fail('cabin_id_missing');
            }
            $cabinIds[] = (int) $id;
        }
        $cabinIds = array_values(array_unique($cabinIds));

        $cabins = DB::table('app_cabin')
            ->whereIn('id', $cabinIds)
            ->get(['id', 'language_id', 'capacity', 'over_capacity_adult', 'over_capacity_child_6_12', 'over_capacity_child_2_5', 'over_capacity_infant'])
            ->keyBy('id');
        if ($cabins->count() !== count($cabinIds)) {
            return $this->fail('cabin_unknown');
        }
        if ($cabins->pluck('language_id')->unique()->count() !== 1) {
            return $this->fail('cabin_language_mixed');
        }
        $languageId = (int) $cabins->first()->language_id;
        $currency = $this->currencyForLanguage($languageId);

        $priceMatrix = [];
        foreach (DB::table('app_cabin_price')->whereIn('cabin_id', $cabinIds)->where('duration', $duration)->get() as $p) {
            $priceMatrix[(int) $p->cabin_id][(int) $p->guest] = (float) $p->price;
        }

        $cabinLines = [];
        foreach ($rows as $row) {
            $cabin = $cabins->get((int) $row['cabin_id']);
            $quantity = $this->quantity($row['quantity'] ?? 1);
            if ($quantity === null) {
                return $this->fail('cabin_quantity_invalid');
            }
            $guests = [
                'adults' => max(0, (int) ($row['adults'] ?? 0)),
                'children_6_12' => max(0, (int) ($row['children_6_12'] ?? 0)),
                'children_2_5' => max(0, (int) ($row['children_2_5'] ?? 0)),
                'infants' => max(0, (int) ($row['infants'] ?? 0)),
            ];
            if (!$this->guestsAllowed($cabin, $guests)) {
                return $this->fail('cabin_guests_invalid');
            }
            $unit = $this->cabinPrice($cabin, $priceMatrix[$cabin->id] ?? [], $guests);
            if ($unit <= 0) {
                return $this->fail('cabin_price_missing');
            }
            $unit = $this->round($unit, $currency);
            $cabinLines[] = [
                'cabin_id' => (int) $cabin->id,
                'unit_price' => $unit,
                'quantity' => $quantity,
                'total_price' => $this->round($unit * $quantity, $currency),
            ];
        }

        $amenityLines = [];
        $amenityRows = array_filter($bookingData['amenities'] ?? [], fn ($row) => (int) ($row['quantity'] ?? 0) > 0);
        if ($amenityRows !== []) {
            $amenityIds = [];
            foreach ($amenityRows as $row) {
                if (!is_numeric($row['amenity_id'] ?? null)) {
                    return $this->fail('amenity_id_missing');
                }
                $amenityIds[] = (int) $row['amenity_id'];
            }
            // Only exclusive (paid, on-request) services, in the cabins' language: prices are stored in
            // that language's currency, so an English (USD) service must never be priced as VND.
            $services = DB::table('app_service')
                ->whereIn('id', array_unique($amenityIds))
                ->where('type', config('backend.appServiceType.exclusive', 2))
                ->where('language_id', $languageId)
                ->get(['id', 'price'])
                ->keyBy('id');
            foreach ($amenityRows as $row) {
                $service = $services->get((int) $row['amenity_id']);
                $quantity = $this->quantity($row['quantity']);
                if (!$service || $quantity === null) {
                    return $this->fail('amenity_unknown');
                }
                $unit = $this->round((float) $service->price, $currency);
                $amenityLines[] = [
                    'amenity_id' => (int) $service->id,
                    'unit_price' => $unit,
                    'quantity' => $quantity,
                    'total_price' => $this->round($unit * $quantity, $currency),
                ];
            }
        }

        $subtotalCabins = $this->round(array_sum(array_column($cabinLines, 'total_price')), $currency);
        $subtotalAmenities = $this->round(array_sum(array_column($amenityLines, 'total_price')), $currency);

        return [
            'currency' => $currency,
            'cabins' => $cabinLines,
            'amenities' => $amenityLines,
            'subtotal_cabins' => $subtotalCabins,
            'subtotal_amenities' => $subtotalAmenities,
            'total' => $this->round($subtotalCabins + $subtotalAmenities, $currency),
        ];
    }

    /** Same limits as public/assets/frontend/js/modules/booking/cabin-price-calculator.js. */
    protected function guestsAllowed(object $cabin, array $g): bool
    {
        $capacity = (int) $cabin->capacity;
        if ($capacity <= 0 || $g['adults'] + $g['children_6_12'] + $g['children_2_5'] <= 0) {
            return false;
        }
        $limit = fn ($value, $default) => (int) $value > 0 ? (int) $value : $default;

        return $g['adults'] <= $limit($cabin->over_capacity_adult, 2 * $capacity - 1)
            && $g['children_6_12'] <= $limit($cabin->over_capacity_child_6_12, $capacity)
            && $g['children_2_5'] <= $limit($cabin->over_capacity_child_2_5, $capacity)
            && $g['infants'] <= $limit($cabin->over_capacity_infant, $capacity);
    }

    /**
     * Price for one cabin: the matrix price for the guest count; above capacity, the capacity price
     * plus extra guests at 100% (adult), 75% (child 6–12) and 50% (child 2–5) of the per-person rate.
     * Infants are free.
     */
    protected function cabinPrice(object $cabin, array $pricesByGuest, array $g): float
    {
        $capacity = (int) $cabin->capacity;
        $guests = $g['adults'] + $g['children_6_12'] + $g['children_2_5'];

        if ($guests <= $capacity) {
            $price = $pricesByGuest[$guests] ?? 0.0;
            if ($price <= 0 && ($pricesByGuest[$capacity] ?? 0) > 0) {
                $price = $pricesByGuest[$capacity] / $capacity * $guests;
            }
            return $price;
        }

        $base = $pricesByGuest[$capacity] ?? 0.0;
        if ($base <= 0) {
            return 0.0;
        }
        $perPerson = $base / $capacity;

        // Fill the base capacity with adults first, then older children, then younger children.
        $room = $capacity;
        $extra = 0.0;
        foreach (['adults' => 1.0, 'children_6_12' => 0.75, 'children_2_5' => 0.5] as $type => $rate) {
            $inBase = min($g[$type], $room);
            $room -= $inBase;
            $extra += ($g[$type] - $inBase) * $perPerson * $rate;
        }

        return $base + $extra;
    }

    /**
     * The booking page sends the itinerary as "{itineraryId}-{cruiseId}" (the id format of
     * /api/booking/itineraries); plain numeric ids are accepted too.
     */
    public static function parseItineraryId($raw): ?int
    {
        if (is_int($raw)) {
            return $raw > 0 ? $raw : null;
        }
        if (is_string($raw) && preg_match('/^\s*(\d+)(?:-\d+)?\s*$/', $raw, $m)) {
            return (int) $m[1] ?: null;
        }

        return null;
    }

    protected function itineraryDuration($rawItineraryId): ?int
    {
        $itineraryId = self::parseItineraryId($rawItineraryId);
        if ($itineraryId === null) {
            return null;
        }
        $duration = DB::table('app_itinerary')->where('id', $itineraryId)->value('duration');

        return $duration === null ? null : (int) $duration;
    }

    protected function currencyForLanguage(int $languageId): string
    {
        $code = strtolower((string) DB::table('ad_language')->where('id', $languageId)->value('code'));

        return $code === 'vi' ? 'vnd' : 'usd';
    }

    protected function quantity($value): ?int
    {
        $quantity = is_numeric($value) ? (int) $value : 0;

        return $quantity >= 1 && $quantity <= self::MAX_QUANTITY ? $quantity : null;
    }

    protected function round(float $amount, string $currency): float
    {
        return round($amount, $currency === 'vnd' ? 0 : 2);
    }

    protected function fail(string $reason): ?array
    {
        $this->error = $reason;

        return null;
    }
}
