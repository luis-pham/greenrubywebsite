<?php

namespace Tests\Feature\Mail;

use App\Mail\BookingAdminNotificationMail;
use App\Mail\BookingCancellationMail;
use App\Mail\BookingConfirmationMail;
use App\Mail\BookingPaymentMail;
use App\Models\Booking;
use App\Services\Mail\BookingMailLocale;
use Illuminate\Support\Facades\Mail;

/**
 * Phase 2B Japanese DRAFT e-mails and the I18N_MAIL_LOCALES safety switch. Rendering only, nothing is sent.
 */
class JapaneseBookingMailTest extends BookingMailTestCase
{
    private const LINK = 'https://greenrubycruises.test/booking/pay?t=fixture';

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateLocale();
    }

    private function enableJa(): void
    {
        config(['i18n.mail.locales' => ['en', 'vi', 'ja']]);
    }

    /** A Japanese guest: English inventory in USD, English terms accepted (owner decision D). */
    private function jaBooking(string $kind = 'en_deposit', string $locale = 'ja'): Booking
    {
        return $this->booking($kind, ['locale' => $locale, 'full_name' => '山田 花子']);
    }

    private function legacySnapshot(string $name): string
    {
        return file_get_contents(__DIR__ . '/snapshots/' . $name . '.txt');
    }

    /** Envelope/HTML of a Japanese guest's mail, rendered as if the guest's name were the fixture's. */
    private function asLegacyGuest(string $captured): string
    {
        return str_replace('山田 花子', 'Jane Doe', $captured);
    }

    private function assertNoPlaceholderLeft(string $out): void
    {
        $this->assertDoesNotMatchRegularExpression('/:(code|name|voyage|departure|total|paid|balance|due|deadline|amount|expires|days)\b/', $out);
    }

    /** No cancellation/refund schedule is hard-coded in the drafts: it is linked, not copied. */
    private function assertNoPolicyText(string $out): void
    {
        foreach (['50%', '75%', '100%', '30%', '15〜29', '7〜14', '0〜6', 'ノーショー'] as $rule) {
            $this->assertStringNotContainsString($rule, $out);
        }
    }

    public function test_the_switch_defaults_to_en_and_vi_and_parses_the_env_list(): void
    {
        $this->assertFalse(getenv('I18N_MAIL_LOCALES'), 'run the suite without I18N_MAIL_LOCALES');
        $this->assertSame(['en', 'vi'], (require config_path('i18n.php'))['mail']['locales']);
        $this->assertSame(['en', 'vi'], BookingMailLocale::enabled());

        config(['i18n.mail.locales' => ' EN, vi ,ja,']);
        $this->assertSame(['en', 'vi', 'ja'], BookingMailLocale::enabled());
    }

    public function test_locale_resolution_falls_back_to_today_unless_drafted_and_enabled(): void
    {
        $this->assertNull(BookingMailLocale::for($this->jaBooking()), 'ja is off by default');
        Booking::query()->delete();

        $this->enableJa();
        $this->assertSame('ja', BookingMailLocale::for($b = $this->jaBooking('en', 'ja-JP')));
        $b->forceFill(['locale' => 'en'])->save();
        $this->assertNull(BookingMailLocale::for($b), 'en keeps the pre-2B e-mail');
        $b->forceFill(['locale' => 'vi'])->save();
        $this->assertNull(BookingMailLocale::for($b), 'vi keeps the pre-2B e-mail');
        $b->forceFill(['locale' => 'fr'])->save();
        $this->assertNull(BookingMailLocale::for($b), 'no fr templates');
        $b->forceFill(['locale' => '../x'])->save();
        $this->assertNull(BookingMailLocale::for($b));
        $b->forceFill(['locale' => null])->save();
        $this->assertNull(BookingMailLocale::for($b));

        $this->assertSame('ja', BookingMailLocale::normalize('ja_JP'));
        $this->assertNull(BookingMailLocale::normalize(''));
        $this->assertNull(BookingMailLocale::normalize(['ja']));
    }

    public function test_switch_off_a_japanese_guest_gets_todays_english_emails(): void
    {
        $b = $this->jaBooking();
        $this->assertSame($this->legacySnapshot('confirmation-en_deposit'), $this->asLegacyGuest($this->capture(new BookingConfirmationMail($b))));
        $this->assertSame($this->legacySnapshot('cancellation-en_deposit'), $this->asLegacyGuest($this->capture(new BookingCancellationMail($b))));
        $this->assertSame(
            $this->legacySnapshot('payment.overdue_notice-en_deposit'),
            $this->asLegacyGuest($this->capture(new BookingPaymentMail($b, 'overdue_notice', self::LINK, ['expires' => 1798675200, 'amount' => 30000])))
        );
    }

    public function test_switch_on_confirmation_is_japanese(): void
    {
        $this->enableJa();
        $out = $this->capture(new BookingConfirmationMail($b = $this->jaBooking()));

        $this->assertStringStartsWith("Subject: ご予約内容のご確認（予約番号：{$b->code}）\n", $out);
        $this->assertStringContainsString("To: <guest-en_deposit@example.test>\n", $out);
        $this->assertStringContainsString('<html lang="ja">', $out);
        $this->assertStringContainsString('<p>山田 花子 様</p>', $out);
        $this->assertStringContainsString('出発日：2026年12月26日', $out);
        $this->assertStringContainsString('人数：大人2名、子ども（6〜12歳）1名、乳幼児1名', $out);
        $this->assertStringContainsString('人数：大人1名、子ども（2〜5歳）1名', $out);
        $this->assertStringContainsString('客室料金：US$640', $out);
        $this->assertStringContainsString('料金：US$40', $out);
        $this->assertStringContainsString('合計金額：US$1,000', $out);
        $this->assertStringContainsString('お支払い済みの予約金：US$300', $out);
        $this->assertStringContainsString('残金：US$700（お支払い期日：2026年11月26日）', $out);
        $this->assertStringContainsString('ご出発の約45日前', $out);
        $this->assertStringContainsString('・PayPal', $out);
        $this->assertStringContainsString('data-review="LEGAL_REVIEW_REQUIRED" data-review-id="confirmation.payment"', $out);
        $this->assertStringContainsString('data-review="LEGAL_REVIEW_REQUIRED" data-review-id="confirmation.cancellation"', $out);
        $this->assertStringContainsString('href="https://greenrubycruises.test/booking-terms-and-conditions#cancellation"', $out);
        $this->assertStringContainsString('href="https://greenrubycruises.test/booking-terms-and-conditions#refunds"', $out);
        $this->assertStringNotContainsString('CANCELLATION POLICY', $out);
        $this->assertNoPolicyText($out);
        $this->assertNoPlaceholderLeft($out);
    }

    public function test_switch_on_cancellation_is_japanese(): void
    {
        $this->enableJa();
        $out = $this->capture(new BookingCancellationMail($b = $this->jaBooking('en')));

        $this->assertStringStartsWith("Subject: ご予約キャンセルのご確認（予約番号：{$b->code}）\n", $out);
        $this->assertStringContainsString('<html lang="ja">', $out);
        $this->assertStringContainsString('お支払い状況：キャンセル済み', $out);
        $this->assertStringContainsString('ご予約の合計金額：US$1,000', $out);
        $this->assertStringContainsString('data-review-id="cancellation.statement"', $out);
        $this->assertStringContainsString('data-review-id="cancellation.payment_and_refund"', $out);
        $this->assertStringContainsString('#refunds"', $out);
        $this->assertNoPolicyText($out);
        $this->assertNoPlaceholderLeft($out);
    }

    public function test_switch_on_payment_messages_are_japanese(): void
    {
        $this->enableJa();
        $b = $this->jaBooking();
        $expected = [
            'payment_link' => ['ご予約手続きのご案内（予約番号：', ['リンクの有効期限は2026年12月31日です。', '確認してお支払い', 'payment_link.terms_and_payment', '#payment"']],
            'balance_request' => ['残金のお支払いのご案内（予約番号：', ['2026年11月26日（ご出発の30日前）まで', '<td>残金</td><td><strong>US$700</strong>', '<td>お支払い済み</td><td><strong>US$300</strong>', 'balance_request.due']],
            'balance_reminder_1' => ['残金のお支払い期日のお知らせ（予約番号：', ['残金US$700のお支払い期日は2026年11月26日です。', 'balance_reminder_1.due']],
            'balance_reminder_2' => ['【再度のお知らせ】残金のお支払い期日（予約番号：', ['balance_reminder_2.due', '行き違い']],
            'overdue_notice' => ['【重要】残金のお支払い期日経過のお知らせ（予約番号：', ['2026年11月28日 03:00（ベトナム時間）までに', 'overdue_notice.formal_notice', 'overdue_notice.cure_deadline', 'overdue_notice.consequence', 'payment.terms_link']],
            'refund_processed' => ['返金手続き完了のお知らせ（予約番号：', ['US$300の返金手続き', 'refund_processed.refund', 'refund_processed.timing', '#refunds"']],
        ];
        foreach ($expected as $type => [$subject, $needles]) {
            $mail = new BookingPaymentMail($b, $type, $type === 'refund_processed' ? null : self::LINK, ['expires' => 1798675200, 'amount' => 30000]);
            $out = $this->capture($mail);
            $this->assertStringStartsWith('Subject: ' . $subject . $b->code . '）', $out, $type);
            $this->assertStringContainsString('<html lang="ja">', $out, $type);
            $this->assertStringContainsString('<p>山田 花子 様</p>', $out, $type);
            $this->assertStringContainsString('<td>出発日</td><td><strong>2026年12月26日</strong>', $out, $type);
            foreach ($needles as $needle) {
                $this->assertStringContainsString($needle, $out, $type);
            }
            $this->assertNoPolicyText($out);
            $this->assertNoPlaceholderLeft($out);
        }
    }

    public function test_switch_on_staff_copies_and_admin_notification_are_unchanged(): void
    {
        $this->enableJa();
        $b = $this->jaBooking();
        $this->assertSame($this->legacySnapshot('staff.overdue_notice-en_deposit'), $this->asLegacyGuest($this->capture(new BookingPaymentMail($b, 'overdue_notice', self::LINK, [], true))));
        $this->assertSame($this->legacySnapshot('staff.cure_period_ended-en_deposit'), $this->asLegacyGuest($this->capture(new BookingPaymentMail($b, 'cure_period_ended', self::LINK, [], true))));
        $this->assertSame($this->legacySnapshot('admin-en_deposit'), $this->asLegacyGuest($this->capture(new BookingAdminNotificationMail($b))));
    }

    public function test_switch_on_en_and_vi_bookings_are_unchanged(): void
    {
        $this->enableJa();
        $this->assertSame($this->legacySnapshot('confirmation-en'), $this->capture(new BookingConfirmationMail($this->booking('en', ['locale' => 'en']))));
        $this->assertSame($this->legacySnapshot('confirmation-vi'), $this->capture(new BookingConfirmationMail($this->booking('vi', ['locale' => 'vi']))));
        $this->assertSame($this->legacySnapshot('payment.balance_request-vi'), $this->capture(new BookingPaymentMail(Booking::where('currency', 'vnd')->first()->load(['cabins', 'amenities', 'payment']), 'balance_request', self::LINK, ['expires' => 1798675200, 'amount' => 7500000])));
    }

    public function test_mail_fake_records_the_japanese_mail_only_when_enabled(): void
    {
        $b = $this->jaBooking();
        Mail::fake();
        Mail::send(new BookingConfirmationMail($b));
        Mail::assertSent(BookingConfirmationMail::class, function (BookingConfirmationMail $mail) use ($b) {
            $mail->build();

            return $mail->hasTo($b->email) && $mail->subject === 'Booking Confirmation - ' . $b->code;
        });

        $this->enableJa();
        Mail::fake();
        Mail::send(new BookingConfirmationMail($b));
        Mail::assertSent(BookingConfirmationMail::class, function (BookingConfirmationMail $mail) use ($b) {
            $mail->build();

            return $mail->hasTo($b->email) && $mail->subject === 'ご予約内容のご確認（予約番号：' . $b->code . '）';
        });
    }

    public function test_attributes_for_booking_creation_are_guarded_by_the_column(): void
    {
        BookingMailLocale::flush();
        $this->assertSame(['locale' => 'ja'], BookingMailLocale::attributes('ja-JP'));
        $this->assertSame(['locale' => 'en'], BookingMailLocale::attributes('en'));
        $this->assertSame([], BookingMailLocale::attributes(null));
        $this->assertSame([], BookingMailLocale::attributes('not a locale'));

        (require database_path('migrations/2026_11_06_000001_add_locale_to_bookings.php'))->down();
        BookingMailLocale::flush();
        $this->assertSame([], BookingMailLocale::attributes('ja'), 'before the migration nothing is written');
        BookingMailLocale::flush();
    }

    public function test_the_migration_is_reversible(): void
    {
        $migration = require database_path('migrations/2026_11_06_000001_add_locale_to_bookings.php');
        $this->assertTrue(\Schema::hasColumn('bookings', 'locale'));
        $migration->down();
        $this->assertFalse(\Schema::hasColumn('bookings', 'locale'));
        $migration->down();
        $migration->up();
        $migration->up();
        $this->assertTrue(\Schema::hasColumn('bookings', 'locale'));
        $this->assertNull(Booking::query()->value('locale'));
    }
}
