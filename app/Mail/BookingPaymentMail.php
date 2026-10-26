<?php

namespace App\Mail;

use App\Models\Booking;
use App\Payments\PaymentLink;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\BackEnd\Helpers\Utilities;

/**
 * Payment messages about an existing booking (TPX decisions 3, 5 and 6), in the guest's language:
 * payment link, balance request, two reminders, the formal overdue notice and refund confirmation.
 * With $staff = true it is the internal copy for Reservations (always Vietnamese).
 */
class BookingPaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    public const TYPES = ['payment_link', 'balance_request', 'balance_reminder_1', 'balance_reminder_2', 'overdue_notice', 'refund_processed', 'cure_period_ended'];

    public function __construct(
        public Booking $booking,
        public string $type,
        public ?string $link = null,
        public array $extra = [],
        public bool $staff = false,
    ) {
    }

    public function build()
    {
        $b = $this->booking;
        $locale = $this->staff ? 'vi' : PaymentLink::locale($b);
        $t = self::MESSAGES[$locale];
        $money = fn ($amount) => strtoupper((string) $b->currency) . ' ' . Utilities::formatStoredAmount((int) $amount, $b->currency, false);
        $date = fn (?CarbonInterface $d) => $d ? $d->copy()->setTimezone(config('booking.timezone'))->format($locale === 'vi' ? 'd/m/Y' : 'j M Y') : '';
        $dateTime = fn (?CarbonInterface $d) => $d ? $d->copy()->setTimezone(config('booking.timezone'))->format($locale === 'vi' ? 'H:i d/m/Y' : 'j M Y, H:i') . ' (GMT+7)' : '';

        $vars = [
            ':code' => $b->code,
            ':name' => $b->full_name,
            ':voyage' => trim(implode(' · ', array_filter([$b->itinerary_name, $b->cruise_name]))),
            ':departure' => $date($b->departure_date),
            ':total' => $money($b->total_amount),
            ':paid' => $money($b->amount_paid),
            ':balance' => $money($b->balanceAmount()),
            ':due' => $date($b->balance_due_date),
            ':deadline' => $dateTime($b->cure_deadline_at),
            ':amount' => $money($this->extra['amount'] ?? 0),
            ':expires' => $date(isset($this->extra['expires']) ? \Carbon\Carbon::createFromTimestamp($this->extra['expires']) : null),
        ];
        $fill = fn (string $text) => strtr($text, $vars);
        $copy = $t[$this->type];

        $config = Utilities::getAllConfig(\Modules\BackEnd\Services\AdLanguageService::getDefaultLanguage());
        $companyName = $config['website-name'] ?? 'Green Ruby Cruises';
        $supportEmail = $config['email'] ?? config('mail.from.address');
        $fromAddress = config('mail.from.address');

        $mail = $this->from($fromAddress, $companyName)
            ->replyTo($supportEmail ?: $fromAddress, $companyName)
            ->subject(($this->staff ? '[Reservations] ' : '') . $fill($copy['subject']));

        return $mail->view('emails.booking-payment')->with([
            'locale' => $locale,
            'heading' => $fill($copy['subject']),
            'greeting' => $this->staff ? null : $fill($t['greeting']),
            'paragraphs' => array_map($fill, $copy['body']),
            'facts' => array_map(fn ($row) => [$row[0], $fill($row[1])], $copy['facts'] ?? []),
            'button' => $this->link && !$this->staff ? $copy['button'] ?? null : null,
            'link' => $this->link,
            'signature' => $t['signature'],
            'companyName' => $companyName,
            'hotline' => $config['hotline'] ?? null,
            'supportEmail' => $supportEmail,
        ]);
    }

    private const FACTS_EN = [['Booking reference', ':code'], ['Voyage', ':voyage'], ['Departure', ':departure']];
    private const FACTS_VI = [['Mã đặt chỗ', ':code'], ['Hành trình', ':voyage'], ['Ngày khởi hành', ':departure']];

    public const MESSAGES = [
        'en' => [
            'greeting' => 'Dear :name,',
            'signature' => 'Reservations Team',
            'payment_link' => [
                'subject' => 'Complete your booking :code',
                'body' => [
                    'Thank you for choosing Green Ruby Cruises. Your reservation is ready for you to confirm.',
                    'Please open the secure link below to review the booking, accept our Booking Terms & Conditions and make your payment. The link is valid until :expires.',
                ],
                'facts' => [...self::FACTS_EN, ['Total', ':total']],
                'button' => 'Review and pay',
            ],
            'balance_request' => [
                'subject' => 'Balance payment for booking :code',
                'body' => [
                    'Thank you for your deposit. The remaining balance for your cruise is now payable.',
                    'Please pay the balance by :due (30 days before departure) using the secure link below.',
                ],
                'facts' => [...self::FACTS_EN, ['Total', ':total'], ['Received', ':paid'], ['Balance due', ':balance'], ['Due date', ':due']],
                'button' => 'Pay the balance',
            ],
            'balance_reminder_1' => [
                'subject' => 'Reminder: balance for booking :code due :due',
                'body' => ['This is a reminder that the balance of :balance for your cruise is due by :due.'],
                'facts' => [...self::FACTS_EN, ['Balance due', ':balance'], ['Due date', ':due']],
                'button' => 'Pay the balance',
            ],
            'balance_reminder_2' => [
                'subject' => 'Second reminder: balance for booking :code due :due',
                'body' => [
                    'We have not yet received the balance of :balance for your cruise. It is due by :due.',
                    'If you have already paid, please disregard this message; bank transfers can take a little time to reach us.',
                ],
                'facts' => [...self::FACTS_EN, ['Balance due', ':balance'], ['Due date', ':due']],
                'button' => 'Pay the balance',
            ],
            'overdue_notice' => [
                'subject' => 'Payment overdue: booking :code',
                'body' => [
                    'This is formal notice that the balance of :balance for your booking was due by :due and has not been received.',
                    'Please complete the payment by :deadline using the link below.',
                    'If the balance is not received by then, we may cancel the booking as set out in section 4.4 of our Booking Terms & Conditions. In that case we may retain up to the deposit already received, and no further cancellation charge will be claimed.',
                    'If you have already paid, or need help with the payment, please contact us before that time.',
                ],
                'facts' => [...self::FACTS_EN, ['Balance due', ':balance'], ['Pay by', ':deadline']],
                'button' => 'Pay the balance',
            ],
            'refund_processed' => [
                'subject' => 'Refund for booking :code',
                'body' => [
                    'We have issued a refund of :amount for your booking to the original payment method.',
                    'The time it takes to appear in your account depends on your bank or payment provider.',
                ],
                'facts' => [...self::FACTS_EN, ['Refund', ':amount']],
            ],
            'cure_period_ended' => ['subject' => 'Cure period ended: :code', 'body' => []],
        ],
        'vi' => [
            'greeting' => 'Kính gửi Quý khách :name,',
            'signature' => 'Bộ phận Đặt chỗ',
            'payment_link' => [
                'subject' => 'Hoàn tất đặt chỗ :code',
                'body' => [
                    'Cảm ơn Quý khách đã lựa chọn Green Ruby Cruises. Đặt chỗ của Quý khách đã sẵn sàng để xác nhận.',
                    'Vui lòng mở đường dẫn bảo mật dưới đây để xem lại đặt chỗ, đồng ý với Điều khoản đặt du thuyền và thanh toán. Đường dẫn có hiệu lực đến ngày :expires.',
                ],
                'facts' => [...self::FACTS_VI, ['Tổng giá trị', ':total']],
                'button' => 'Xem và thanh toán',
            ],
            'balance_request' => [
                'subject' => 'Thanh toán phần còn lại cho đặt chỗ :code',
                'body' => [
                    'Cảm ơn Quý khách đã đặt cọc. Phần còn lại của chuyến đi hiện đã đến kỳ thanh toán.',
                    'Vui lòng thanh toán trước ngày :due (30 ngày trước ngày khởi hành) qua đường dẫn bảo mật dưới đây.',
                ],
                'facts' => [...self::FACTS_VI, ['Tổng giá trị', ':total'], ['Đã nhận', ':paid'], ['Còn lại', ':balance'], ['Hạn thanh toán', ':due']],
                'button' => 'Thanh toán phần còn lại',
            ],
            'balance_reminder_1' => [
                'subject' => 'Nhắc thanh toán: đặt chỗ :code, hạn :due',
                'body' => ['Green Ruby Cruises xin nhắc Quý khách: phần còn lại :balance cần được thanh toán trước ngày :due.'],
                'facts' => [...self::FACTS_VI, ['Còn lại', ':balance'], ['Hạn thanh toán', ':due']],
                'button' => 'Thanh toán phần còn lại',
            ],
            'balance_reminder_2' => [
                'subject' => 'Nhắc lần 2: đặt chỗ :code, hạn :due',
                'body' => [
                    'Chúng tôi chưa nhận được phần còn lại :balance cho chuyến đi của Quý khách. Hạn thanh toán là ngày :due.',
                    'Nếu Quý khách đã thanh toán, vui lòng bỏ qua thư này; chuyển khoản ngân hàng có thể cần thêm thời gian để được ghi nhận.',
                ],
                'facts' => [...self::FACTS_VI, ['Còn lại', ':balance'], ['Hạn thanh toán', ':due']],
                'button' => 'Thanh toán phần còn lại',
            ],
            'overdue_notice' => [
                'subject' => 'Thông báo quá hạn thanh toán: đặt chỗ :code',
                'body' => [
                    'Green Ruby Cruises trân trọng thông báo: phần còn lại :balance của đặt chỗ đã đến hạn ngày :due nhưng chúng tôi chưa nhận được.',
                    'Quý khách vui lòng hoàn tất thanh toán trước :deadline qua đường dẫn dưới đây.',
                    'Nếu đến thời điểm đó chưa nhận được thanh toán, chúng tôi có thể hủy đặt chỗ theo mục 4.4 của Điều khoản đặt du thuyền. Khi đó chúng tôi có thể giữ lại tối đa khoản đặt cọc đã nhận và không thu thêm phí hủy nào khác.',
                    'Nếu Quý khách đã thanh toán hoặc cần hỗ trợ, vui lòng liên hệ chúng tôi trước thời điểm trên.',
                ],
                'facts' => [...self::FACTS_VI, ['Còn lại', ':balance'], ['Thanh toán trước', ':deadline']],
                'button' => 'Thanh toán phần còn lại',
            ],
            'refund_processed' => [
                'subject' => 'Hoàn tiền cho đặt chỗ :code',
                'body' => [
                    'Chúng tôi đã hoàn :amount cho đặt chỗ của Quý khách về phương thức thanh toán ban đầu.',
                    'Thời gian tiền về tài khoản phụ thuộc vào ngân hàng hoặc đơn vị thanh toán của Quý khách.',
                ],
                'facts' => [...self::FACTS_VI, ['Số tiền hoàn', ':amount']],
            ],
            'cure_period_ended' => [
                'subject' => 'Hết hạn bổ sung thanh toán: :code',
                'body' => [
                    'Đặt chỗ :code (khởi hành :departure) vẫn chưa thanh toán phần còn lại :balance sau thời hạn bổ sung (:deadline).',
                    'Hệ thống KHÔNG tự hủy. Vui lòng kiểm tra phản hồi của khách và đối soát giao dịch với Finance trước khi người có thẩm quyền quyết định.',
                ],
                'facts' => [...self::FACTS_VI, ['Còn lại', ':balance']],
            ],
        ],
    ];
}
