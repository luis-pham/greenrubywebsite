# Email System Safety Documentation

## ✅ Đảm Bảo Email KHÔNG Ảnh Hưởng Thanh Toán

Hệ thống email được thiết kế để **KHÔNG BAO GIỜ** làm ảnh hưởng đến kết quả thanh toán hoặc tạo booking.

---

## 🔒 Cơ Chế Bảo Vệ

### 1. Reservation Only (Inquiry) - `PaymentController::storeInquiry()`

**Thứ tự thực hiện:**

```php
try {
    DB::beginTransaction();
    
    // 1. Tạo Payment record
    $payment = Payment::create([...]);
    
    // 2. Tạo Booking record
    $booking = Booking::create([...]);
    
    // 3. Tạo Cabin records
    BookingCabin::create([...]);
    
    // 4. Tạo Amenity records
    BookingAmenity::create([...]);
    
    // ✅ COMMIT TRANSACTION - Dữ liệu đã được lưu vào database
    DB::commit();
    
} catch (\Throwable $e) {
    DB::rollBack();
    return response()->json(['success' => false, ...], 500);
}

// ✅ SAU KHI COMMIT - Booking đã an toàn trong database
$booking->refresh();
$booking->load(['cabins', 'amenities', 'payment']);

// Gửi email (NGOÀI transaction)
try {
    if ($booking->email) {
        Mail::to($booking->email)->send(new BookingConfirmationMail($booking));
    }
    
    if ($adminEmail) {
        Mail::to($adminEmail)->send(new BookingAdminNotificationMail($booking));
    }
} catch (\Throwable $emailException) {
    // ❌ Email lỗi? Không sao!
    Log::error('Failed to send inquiry emails', [...]);
    // KHÔNG throw exception, KHÔNG rollback
}

// ✅ Response trả về success BẤT KỂ email có lỗi hay không
return response()->json([
    'success' => true,
    'code' => $booking->code,
    'message' => 'Yêu cầu đặt chỗ đã được gửi.',
], 201);
```

**Kết quả:**
- ✅ Booking đã được tạo và lưu vào database
- ✅ Response trả về success
- ❌ Email lỗi → Chỉ ghi log, KHÔNG ảnh hưởng kết quả
- ✅ User vẫn thấy "Đặt chỗ thành công"

---

### 2. Payment Success - `Payment::booted()` hook

**Khi nào trigger:**
- Stripe/PayPal/SePay webhook callback
- Payment status thay đổi từ "pending" → "succeeded"

**Thứ tự thực hiện:**

```php
// Laravel gọi Payment::update(['status' => 'succeeded'])
// ↓
// Transaction tự động của Laravel save payment status
// ↓
// ✅ Payment status đã saved vào database
// ↓
// Laravel trigger hook `updated()` SAU KHI transaction committed
// ↓

static::updated(function (Payment $payment) {
    try {
        // 1. Kiểm tra nếu status = succeeded
        if ($payment->status === 'succeeded') {
            
            // 2. Update booking status
            if ($payment->booking) {
                $payment->booking->update(['status' => 'paid']);
            }
            
            // 3. Load booking data
            $booking = $payment->booking()->with(['cabins', 'amenities'])->first();
            
            // 4. Gửi email (có riêng try-catch)
            try {
                if ($booking->email) {
                    Mail::to($booking->email)->send(...);
                }
                
                if ($adminEmail) {
                    Mail::to($adminEmail)->send(...);
                }
            } catch (\Throwable $e) {
                // ❌ Email lỗi? Không sao!
                Log::error('Failed to send booking success emails', [...]);
                // KHÔNG throw exception
            }
        }
        
    } catch (\Throwable $e) {
        // ❌ BẤT KỲ lỗi nào trong hook? Không sao!
        Log::error('Payment booted hook error - will not affect payment status', [...]);
        // KHÔNG throw exception
        // Payment status VẪN là "succeeded" trong database
    }
});
```

**Kết quả:**
- ✅ Payment status = "succeeded" đã saved
- ✅ Booking status = "paid" đã saved
- ❌ Email lỗi → Chỉ ghi log, KHÔNG ảnh hưởng payment status
- ✅ Webhook callback vẫn nhận response success từ payment gateway

---

### 3. Payment Failed - `Payment::booted()` hook

**Tương tự như Payment Success:**
- Payment status = "failed" đã saved
- Booking status = "failed" đã saved
- Email lỗi → Không ảnh hưởng

