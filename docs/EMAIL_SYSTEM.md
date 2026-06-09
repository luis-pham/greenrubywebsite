# Booking Email System Documentation

## Overview
The booking email system automatically sends confirmation emails to customers and notification emails to administrators when a booking is created or payment status changes.

## Email Types

### EM01 - Customer Booking Confirmation Email
**Template:** `resources/views/emails/booking-confirmation.blade.php`  
**Mailable Class:** `App\Mail\BookingConfirmationMail`

**Sent to:** Customer's email address  
**Triggered when:**
- Payment status changes to "succeeded" (paid successfully)
- Customer creates a "Reservation Only" booking (inquiry)

**Content includes:**
- Booking Information (Code, Tour, Cruise, Departure Date, Number of Guests)
- Cabin Details (Type, Guests breakdown, Prices)
- Additional Services (Amenities with quantities and prices)
- Estimated Total
- Payment Information (Method, Status)
- Cancellation Policy
- Contact Information

---

### EM02 - Admin Booking Notification Email
**Template:** `resources/views/emails/booking-admin-notification.blade.php`  
**Mailable Class:** `App\Mail\BookingAdminNotificationMail`

**Sent to:** System administrator (configured in `.env` as `MAIL_TO_ADDRESS`)  
**Triggered when:**
- Payment status changes to "succeeded" (paid successfully)
- Payment status changes to "failed"
- Customer creates a "Reservation Only" booking (inquiry)

**Content includes:**
- Booking Summary (ID, Date, Status)
- Customer Information (Name, Email, Phone)
- Cruise Information
- Cabin Details
- Additional Services
- Payment Information (including Transaction ID if available)
- Admin Action Required Checklist
- Link to Admin Portal

---

## Technical Implementation

### 1. Email Triggers

#### Payment Model Observer
Located in: `app/Models/Payment.php`

```php
protected static function booted()
{
    static::updated(function (Payment $payment) {
        // When payment succeeded
        if ($payment->status === 'succeeded') {
            // Update booking status to 'paid'
            // Send confirmation email to customer
            // Send notification email to admin
        }

        // When payment failed
        if ($payment->status === 'failed') {
            // Update booking status to 'failed'
            // Send notification email to admin only
        }
    });
}
```

#### Reservation Only (Inquiry)
Located in: `app/Http/Controllers/Api/PaymentController.php` → `storeInquiry()`

After creating booking with `gateway = 'cash'`:
- Send confirmation email to customer
- Send notification email to admin

### 2. Email Configuration

**.env settings:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
MAIL_TO_ADDRESS=admin@gmail.com  # Admin email for notifications
```

**Config file:** `config/mail.php`

### 3. Payment Method Labels

Payment methods are displayed with user-friendly labels:

| Gateway Code | Display Label |
|-------------|---------------|
| `stripe` | Stripe (Credit Card) |
| `paypal` | PayPal |
| `sepay` | Bank Transfer |
| `cash` | Reservation Only |

### 4. Email Styling

Both email templates use inline CSS for maximum email client compatibility:
- Responsive design (mobile-friendly)
- Professional color scheme (Blue/Red gradient headers)
- Clear sections with borders and background colors
- Clean typography with proper spacing

---

## Testing

### Manual Testing
1. Create a test booking via the frontend
2. Complete payment or submit as "Reservation Only"
3. Check customer email inbox
4. Check admin email inbox (`MAIL_TO_ADDRESS`)

### Log Monitoring
All email sending activities are logged:
```bash
# Success logs
[timestamp] local.INFO: Booking confirmation email sent {"booking_code":"BK20260311ABC123","email":"customer@example.com"}
[timestamp] local.INFO: Booking admin notification sent {"booking_code":"BK20260311ABC123","admin_email":"admin@example.com"}

# Error logs
[timestamp] local.ERROR: Failed to send booking emails {"booking_code":"BK20260311ABC123","error":"Connection refused"}
```

**Log file location:** `storage/logs/laravel.log`

---

## Troubleshooting

### Emails not being sent

**1. Check mail configuration:**
```bash
php artisan config:cache
php artisan cache:clear
```

**2. Verify SMTP credentials:**
- For Gmail, ensure "App Passwords" are used (not regular password)
- Check firewall settings for port 587

**3. Check logs:**
```bash
tail -f storage/logs/laravel.log
```

**4. Test mail configuration:**
```bash
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```

### Emails going to spam

**Solutions:**
- Set up SPF, DKIM, and DMARC records for your domain
- Use a verified email service provider (SendGrid, AWS SES, Mailgun)
- Ensure `MAIL_FROM_ADDRESS` matches your domain

### Missing booking data in emails

**Check:**
- Booking was loaded with relationships: `$booking->load(['cabins', 'amenities', 'payment'])`
- All required fields are present in the booking record

---

## Customization

### Changing Email Content

**Customer Email:** Edit `resources/views/emails/booking-confirmation.blade.php`  
**Admin Email:** Edit `resources/views/emails/booking-admin-notification.blade.php`

### Adding New Email Variables

1. Add to Mailable class `build()` method:
```php
->with([
    'customVariable' => $value,
])
```

2. Use in Blade template:
```blade
{{ $customVariable }}
```

### Changing Email Subject

Edit the Mailable class:
```php
public function build()
{
    return $this->subject('Your Custom Subject - ' . $this->booking->code)
        // ...
}
```

---

## Production Recommendations

1. **Use Queue for Email Sending:**
   ```php
   Mail::to($email)->queue(new BookingConfirmationMail($booking));
   ```

2. **Set up proper email service:**
   - Use SendGrid, AWS SES, or Mailgun for better deliverability
   - Configure rate limits and retries

3. **Monitor email logs:**
   - Set up alerts for failed emails
   - Track delivery rates

4. **Backup email addresses:**
   - Add BCC to critical emails
   - Store email history in database

---

## Related Files

- `app/Mail/BookingConfirmationMail.php`
- `app/Mail/BookingAdminNotificationMail.php`
- `resources/views/emails/booking-confirmation.blade.php`
- `resources/views/emails/booking-admin-notification.blade.php`
- `app/Models/Payment.php`
- `app/Http/Controllers/Api/PaymentController.php`
- `config/mail.php`
- `.env`

---

**Last Updated:** 2026-03-11  
**Version:** 1.0
