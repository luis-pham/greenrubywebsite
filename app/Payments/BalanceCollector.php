<?php

namespace App\Payments;

use App\Mail\BookingPaymentMail;
use App\Models\Booking;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * TPX decision 3, run once a day (bookings:collect-balances):
 *  D-45 balance request with a payment link, D-37 and D-32 reminders (staff copied on the second),
 *  D-30 formal overdue notice with `cure_hours` more to pay, then a staff alert when that time is up.
 * It never cancels a booking or keeps money: those decisions stay with authorised staff.
 *
 * Each stage is sent at most once (booking_notifications has a unique booking/type key). When the job
 * has not run for a while, only the latest guest stage due is sent and the earlier ones are recorded
 * as skipped, so a guest never receives three emails in one day.
 */
final class BalanceCollector
{
    /** @var string[] lines describing what was (or would be) done */
    public array $report = [];

    public static function enabled(): bool
    {
        return (bool) config('booking.balance_collection_enabled') && BookingAudit::ready();
    }

    public function run(bool $dryRun = false, ?CarbonImmutable $today = null): array
    {
        $this->report = [];
        $today = $today ?? PaymentPlan::today();

        $bookings = Booking::query()
            ->where('payment_plan', PaymentPlan::DEPOSIT)
            ->where('status', Booking::statusConfirmed())
            ->whereIn('payment_status', [Booking::PAYMENT_DEPOSIT_PAID, Booking::PAYMENT_OVERDUE])
            ->whereNotNull('departure_date')
            ->whereDate('departure_date', '>=', $today->toDateString())
            ->orderBy('departure_date')
            ->get();

        foreach ($bookings as $booking) {
            if ($booking->balanceAmount() <= 0) {
                continue;
            }
            $this->collect($booking, $today, $dryRun);
        }

        return $this->report;
    }

    private function collect(Booking $booking, CarbonImmutable $today, bool $dryRun): void
    {
        $days = PaymentPlan::daysBefore($booking->departure_date->toDateString(), $today);
        $sent = DB::table('booking_notifications')->where('booking_id', $booking->id)->pluck('type')->all();

        // Guest stages in order; the first element of each pair is "due when days <= x".
        $stages = [
            ['balance_request', (int) config('booking.balance_request_days', 45)],
            ['balance_reminder_1', (int) (config('booking.balance_reminder_days')[0] ?? 37)],
            ['balance_reminder_2', (int) (config('booking.balance_reminder_days')[1] ?? 32)],
            ['overdue_notice', (int) config('booking.balance_days_before', 30)],
        ];
        $due = array_values(array_filter($stages, fn ($s) => $days <= $s[1] && !in_array($s[0], $sent, true)));

        if ($due) {
            $latest = end($due)[0];
            foreach ($due as [$type]) {
                if ($type !== $latest) {
                    $this->record($booking, $type, null, ['skipped' => true, 'days_before' => $days], $dryRun);
                }
            }
            $this->sendStage($booking, $latest, $days, $dryRun);

            return;
        }

        // Cure period over and still unpaid: tell Reservations once. Never cancel automatically.
        if ($booking->payment_status === Booking::PAYMENT_OVERDUE && $booking->cure_deadline_at
            && $booking->cure_deadline_at->lte(now()) && !in_array('cure_period_ended', $sent, true)) {
            $this->staff($booking, 'cure_period_ended', null, $dryRun);
            $this->record($booking, 'cure_period_ended', config('booking.reservations_email'), ['days_before' => $days], $dryRun);
        }
    }

    private function sendStage(Booking $booking, string $type, int $days, bool $dryRun): void
    {
        if ($type === 'overdue_notice' && !$dryRun) {
            $booking->update([
                'payment_status' => Booking::PAYMENT_OVERDUE,
                'payment_overdue_at' => now(),
                'cure_deadline_at' => now()->addHours((int) config('booking.cure_hours', 48)),
            ]);
        }

        // The link stays valid a few days past the cure period so a late payer is not stuck.
        $expires = $booking->departure_date->copy()->endOfDay()->getTimestamp();
        $link = PaymentLink::url($booking, PaymentLink::BALANCE, $expires);

        if (!$dryRun && $booking->email) {
            try {
                Mail::to($booking->email)->send(new BookingPaymentMail($booking, $type, $link));
            } catch (\Throwable $e) {
                // Not recorded as sent: the next run tries again.
                Log::error('Balance collection email failed', ['booking' => $booking->code, 'type' => $type, 'error' => $e->getMessage()]);
                $this->report[] = "{$booking->code} {$type} FAILED: {$e->getMessage()}";

                return;
            }
        }
        if (in_array($type, ['balance_reminder_2', 'overdue_notice'], true)) {
            $this->staff($booking, $type, $link, $dryRun);
        }
        $this->record($booking, $type, $booking->email, ['days_before' => $days, 'balance' => $booking->balanceAmount()], $dryRun);
    }

    private function staff(Booking $booking, string $type, ?string $link, bool $dryRun): void
    {
        $to = config('booking.reservations_email');
        if ($dryRun || !$to) {
            return;
        }
        try {
            Mail::to($to)->send(new BookingPaymentMail($booking, $type, $link, [], true));
        } catch (\Throwable $e) {
            Log::error('Balance collection staff email failed', ['booking' => $booking->code, 'type' => $type, 'error' => $e->getMessage()]);
        }
    }

    private function record(Booking $booking, string $type, ?string $recipient, array $meta, bool $dryRun): void
    {
        $this->report[] = trim(sprintf('%s %s%s (D-%d)', $booking->code, $type, empty($meta['skipped']) ? '' : ' skipped', $meta['days_before'] ?? 0));
        if ($dryRun) {
            return;
        }
        DB::table('booking_notifications')->insertOrIgnore([
            'booking_id' => $booking->id,
            'type' => $type,
            'recipient' => $recipient,
            'meta' => json_encode($meta),
            'sent_at' => now(),
        ]);
        BookingAudit::log($booking->id, 'notification.' . $type, null, $meta + ['recipient' => $recipient]);
    }
}
