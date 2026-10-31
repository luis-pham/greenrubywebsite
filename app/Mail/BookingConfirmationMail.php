<?php

namespace App\Mail;

use App\Models\Booking;
use App\Services\Mail\BookingMailLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AdLanguageService;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $config;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        
        $language = AdLanguageService::getDefaultLanguage();
        $this->config = Utilities::getAllConfig($language);
    }

    public function build()
    {
        $companyName = $this->config['website-name'] ?? 'Green Ruby Cruises';
        $hotline = $this->config['hotline'] ?? '1900 8888';
        $email = $this->config['email'] ?? config('mail.from.address');
        $website = config('app.website_link');

        $fromAddress = config('mail.from.address');
        $replyToAddress = $email && $email !== $fromAddress ? $email : $fromAddress;
        $customerEmail = $this->booking->email ? trim((string) $this->booking->email) : '';
        // Phase 2B: null (en, vi, no locale, or a locale not enabled for mail) = the pre-2B e-mail, unchanged.
        $locale = BookingMailLocale::for($this->booking);
        if ($locale !== null) {
            return $this->buildLocalized($locale, $companyName, $hotline, $email, $website, $fromAddress, $replyToAddress, $customerEmail);
        }
        $mailable = $this->from($fromAddress, $companyName)
            ->replyTo($replyToAddress, $companyName)
            ->subject('Booking Confirmation - ' . $this->booking->code);
        if ($customerEmail !== '') {
            $mailable->to($customerEmail);
        }
        return $mailable
            ->view('emails.booking-confirmation')
            ->with([
                'booking' => $this->booking,
                'companyName' => $companyName,
                'hotline' => $hotline,
                'supportEmail' => $email,
                'website' => $website,
                'paymentMethodLabel' => $this->getPaymentMethodLabel($this->booking->payment->gateway ?? null),
            ]);
    }

    /** Guest e-mail in a phase-2B locale (resources/views/emails/{locale}/booking-confirmation.blade.php). */
    protected function buildLocalized(string $locale, $companyName, $hotline, $email, $website, $fromAddress, $replyToAddress, string $customerEmail)
    {
        $copy = BookingMailLocale::copy($locale);
        $mailable = $this->from($fromAddress, $companyName)
            ->replyTo($replyToAddress, $companyName)
            ->subject(strtr($copy['subjects']['confirmation'], [':code' => $this->booking->code]));
        if ($customerEmail !== '') {
            $mailable->to($customerEmail);
        }
        $gateway = $this->booking->payment->gateway ?? null;

        return $mailable
            ->view('emails.' . $locale . '.booking-confirmation')
            ->with([
                'booking' => $this->booking,
                'companyName' => $companyName,
                'hotline' => $hotline,
                'supportEmail' => $email,
                'website' => $website,
                'paymentMethodLabel' => $copy['payment_methods'][$gateway] ?? $this->getPaymentMethodLabel($gateway),
                'balanceRequestDays' => (int) config('booking.balance_request_days', 45),
                'termsUrl' => BookingMailLocale::termsUrl('cancellation'),
                'refundTermsUrl' => BookingMailLocale::termsUrl('refunds'),
            ]);
    }

    protected function getPaymentMethodLabel($gateway)
    {
        $labels = [
            'stripe' => 'Stripe (Credit Card)',
            'paypal' => 'PayPal',
            'sepay' => 'Bank Transfer',
            'cash' => 'Reservation Only',
        ];
        
        return $labels[$gateway] ?? ucfirst($gateway ?? 'N/A');
    }
}
