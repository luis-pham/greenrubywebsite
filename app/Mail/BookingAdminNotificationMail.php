<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AdLanguageService;

class BookingAdminNotificationMail extends Mailable
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
        $adminPortalUrl = env('ADMIN_PORTAL_URL', config('app.url') . '/backend');

        return $this->from(config('mail.from.address'), 'Booking System')
            ->subject('[New Booking] ' . $this->booking->code . ' - ' . $this->booking->full_name)
            ->view('emails.booking-admin-notification')
            ->with([
                'booking' => $this->booking,
                'companyName' => $companyName,
                'adminPortalUrl' => $adminPortalUrl,
                'paymentMethodLabel' => $this->getPaymentMethodLabel($this->booking->payment->gateway ?? null),
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
