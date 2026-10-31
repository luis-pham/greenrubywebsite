<?php

namespace App\Mail;

use App\Models\Booking;
use App\Services\Mail\BookingMailLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AdLanguageService;

class BookingCancellationMail extends Mailable
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
            ->subject('Booking Cancellation Confirmation - ' . $this->booking->code);

        if ($customerEmail !== '') {
            $mailable->to($customerEmail);
        }

        return $mailable
            ->view('emails.booking-cancellation')
            ->with([
                'booking' => $this->booking,
                'companyName' => $companyName,
                'hotline' => $hotline,
                'supportEmail' => $email,
                'website' => $website,
            ]);
    }

    /** Guest e-mail in a phase-2B locale (resources/views/emails/{locale}/booking-cancellation.blade.php). */
    protected function buildLocalized(string $locale, $companyName, $hotline, $email, $website, $fromAddress, $replyToAddress, string $customerEmail)
    {
        $copy = BookingMailLocale::copy($locale);
        $mailable = $this->from($fromAddress, $companyName)
            ->replyTo($replyToAddress, $companyName)
            ->subject(strtr($copy['subjects']['cancellation'], [':code' => $this->booking->code]));
        if ($customerEmail !== '') {
            $mailable->to($customerEmail);
        }

        return $mailable
            ->view('emails.' . $locale . '.booking-cancellation')
            ->with([
                'booking' => $this->booking,
                'companyName' => $companyName,
                'hotline' => $hotline,
                'supportEmail' => $email,
                'website' => $website,
                'termsUrl' => BookingMailLocale::termsUrl('cancellation'),
                'refundTermsUrl' => BookingMailLocale::termsUrl('refunds'),
            ]);
    }
}
