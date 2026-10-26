<?php

namespace App\Payments;

use App\Mail\BookingPaymentMail;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentRefund;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Refunds with approval (TPX decision 5): Reservations requests, Finance approves or rejects (never their
 * own request), then Finance makes the refund in Stripe, PayPal or by bank transfer and records it here.
 * Nothing is refunded automatically and no gateway is called. Every step goes to booking_audit_log.
 * Amounts are storage units (cents for USD, whole VND).
 */
final class RefundService
{
    public static function isFinance(?int $userId): bool
    {
        return $userId !== null
            && DB::table('ad_user_role')->where('user_id', $userId)->whereIn('role_id', config('booking.finance_roles', [1]))->exists();
    }

    /**
     * What the Booking Terms give back for a reason, as a starting point for Reservations (Finance decides).
     *
     * @return array{amount: ?int, basis: string}
     */
    public static function suggest(Booking $booking, string $reason, ?string $cancelledOn = null): array
    {
        $paid = $booking->netPaid();
        $total = (int) $booking->total_amount;

        switch ($reason) {
            case 'weather':
            case 'operator_cancellation':
                return ['amount' => $paid, 'basis' => '§8/§9: full refund of the affected cruise booking'];

            case 'non_payment':
                // Decision 4: keep at most the deposit received (30% of the total), claim nothing more.
                $kept = min($paid, (int) round($total * (int) config('booking.non_payment_retention_percent', 30) / 100));

                return ['amount' => max(0, $paid - $kept), 'basis' => '§4.4: up to the deposit received may be kept'];

            case 'guest_cancellation':
                if (!$booking->departure_date) {
                    return ['amount' => null, 'basis' => 'no departure date'];
                }
                $days = PaymentPlan::daysBefore($booking->departure_date->toDateString(), $cancelledOn ? \Carbon\CarbonImmutable::parse($cancelledOn, config('booking.timezone')) : null);
                $chargePercent = $days >= 30 ? 0 : ($days >= 15 ? 50 : ($days >= 7 ? 75 : 100));
                $charge = (int) round($total * $chargePercent / 100);

                return ['amount' => max(0, $paid - $charge), 'basis' => "§5: {$days} days before departure, charge {$chargePercent}% of the total"];
        }

        return ['amount' => null, 'basis' => 'decided case by case'];
    }

    /** Received, minus refunds already made or still in the approval pipeline. */
    public static function refundable(Booking $booking): int
    {
        $open = (int) PaymentRefund::where('booking_id', $booking->id)
            ->whereIn('status', [PaymentRefund::REQUESTED, PaymentRefund::APPROVED])
            ->sum('amount');

        return max(0, $booking->netPaid() - $open);
    }

    public static function request(Booking $booking, int $amount, string $reason, ?string $note, int $userId): PaymentRefund
    {
        if (!in_array($reason, PaymentRefund::REASONS, true)) {
            throw new \DomainException('Lý do hoàn tiền không hợp lệ.');
        }
        if ($amount <= 0 || $amount > self::refundable($booking)) {
            throw new \DomainException('Số tiền hoàn phải lớn hơn 0 và không vượt quá số tiền đã nhận còn có thể hoàn.');
        }
        if ($reason === 'other' && trim((string) $note) === '') {
            throw new \DomainException('Lý do "khác" cần ghi rõ giải thích.');
        }
        $payments = Payment::where('booking_id', $booking->id)->where('status', Payment::statusSucceeded())->pluck('id');

        $refund = PaymentRefund::create([
            'booking_id' => $booking->id,
            'payment_id' => $payments->count() === 1 ? $payments->first() : null,
            'amount' => $amount,
            'currency' => $booking->currency,
            'reason' => $reason,
            'note' => $note,
            'status' => PaymentRefund::REQUESTED,
            'requested_by' => $userId,
            'requested_at' => now(),
        ]);
        BookingAudit::log($booking->id, 'refund.requested', $userId, ['refund_id' => $refund->id, 'amount' => $amount, 'reason' => $reason, 'suggested' => self::suggest($booking, $reason)['amount']]);

        return $refund;
    }

    public static function decide(PaymentRefund $refund, bool $approve, ?string $note, int $userId): void
    {
        self::assertFinance($userId);
        if ($refund->requested_by === $userId) {
            throw new \DomainException('Người đề nghị không được tự duyệt đề nghị hoàn tiền của mình.');
        }
        if (!$approve && trim((string) $note) === '') {
            throw new \DomainException('Từ chối cần ghi lý do.');
        }
        self::transition($refund, PaymentRefund::REQUESTED, [
            'status' => $approve ? PaymentRefund::APPROVED : PaymentRefund::REJECTED,
            'decided_by' => $userId,
            'decided_at' => now(),
            'decision_note' => $note,
        ]);
        BookingAudit::log($refund->booking_id, $approve ? 'refund.approved' : 'refund.rejected', $userId, ['refund_id' => $refund->id, 'note' => $note]);
    }

    /** Finance confirms the money has been sent back (gateway refund id or bank transfer reference). */
    public static function markProcessed(PaymentRefund $refund, string $providerReference, int $userId, bool $notifyGuest = true): void
    {
        self::assertFinance($userId);
        if (trim($providerReference) === '') {
            throw new \DomainException('Cần nhập mã hoàn tiền của Stripe/PayPal hoặc mã giao dịch ngân hàng.');
        }
        DB::transaction(function () use ($refund, $providerReference, $userId) {
            self::transition($refund, PaymentRefund::APPROVED, [
                'status' => PaymentRefund::PROCESSED,
                'processed_by' => $userId,
                'processed_at' => now(),
                'provider_reference' => $providerReference,
            ]);
            $booking = Booking::whereKey($refund->booking_id)->lockForUpdate()->first();
            $booking->update(['amount_refunded' => (int) $booking->amount_refunded + $refund->amount]);
            $booking->refreshPaidAmount();
        });
        BookingAudit::log($refund->booking_id, 'refund.processed', $userId, ['refund_id' => $refund->id, 'amount' => $refund->amount, 'reference' => $providerReference]);

        $booking = Booking::find($refund->booking_id);
        if ($notifyGuest && $booking->email) {
            try {
                Mail::to($booking->email)->send(new BookingPaymentMail($booking, 'refund_processed', null, ['amount' => $refund->amount]));
            } catch (\Throwable $e) {
                Log::error('Refund email failed', ['booking' => $booking->code, 'error' => $e->getMessage()]);
            }
        }
    }

    private static function assertFinance(int $userId): void
    {
        if (!self::isFinance($userId)) {
            throw new \DomainException('Chỉ Finance được duyệt và ghi nhận hoàn tiền.');
        }
    }

    /** Moves the refund from $from under a row lock, so two clicks cannot decide or record it twice. */
    private static function transition(PaymentRefund $refund, string $from, array $changes): void
    {
        DB::transaction(function () use ($refund, $from, $changes) {
            $current = PaymentRefund::whereKey($refund->id)->lockForUpdate()->first();
            if (!$current || $current->status !== $from) {
                throw new \DomainException('Đề nghị hoàn tiền đã được xử lý trước đó.');
            }
            $current->update($changes);
            $refund->setRawAttributes($current->getAttributes(), true);
        });
    }
}