---

## 🧪 Test Cases

### Test 1: Email SMTP cấu hình sai
**Kịch bản:**
1. Cấu hình sai MAIL_PASSWORD trong `.env`
2. User tạo booking "Reservation Only"
3. Hệ thống cố gắng gửi email → Lỗi SMTP

**Kết quả mong đợi:**
- ✅ Booking được tạo thành công trong database
- ✅ Frontend nhận response success
- ❌ Email không gửi được
- ✅ Log ghi: "Failed to send inquiry emails: Authentication failed"

**Kiểm tra log:**
```bash
tail -f storage/logs/laravel.log | grep "Failed to send"
```

---

### Test 2: Gmail rate limit
**Kịch bản:**
1. Gmail đã gửi quá 100 email/ngày
2. User thanh toán thành công qua Stripe
3. Hệ thống cố gửng gửi email → Gmail từ chối (rate limit)

**Kết quả mong đợi:**
- ✅ Payment status = "succeeded" trong database
- ✅ Booking status = "paid" trong database
- ✅ Stripe webhook nhận response 200 OK
- ❌ Email không gửi được
- ✅ Log ghi: "Failed to send booking success emails: Rate limit exceeded"

---

### Test 3: Booking không có email
**Kịch bản:**
1. User không nhập email khi booking
2. User thanh toán thành công

**Kết quả mong đợi:**
- ✅ Payment status = "succeeded"
- ✅ Booking status = "paid"
- ⏭️ Không gửi email cho customer (vì không có email)
- ✅ Vẫn gửi email cho admin
- ✅ Không có lỗi trong log

---

### Test 4: Mail server down
**Kịch bản:**
1. MAIL_HOST không kết nối được (server down, firewall block)
2. User tạo booking

**Kết quả mong đợi:**
- ✅ Booking được tạo thành công
- ✅ Frontend nhận response success
- ❌ Email timeout sau vài giây
- ✅ Log ghi: "Failed to send inquiry emails: Connection timeout"

---

## 📝 Log Examples

### Email gửi thành công:
```
[2026-03-11 10:30:45] local.INFO: Booking confirmation email sent {"booking_code":"BK20260311ABC123","email":"customer@example.com"}
[2026-03-11 10:30:46] local.INFO: Booking admin notification sent {"booking_code":"BK20260311ABC123","admin_email":"admin@example.com"}
```

### Email gửi thất bại:
```
[2026-03-11 10:30:45] local.ERROR: Failed to send booking success emails {"booking_code":"BK20260311ABC123","error":"Failed to authenticate on SMTP server","trace":"..."}
```

### Hook error (rất hiếm xảy ra):
```
[2026-03-11 10:30:45] local.ERROR: Payment booted hook error - will not affect payment status {"payment_id":123,"status":"succeeded","error":"...","trace":"..."}
```

---

## 🔧 Troubleshooting

### Nếu email không gửi được:

**1. Kiểm tra log:**
```bash
tail -100 storage/logs/laravel.log | grep -i "email\|mail"
```

**2. Kiểm tra cấu hình SMTP:**
```bash
# Trong file .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password  # ← Phải dùng App Password, không phải password thường
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_TO_ADDRESS=admin@example.com
```

**3. Clear config cache:**
```bash
php artisan config:cache
```

**4. Test gửi email thủ công:**
```bash
php artisan tinker
>>> $booking = App\Models\Booking::with(['cabins', 'amenities', 'payment'])->first();
>>> Mail::to('test@example.com')->send(new App\Mail\BookingConfirmationMail($booking));
```

---

## ✅ Kết Luận

**Email system được thiết kế với 3 lớp bảo vệ:**

1. **Transaction Isolation:**
   - Email LUÔN gửi SAU KHI `DB::commit()`
   - Email KHÔNG nằm trong database transaction

2. **Exception Handling:**
   - MỌI lỗi email đều được catch
   - KHÔNG throw exception ra ngoài
   - Chỉ ghi log để debug

3. **Silent Failure:**
   - Email lỗi → Log error
   - Booking/Payment vẫn thành công
   - User không biết email lỗi (vì không ảnh hưởng đến nghiệp vụ)

**→ Thanh toán và tạo booking LUÔN LUÔN an toàn, bất kể email có lỗi hay không.**

---

**Last Updated:** 2026-03-11  
**Version:** 1.0
