<?php

namespace Tests\Feature\Mail;

use App\Mail\BookingAdminNotificationMail;
use App\Mail\BookingCancellationMail;
use App\Mail\BookingConfirmationMail;
use App\Mail\BookingPaymentMail;
use App\Models\Booking;
use Illuminate\Mail\Mailable;

/**
 * Phase 2B guarantee: EN and VI booking e-mails are byte-identical to the ones sent before locale-aware
 * mail existed. The snapshots in ./snapshots were recorded from the unchanged mailables and views (git
 * b6cc098) with MAIL_SNAPSHOT_RECORD=1; they are compared, never rewritten, in a normal run.
 *
 * Every case is rendered three times: as recorded (no bookings.locale column), with the column migrated
 * and empty, and with a guest locale that is not enabled for mail (e.g. ja while I18N_MAIL_LOCALES=en,vi).
 */
class LegacyBookingMailRegressionTest extends BookingMailTestCase
{
    public static function cases(): array
    {
        $cases = [];
        foreach (['en', 'en_deposit', 'vi', 'legacy'] as $kind) {
            $cases["confirmation-$kind"] = [$kind, 'confirmation'];
            $cases["cancellation-$kind"] = [$kind, 'cancellation'];
            $cases["admin-$kind"] = [$kind, 'admin'];
        }
        foreach (['en_deposit', 'vi'] as $kind) {
            foreach (BookingPaymentMail::TYPES as $type) {
                if ($type !== 'cure_period_ended') {
                    $cases["payment-$type-$kind"] = [$kind, 'payment:' . $type];
                }
            }
            $cases["payment-staff-overdue_notice-$kind"] = [$kind, 'staff:overdue_notice'];
            $cases["payment-staff-cure_period_ended-$kind"] = [$kind, 'staff:cure_period_ended'];
        }
        $cases['payment-payment_link-legacy'] = ['legacy', 'payment:payment_link'];

        return $cases;
    }

    private function mail(Booking $booking, string $which): Mailable
    {
        [$kind, $type] = array_pad(explode(':', $which, 2), 2, null);
        $link = 'https://greenrubycruises.test/booking/pay?t=fixture';
        $extra = ['expires' => 1798675200, 'amount' => $booking->currency === 'vnd' ? 7500000 : 30000];

        return match ($kind) {
            'confirmation' => new BookingConfirmationMail($booking),
            'cancellation' => new BookingCancellationMail($booking),
            'admin' => new BookingAdminNotificationMail($booking),
            'payment' => new BookingPaymentMail($booking, $type, $type === 'refund_processed' ? null : $link, $extra),
            'staff' => new BookingPaymentMail($booking, $type, $link, [], true),
        };
    }

    private function snapshot(string $name, string $actual): void
    {
        $file = __DIR__ . '/snapshots/' . str_replace(':', '.', $name) . '.txt';
        if (getenv('MAIL_SNAPSHOT_RECORD') === '1') {
            @mkdir(dirname($file), 0775, true);
            file_put_contents($file, $actual);
            $this->markTestIncomplete("recorded $name");
        }
        $this->assertFileExists($file, 'snapshot missing: record it from the pre-2B code');
        $this->assertSame(file_get_contents($file), $actual, "$name differs from the pre-2B e-mail");
    }

    /** @dataProvider cases */
    public function test_output_is_unchanged_without_a_locale_column(string $kind, string $which): void
    {
        $this->snapshot("$which-$kind", $this->capture($this->mail($this->booking($kind), $which)));
    }

    /** @dataProvider cases */
    public function test_output_is_unchanged_with_an_empty_locale(string $kind, string $which): void
    {
        if (getenv('MAIL_SNAPSHOT_RECORD') === '1') {
            $this->markTestSkipped('recording');
        }
        $this->migrateLocale();
        $this->snapshot("$which-$kind", $this->capture($this->mail($this->booking($kind), $which)));
    }

    /** @dataProvider cases */
    public function test_output_is_unchanged_for_the_stored_en_or_vi_locale_and_for_a_disabled_one(string $kind, string $which): void
    {
        if (getenv('MAIL_SNAPSHOT_RECORD') === '1') {
            $this->markTestSkipped('recording');
        }
        $this->migrateLocale();
        config(['i18n.mail.locales' => ['en', 'vi']]);
        $locale = $kind === 'vi' ? 'vi' : 'en';
        $this->snapshot("$which-$kind", $this->capture($this->mail($this->booking($kind, ['locale' => $locale]), $which)));

        // A Japanese guest (English inventory, USD) while ja mail is not enabled: today's e-mail.
        if ($kind !== 'vi') {
            Booking::query()->delete();
            $this->snapshot("$which-$kind", $this->capture($this->mail($this->booking($kind, ['locale' => 'ja']), $which)));
        }
    }
}
