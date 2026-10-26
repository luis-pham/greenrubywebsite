<?php

namespace App\Payments;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Deposit or full payment for a retail booking (TPX decision 2, config/booking.php):
 *  - departing more than `deposit_min_days` (45) days from today: 30% deposit by default, full payment optional;
 *  - otherwise: full payment;
 *  - the balance is due `balance_days_before` (30) days before departure.
 * Days are Vietnam calendar days. Amounts are major units (USD dollars, whole VND), like BookingPricer.
 */
final class PaymentPlan
{
    public const FULL = 'full';
    public const DEPOSIT = 'deposit';

    public static function enabled(): bool
    {
        return (bool) config('booking.deposit_enabled') && BookingSchema::ready();
    }

    public static function today(): CarbonImmutable
    {
        return CarbonImmutable::now(config('booking.timezone', 'Asia/Ho_Chi_Minh'))->startOfDay();
    }

    /** Calendar days from $today to the departure date (negative when it is in the past). */
    public static function daysBefore(string $departureDate, ?CarbonImmutable $today = null): int
    {
        $today = $today ?? self::today();
        $departure = CarbonImmutable::createFromFormat('!Y-m-d', $departureDate, $today->getTimezone());

        return (int) $today->diffInDays($departure, false);
    }

    /** @return string[] plans the guest may choose, the default first */
    public static function options(string $departureDate, ?CarbonImmutable $today = null): array
    {
        return self::daysBefore($departureDate, $today) > (int) config('booking.deposit_min_days', 45)
            ? [self::DEPOSIT, self::FULL]
            : [self::FULL];
    }

    /** The plan to use: the requested one when allowed, otherwise the default. */
    public static function choose(?string $requested, string $departureDate, ?CarbonImmutable $today = null): string
    {
        $options = self::options($departureDate, $today);

        return in_array($requested, $options, true) ? $requested : $options[0];
    }

    /**
     * @return array{due_now: float, balance: float} deposit rounded to the currency's unit; the balance
     *         is the remainder, so due_now + balance is always exactly the total
     */
    public static function amounts(float $total, string $currency, string $plan): array
    {
        if ($plan !== self::DEPOSIT) {
            return ['due_now' => $total, 'balance' => 0.0];
        }
        $decimals = strtolower($currency) === 'vnd' ? 0 : 2;
        $deposit = round($total * (int) config('booking.deposit_percent', 30) / 100, $decimals);

        return ['due_now' => $deposit, 'balance' => round($total - $deposit, $decimals)];
    }

    public static function balanceDueDate(string $departureDate): string
    {
        return CarbonImmutable::createFromFormat('!Y-m-d', $departureDate)
            ->subDays((int) config('booking.balance_days_before', 30))
            ->toDateString();
    }

    /**
     * The departure date decides how much is charged, so it must be a scheduled departure of the
     * booked itinerary ("{itineraryId}-{cruiseId}"), not just any date the browser sends.
     */
    public static function isScheduledDeparture($rawItineraryId, string $departureDate): bool
    {
        $itineraryId = BookingPricer::parseItineraryId($rawItineraryId);
        if ($itineraryId === null) {
            return false;
        }
        $query = DB::table('app_cruise_itinerary')
            ->where('itinerary_id', $itineraryId)
            ->whereDate('start_at', $departureDate);
        if (is_string($rawItineraryId) && preg_match('/^\s*\d+-(\d+)\s*$/', $rawItineraryId, $m)) {
            $query->where('cruise_id', (int) $m[1]);
        }

        return $query->exists();
    }
}
