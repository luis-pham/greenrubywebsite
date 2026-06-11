<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Models\BookingAmenity;
use App\Models\BookingCabin;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Payments\PaymentManager;
use App\Support\SafeRedirect;
use Modules\BackEnd\Entities\AdLanguage;

class PaymentController extends Controller
{
    protected $payments;

    public function __construct(PaymentManager $payments)
    {
        $this->payments = $payments;
    }

    public function storeInquiry(Request $request)
    {
        $data = $request->validate([
            'booking' => 'required|array',
            'booking.full_name' => 'required|string|max:255',
            'booking.email' => 'sometimes|nullable|email',
            'booking.phone' => 'sometimes|nullable|string|max:50',
            'booking.departure_date' => 'sometimes|nullable|date',
            'booking.itinerary_id' => 'sometimes|nullable',
            'booking.itinerary_name' => 'sometimes|nullable|string',
            'booking.cruise_name' => 'sometimes|nullable|string',
            'booking.itinerary_duration_label' => 'sometimes|nullable|string',
            'booking.destination' => 'sometimes|nullable|string',
            'booking.guests_total' => 'sometimes|integer|min:0',
            'booking.subtotal_cabins' => 'sometimes|numeric|min:0',
            'booking.subtotal_amenities' => 'sometimes|numeric|min:0',
            'booking.total_amount' => 'sometimes|numeric|min:0',
            'booking.cabins' => 'sometimes|array',
            'booking.cabins.*.cabin_name' => 'sometimes|string',
            'booking.cabins.*.cabin_description' => 'sometimes|nullable|string',
            'booking.cabins.*.unit_price' => 'sometimes|numeric|min:0',
            'booking.cabins.*.quantity' => 'sometimes|integer|min:0',
            'booking.cabins.*.adults' => 'sometimes|integer|min:0',
            'booking.cabins.*.children_6_12' => 'sometimes|integer|min:0',
            'booking.cabins.*.children_2_5' => 'sometimes|integer|min:0',
            'booking.cabins.*.infants' => 'sometimes|integer|min:0',
            'booking.cabins.*.total_price' => 'sometimes|numeric|min:0',
            'booking.cabins.*.cabin_id' => 'sometimes|nullable|integer',
            'booking.amenities' => 'sometimes|array',
            'booking.amenities.*.amenity_name' => 'sometimes|string',
            'booking.amenities.*.amenity_id' => 'sometimes|nullable|integer',
            'booking.amenities.*.unit_price' => 'sometimes|numeric|min:0',
            'booking.amenities.*.quantity' => 'sometimes|integer|min:0',
            'booking.amenities.*.total_price' => 'sometimes|numeric|min:0',
        ]);

        $bookingData = $data['booking'];
        $currency = $this->resolveCurrencyFromBooking($bookingData);
        if ($currency === null) {
            $currency = $this->resolveCurrencyFromDefaultLanguage();
        }
        $currency = strtolower($currency);

        try {
            DB::beginTransaction();
            $internalTxId = 'inquiry_' . uniqid('', true);

            [$subtotalCabinsRaw, $subtotalAmenitiesRaw, $totalRaw] = $this->calculateBookingTotals($bookingData);

            $subtotalCabins = $this->toStorageAmount($subtotalCabinsRaw, $currency);
            $subtotalAmenities = $this->toStorageAmount($subtotalAmenitiesRaw, $currency);
            $totalAmount = $this->toStorageAmount($totalRaw, $currency);

            $payment = Payment::create([
                'internal_tx_id' => $internalTxId,
                'gateway' => 'cash',
                'amount' => $totalAmount,
                'currency' => $currency,
                'status' => Payment::defaultStatus(),
            ]);
            $bookingCode = 'BK' . date('Ymd') . strtoupper(substr(uniqid(), -6));
            $rawItineraryId = $bookingData['itinerary_id'] ?? null;
            $itineraryId = null;
            if ($rawItineraryId !== null && $rawItineraryId !== '') {
                $itineraryId = is_numeric($rawItineraryId) ? (int) $rawItineraryId : null;
            }
            $rawEmail = isset($bookingData['email']) ? trim((string) $bookingData['email']) : '';
            $booking = Booking::create([
                'payment_id' => $payment->id,
                'code' => $bookingCode,
                'full_name' => $bookingData['full_name'] ?? '',
                'email' => $rawEmail !== '' ? $rawEmail : null,
                'phone' => $bookingData['phone'] ?? null,
                'departure_date' => !empty($bookingData['departure_date']) ? $bookingData['departure_date'] : null,
                'itinerary_id' => $itineraryId,
                'itinerary_name' => $bookingData['itinerary_name'] ?? null,
                'cruise_name' => $bookingData['cruise_name'] ?? null,
                'itinerary_duration_label' => $bookingData['itinerary_duration_label'] ?? null,
                'destination' => $bookingData['destination'] ?? null,
                'guests_total' => (int) ($bookingData['guests_total'] ?? 0),
                'currency' => $currency,
                'subtotal_cabins' => $subtotalCabins,
                'subtotal_amenities' => $subtotalAmenities,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $totalAmount,
                'status' => config('statuses.booking.pending', 'pending'),
            ]);
            foreach ($bookingData['cabins'] ?? [] as $row) {
                $cabinPayload = [
                    'booking_id' => $booking->id,
                    'cabin_name' => $row['cabin_name'] ?? '',
                    'cabin_description' => $row['cabin_description'] ?? null,
                    'unit_price' => $this->toStorageAmount((float) ($row['unit_price'] ?? 0), $currency),
                    'quantity' => (int) ($row['quantity'] ?? 1),
                    'adults' => (int) ($row['adults'] ?? 0),
                    'children_6_12' => (int) ($row['children_6_12'] ?? 0),
                    'children_2_5' => (int) ($row['children_2_5'] ?? 0),
                    'infants' => (int) ($row['infants'] ?? 0),
                    'total_price' => $this->toStorageAmount((float) ($row['total_price'] ?? 0), $currency),
                ];
                if (isset($row['cabin_id']) && is_numeric($row['cabin_id'])) {
                    $cabinPayload['cabin_id'] = (int) $row['cabin_id'];
                }
                BookingCabin::create($cabinPayload);
            }
            foreach ($bookingData['amenities'] ?? [] as $row) {
                BookingAmenity::create([
                    'booking_id' => $booking->id,
                    'amenity_name' => $row['amenity_name'] ?? '',
                    'unit_price' => $this->toStorageAmount((float) ($row['unit_price'] ?? 0), $currency),
                    'quantity' => (int) ($row['quantity'] ?? 0),
                    'total_price' => $this->toStorageAmount((float) ($row['total_price'] ?? 0), $currency),
                ]);
            }
            DB::commit();

            $booking->refresh();
            $booking->load(['cabins', 'amenities', 'payment']);

            try {
                $customerEmail = $booking->email ? trim((string) $booking->email) : '';
                $adminEmail = config('mail.admin.address');
                $adminEmailNormalized = $adminEmail ? trim(strtolower((string) $adminEmail)) : '';
                $customerEmailNormalized = $customerEmail !== '' ? trim(strtolower($customerEmail)) : '';
                if ($customerEmail !== '' && $customerEmailNormalized !== $adminEmailNormalized) {
                    \Mail::send(new \App\Mail\BookingConfirmationMail($booking));
                    Log::info('Inquiry confirmation email sent to customer', ['booking_code' => $booking->code, 'email' => $customerEmail]);
                } elseif ($customerEmail !== '' && $customerEmailNormalized === $adminEmailNormalized) {
                    Log::warning('Inquiry: customer email is admin, skipping confirmation', ['booking_code' => $booking->code]);
                } else {
                    Log::warning('Inquiry: customer email empty, skipping confirmation email', ['booking_code' => $booking->code]);
                }
                if ($adminEmail) {
                    \Mail::to($adminEmail)->send(new \App\Mail\BookingAdminNotificationMail($booking));
                    Log::info('Inquiry admin notification sent', ['booking_code' => $booking->code, 'admin_email' => $adminEmail]);
                }
            } catch (\Throwable $emailException) {
                Log::error('Failed to send inquiry emails', [
                    'booking_code' => $booking->code,
                    'error' => $emailException->getMessage()
                ]);
            }

            return response()->json([
                'success' => true,
                'code' => $booking->code,
                'message' => 'Yêu cầu đặt chỗ đã được gửi. Tư vấn viên sẽ liên hệ bạn sớm.',
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Booking inquiry store error', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Không thể lưu yêu cầu. Vui lòng thử lại.',
            ], 500);
        }
    }

    public function init(Request $request)
    {
        $methods = implode(',', Payment::validMethods());
        $data = $request->validate([
            'method' => 'required|string|in:' . $methods,
            'amount' => 'required|numeric|min:1',
            'currency' => 'sometimes|string',
            'description' => 'sometimes|string',
            'return_base' => 'sometimes|string',
            'booking' => 'sometimes|array',
            'booking.departure_date' => 'sometimes|nullable|date',
            'booking.itinerary_id' => 'sometimes|nullable',
            'booking.itinerary_name' => 'sometimes|nullable|string',
            'booking.cruise_name' => 'sometimes|nullable|string',
            'booking.itinerary_duration_label' => 'sometimes|nullable|string',
            'booking.destination' => 'sometimes|nullable|string',
            'booking.guests_total' => 'sometimes|integer|min:0',
            'booking.full_name' => 'sometimes|nullable|string',
            'booking.email' => 'sometimes|nullable|email',
            'booking.phone' => 'sometimes|nullable|string',
            'booking.subtotal_cabins' => 'sometimes|numeric|min:0',
            'booking.subtotal_amenities' => 'sometimes|numeric|min:0',
            'booking.total_amount' => 'sometimes|numeric|min:0',
            'booking.cabins' => 'sometimes|array',
            'booking.cabins.*.cabin_name' => 'sometimes|string',
            'booking.cabins.*.cabin_description' => 'sometimes|nullable|string',
            'booking.cabins.*.unit_price' => 'sometimes|numeric|min:0',
            'booking.cabins.*.quantity' => 'sometimes|integer|min:0',
            'booking.cabins.*.adults' => 'sometimes|integer|min:0',
            'booking.cabins.*.children_6_12' => 'sometimes|integer|min:0',
            'booking.cabins.*.children_2_5' => 'sometimes|integer|min:0',
            'booking.cabins.*.infants' => 'sometimes|integer|min:0',
            'booking.cabins.*.total_price' => 'sometimes|numeric|min:0',
            'booking.cabins.*.cabin_id' => 'sometimes|nullable|integer',
            'booking.amenities' => 'sometimes|array',
            'booking.amenities.*.amenity_name' => 'sometimes|string',
            'booking.amenities.*.amenity_id' => 'sometimes|nullable|integer',
            'booking.amenities.*.unit_price' => 'sometimes|numeric|min:0',
            'booking.amenities.*.quantity' => 'sometimes|integer|min:0',
            'booking.amenities.*.total_price' => 'sometimes|numeric|min:0',
        ]);

        try {
            $method = $data['method'];
            $bookingData = $data['booking'] ?? [];
            $currency = $this->resolveCurrencyFromBooking($bookingData);
            if ($currency === null) {
                return response()->json([
                    'success' => false,
                    'code' => 'currency_unknown',
                    'message' => 'Không xác định được đơn vị tiền tệ. Đơn hàng phải có ít nhất một cabin (cabin_id) để xác định từ ngôn ngữ trên máy chủ.',
                ], 400);
            }
            $currency = strtolower($currency);
            
            $amountForGateway = $this->calculateAmountFromBooking($bookingData, $currency);
            if ($amountForGateway <= 0) {
                return response()->json([
                    'success' => false,
                    'code' => 'amount_calculation_failed',
                    'message' => 'Không tính được số tiền thanh toán từ dữ liệu trên máy chủ. Vui lòng kiểm tra lại cấu hình cabin/giá.',
                ], 400);
            }
            
            $amountForDb = $this->toStorageAmount($amountForGateway, $currency);

            $usdOnlyMethods = array('stripe', 'paypal');
            $vndOnlyMethods = array('sepay');
            if (in_array($method, $usdOnlyMethods, true) && $currency !== 'usd') {
                return response()->json([
                    'success' => false,
                    'code' => 'currency_method_mismatch',
                    'reason' => 'usd_only',
                    'message' => 'Stripe và PayPal chỉ hỗ trợ USD. Đơn của bạn đang dùng VND (theo cabin đã chọn). Vui lòng chọn Chuyển khoản ngân hàng (Bank Transfer).',
                ], 400);
            }
            if (in_array($method, $vndOnlyMethods, true) && $currency !== 'vnd') {
                return response()->json([
                    'success' => false,
                    'code' => 'currency_method_mismatch',
                    'reason' => 'vnd_only',
                    'message' => 'Chuyển khoản ngân hàng chỉ hỗ trợ VND. Đơn của bạn đang dùng USD (theo cabin đã chọn). Vui lòng chọn Stripe hoặc PayPal.',
                ], 400);
            }

            $description = $data['description'] ?? 'Payment';
            $returnBase = SafeRedirect::normalize($data['return_base'] ?? null);
            $internalTxId = uniqid('tx_', true);
            $statusToken = $this->generateStatusToken($internalTxId);

            [$successUrl, $cancelUrl] = $this->buildCallbackUrls($request, $method, $internalTxId, $returnBase, $statusToken);

            DB::beginTransaction();
            try {
                $payment = Payment::create([
                    'internal_tx_id' => $internalTxId,
                    'gateway' => $method,
                    'amount' => $amountForDb,
                    'currency' => $currency,
                    'status' => Payment::defaultStatus(),
                ]);

                $bookingData = $data['booking'] ?? null;
                if ($bookingData && !empty($bookingData['full_name'])) {
                    $bookingCode = 'BK' . date('Ymd') . strtoupper(substr(uniqid(), -6));
                    [$subtotalCabinsCurrency, $subtotalAmenitiesCurrency, $totalAmountCurrency] = $this->calculateBookingSubtotals($bookingData, $currency, $amountForGateway);
                    $subtotalCabins = $this->toStorageAmount($subtotalCabinsCurrency, $currency);
                    $subtotalAmenities = $this->toStorageAmount($subtotalAmenitiesCurrency, $currency);
                    $totalAmount = $this->toStorageAmount($totalAmountCurrency, $currency);
                    $rawItineraryId = $bookingData['itinerary_id'] ?? null;
                    $itineraryId = null;
                    if ($rawItineraryId !== null && $rawItineraryId !== '') {
                        $itineraryId = is_numeric($rawItineraryId) ? (int) $rawItineraryId : null;
                    }
                    $rawEmail = isset($bookingData['email']) ? trim((string) $bookingData['email']) : '';
                    $booking = Booking::create([
                        'payment_id' => $payment->id,
                        'code' => $bookingCode,
                        'full_name' => $bookingData['full_name'] ?? '',
                        'email' => $rawEmail !== '' ? $rawEmail : null,
                        'phone' => $bookingData['phone'] ?? null,
                        'departure_date' => !empty($bookingData['departure_date']) ? $bookingData['departure_date'] : null,
                        'itinerary_id' => $itineraryId,
                        'itinerary_name' => $bookingData['itinerary_name'] ?? null,
                        'cruise_name' => $bookingData['cruise_name'] ?? null,
                        'itinerary_duration_label' => $bookingData['itinerary_duration_label'] ?? null,
                        'destination' => $bookingData['destination'] ?? null,
                        'guests_total' => (int) ($bookingData['guests_total'] ?? 0),
                        'currency' => $currency,
                        'subtotal_cabins' => $subtotalCabins,
                        'subtotal_amenities' => $subtotalAmenities,
                        'discount_amount' => 0,
                        'tax_amount' => 0,
                        'total_amount' => $totalAmount,
                        'status' => config('statuses.booking.pending', 'pending'),
                    ]);
                    foreach ($bookingData['cabins'] ?? [] as $row) {
                        $cabinPayload = [
                            'booking_id' => $booking->id,
                            'cabin_name' => $row['cabin_name'] ?? '',
                            'cabin_description' => $row['cabin_description'] ?? null,
                            'unit_price' => $this->toStorageAmount((float) ($row['unit_price'] ?? 0), $currency),
                            'quantity' => (int) ($row['quantity'] ?? 1),
                            'adults' => (int) ($row['adults'] ?? 0),
                            'children_6_12' => (int) ($row['children_6_12'] ?? 0),
                            'children_2_5' => (int) ($row['children_2_5'] ?? 0),
                            'infants' => (int) ($row['infants'] ?? 0),
                            'total_price' => $this->toStorageAmount((float) ($row['total_price'] ?? 0), $currency),
                        ];
                        if (isset($row['cabin_id']) && is_numeric($row['cabin_id'])) {
                            $cabinPayload['cabin_id'] = (int) $row['cabin_id'];
                        }
                        BookingCabin::create($cabinPayload);
                    }
                    foreach ($bookingData['amenities'] ?? [] as $row) {
                        BookingAmenity::create([
                            'booking_id' => $booking->id,
                            'amenity_name' => $row['amenity_name'] ?? '',
                            'unit_price' => $this->toStorageAmount((float) ($row['unit_price'] ?? 0), $currency),
                            'quantity' => (int) ($row['quantity'] ?? 0),
                            'total_price' => $this->toStorageAmount((float) ($row['total_price'] ?? 0), $currency),
                        ]);
                    }
                    PaymentCustomer::create([
                        'payment_id' => $payment->id,
                        'full_name' => $bookingData['full_name'] ?? '',
                        'email' => $bookingData['email'] ?? null,
                        'phone' => $bookingData['phone'] ?? null,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent() ? mb_substr($request->userAgent(), 0, 255) : null,
                        'gateway_payload' => $request->except(['booking']),
                    ]);
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }

            $context = [
                'method' => $method,
                'amount' => $amountForGateway,
                'currency' => $currency,
                'description' => $description,
                'internal_tx_id' => $internalTxId,
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ];

            $result = $this->payments->init($context);

            $payment = Payment::where('internal_tx_id', $internalTxId)->first();
            if ($payment && $result->providerTxId) {
                $payment->update(['provider_tx_id' => $result->providerTxId]);
            }

            return response()->json([
                'success' => true,
                'internal_tx_id' => $result->internalTxId,
                'provider_tx_id' => $result->providerTxId,
                'payment_url' => $result->paymentUrl,
                'status_token' => $statusToken,
            ]);
        } catch (\Throwable $e) {
            Log::error('Payment init error', ['exception' => $e]);

            if (isset($internalTxId)) {
                $payment = Payment::where('internal_tx_id', $internalTxId)->first();
                if ($payment && $payment->status === Payment::statusPending()) {
                    $payment->update([
                        'status' => Payment::statusFailed(),
                        'error_code' => 'payment_init_error',
                        'error_message' => $e->getMessage(),
                        'completed_at' => now(),
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'code' => 'payment_init_error',
                'message' => 'Khởi tạo thanh toán thất bại.',
            ], 500);
        }
    }

    public function status(Request $request)
    {
        $internalTxId = $request->query('internal_tx_id') ?: $request->query('tx');
        if (!$internalTxId) {
            return response()->json([
                'success' => false,
                'code' => 'missing_tx_id',
                'message' => 'Thiếu internal_tx_id hoặc tx.',
            ], 400);
        }

        $payment = Payment::where('internal_tx_id', $internalTxId)->first();
        if (!$payment) {
            return response()->json([
                'success' => false,
                'code' => 'not_found',
                'message' => 'Không tìm thấy giao dịch.',
            ], 404);
        }

        $statusToken = $request->query('token');
        $canViewBooking = $this->isValidStatusToken($internalTxId, $statusToken);

        if ($payment->status === Payment::statusPending()) {
            try {
                $this->payments->check($payment->gateway, $internalTxId, ['internal_tx_id' => $internalTxId]);
                $payment->refresh();
            } catch (\Throwable $e) {
                Log::debug('Payment status check', ['error' => $e->getMessage()]);
            }
        }

        $message = Payment::statusMessage($payment->status);
        if ($payment->error_code) {
            $customMsg = config('payment.error_messages.' . $payment->error_code) ?? $payment->error_message;
            if ($customMsg) {
                $message = $customMsg;
            }
        }

        $bookingPayload = null;
        if ($canViewBooking) {
            $booking = Booking::with(['cabins', 'amenities'])->where('payment_id', $payment->id)->first();
            if ($booking) {
                $currency = $booking->currency ?? 'usd';
                $bookingPayload = [
                    'code' => $booking->code,
                    'full_name' => $booking->full_name,
                    'email' => $booking->email,
                    'phone' => $booking->phone,
                    'departure_date' => $booking->departure_date ? $booking->departure_date->toDateString() : null,
                    'itinerary_id' => $booking->itinerary_id,
                    'itinerary_name' => $booking->itinerary_name,
                    'cruise_name' => $booking->cruise_name,
                    'itinerary_duration_label' => $booking->itinerary_duration_label,
                    'destination' => $booking->destination,
                    'guests_total' => $booking->guests_total,
                    'currency' => $currency,
                    'subtotal_cabins' => $this->fromStorageAmount($booking->subtotal_cabins, $currency),
                    'subtotal_amenities' => $this->fromStorageAmount($booking->subtotal_amenities, $currency),
                    'discount_amount' => $this->fromStorageAmount($booking->discount_amount, $currency),
                    'tax_amount' => $this->fromStorageAmount($booking->tax_amount, $currency),
                    'total_amount' => $this->fromStorageAmount($booking->total_amount, $currency),
                    'cabins' => $booking->cabins->map(function (BookingCabin $cabin) use ($currency) {
                        return [
                            'cabin_name' => $cabin->cabin_name,
                            'cabin_description' => $cabin->cabin_description,
                            'unit_price' => $this->fromStorageAmount($cabin->unit_price, $currency),
                            'quantity' => $cabin->quantity,
                            'adults' => $cabin->adults,
                            'children_6_12' => $cabin->children_6_12,
                            'children_2_5' => $cabin->children_2_5,
                            'infants' => $cabin->infants,
                            'total_price' => $this->fromStorageAmount($cabin->total_price, $currency),
                        ];
                    })->values()->all(),
                    'amenities' => $booking->amenities->map(function (BookingAmenity $amenity) use ($currency) {
                        return [
                            'amenity_name' => $amenity->amenity_name,
                            'unit_price' => $this->fromStorageAmount($amenity->unit_price, $currency),
                            'quantity' => $amenity->quantity,
                            'total_price' => $this->fromStorageAmount($amenity->total_price, $currency),
                        ];
                    })->values()->all(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'internal_tx_id' => $payment->internal_tx_id,
            'status' => $payment->status,
            'provider_tx_id' => $payment->provider_tx_id,
            'message' => $message,
            'error_code' => $payment->error_code,
            'error_message' => $payment->error_message,
            'booking' => $bookingPayload,
        ]);
    }

    public function cancel(Request $request)
    {
        $data = $request->validate([
            'internal_tx_id' => 'required|string',
            'token' => 'required|string',
        ]);

        $internalTxId = $data['internal_tx_id'];
        if (!$this->isValidStatusToken($internalTxId, $data['token'])) {
            return response()->json([
                'success' => false,
                'code' => 'invalid_token',
                'message' => 'Token không hợp lệ.',
            ], 403);
        }

        $payment = Payment::where('internal_tx_id', $internalTxId)->first();
        if (!$payment) {
            return response()->json([
                'success' => false,
                'code' => 'not_found',
                'message' => 'Không tìm thấy giao dịch.',
            ], 404);
        }

        if ($payment->status === Payment::statusPending()) {
            $payment->update([
                'status' => Payment::statusCanceled(),
                'error_code' => 'user_cancelled',
                'error_message' => 'User cancelled payment session',
                'completed_at' => now(),
            ]);
            Log::info('Payment cancelled via API', ['tx' => $internalTxId]);
        }

        return response()->json([
            'success' => true,
            'internal_tx_id' => $payment->internal_tx_id,
            'status' => $payment->fresh()->status,
        ]);
    }

    public function callback($method, Request $request)
    {
        if (!in_array($method, Payment::validMethods(), true)) {
            return response()->json([
                'success' => false,
                'code' => 'payment_method_invalid',
                'message' => 'Phương thức thanh toán không hợp lệ.',
            ], 400);
        }

        $internalTxId = $request->query('tx');
        $resultParam = $request->query('result');
        $redirectUrl = $request->query('redirect');

        if ($internalTxId) {
            $payment = Payment::where('internal_tx_id', $internalTxId)->first();
            if ($payment && $resultParam === 'cancel') {
                if ($payment->status === Payment::statusPending()) {
                    $payment->update([
                        'status' => Payment::statusCanceled(),
                        'error_code' => 'user_cancelled',
                        'error_message' => 'User cancelled (callback)',
                        'completed_at' => now(),
                    ]);
                    Log::info('Payment callback: updated to canceled', ['tx' => $internalTxId]);
                }
            }
        }

        try {
            $result = $this->payments->confirm($method, $request);
            $payment = Payment::where('internal_tx_id', $result->internalTxId)->first();
            $status = $payment ? $payment->status : $result->status;

            if ($redirectUrl) {
                return $this->redirectToReturnUrl($redirectUrl, $resultParam ?? 'success', $result->internalTxId, $method);
            }
            return response()->json([
                'success' => true,
                'internal_tx_id' => $result->internalTxId,
                'status' => $status,
                'provider_tx_id' => $result->providerTxId ?? ($payment ? $payment->provider_tx_id : null),
                'message' => Payment::statusMessage($status),
            ]);
        } catch (\Throwable $e) {
            Log::error('Payment callback error', ['exception' => $e, 'method' => $method]);
            if (!empty($redirectUrl)) {
                return $this->redirectToReturnUrl($redirectUrl, 'cancel', $internalTxId ?? '', $method);
            }

            return response()->json([
                'success' => false,
                'code' => 'payment_callback_error',
                'message' => 'Xử lý kết quả thanh toán thất bại.',
            ], 500);
        }
    }

    protected function resolveCurrencyFromBooking(array $bookingData): ?string
    {
        $cabins = $bookingData['cabins'] ?? [];
        $firstCabinId = null;
        foreach ($cabins as $cabin) {
            $id = $cabin['cabin_id'] ?? null;
            if ($id !== null && $id !== '' && is_numeric($id)) {
                $firstCabinId = (int) $id;
                break;
            }
        }
        if ($firstCabinId === null) {
            return null;
        }
        $languageId = DB::table('app_cabin')->where('id', $firstCabinId)->value('language_id');
        if ($languageId === null) {
            return null;
        }
        $language = AdLanguage::find($languageId);
        if (!$language || !$language->code) {
            return null;
        }
        $code = strtolower(trim($language->code));
        return $code === 'vi' ? 'vnd' : 'usd';
    }

    protected function resolveCurrencyFromDefaultLanguage(): string
    {
        $language = AdLanguage::where('is_default', true)->first();
        if (!$language || !$language->code) {
            return 'vnd';
        }
        $code = strtolower(trim($language->code));
        return $code === 'vi' ? 'vnd' : 'usd';
    }

    protected function buildCallbackUrls(Request $request, string $method, string $internalTxId, ?string $returnBase, string $statusToken): array
    {
        $host = $request->getSchemeAndHttpHost();
        $path = '/api/payment/callback/' . $method;
        $base = rtrim($host, '/') . $path;
        $query = 'tx=' . urlencode($internalTxId) . '&method=' . urlencode($method);
        $redirect = '';
        if ($returnBase) {
            $sep = strpos($returnBase, '?') !== false ? '&' : '?';
            $redirectTarget = $returnBase . $sep . 'token=' . urlencode($statusToken);
            $redirect = '&redirect=' . urlencode($redirectTarget);
        }
        return [
            $base . '?result=success&' . $query . $redirect,
            $base . '?result=cancel&' . $query . $redirect,
        ];
    }

    protected function calculateAmountFromBooking(array $bookingData, string $currency): float
    {
        [$subtotalCabins, $subtotalAmenities, $total] = $this->calculateBookingTotals($bookingData);

        if ($total <= 0) {
            return 0.0;
        }

        if ($currency === 'vnd') {
            return round($total, 0);
        }

        return round($total, 2);
    }

    protected function calculateBookingSubtotals(array $bookingData, string $currency, float $paymentAmount): array
    {
        [$subtotalCabinsRaw, $subtotalAmenitiesRaw, $totalRaw] = $this->calculateBookingTotals($bookingData);

        if ($currency === 'vnd') {
            $subtotalCabins = (int) round($subtotalCabinsRaw);
            $subtotalAmenities = (int) round($subtotalAmenitiesRaw);
            $totalAmount = $subtotalCabins + $subtotalAmenities;

            return [$subtotalCabins, $subtotalAmenities, $totalAmount];
        }

        if ($currency === 'usd' && $totalRaw > 0 && $paymentAmount > 0) {
            $paymentCents = (int) round($paymentAmount * 100);
            $subtotalCabinsCentsRaw = (int) round($subtotalCabinsRaw * 100);
            $subtotalAmenitiesCentsRaw = (int) round($subtotalAmenitiesRaw * 100);
            $totalRawCents = $subtotalCabinsCentsRaw + $subtotalAmenitiesCentsRaw;

            if ($totalRawCents <= 0) {
                return [
                    $paymentAmount,
                    0.0,
                    $paymentAmount,
                ];
            }

            $subtotalCabinsCents = (int) floor($subtotalCabinsCentsRaw * $paymentCents / $totalRawCents);
            $subtotalAmenitiesCents = $paymentCents - $subtotalCabinsCents;
            $totalAmountCents = $paymentCents;

            return [
                $subtotalCabinsCents / 100,
                $subtotalAmenitiesCents / 100,
                $totalAmountCents / 100,
            ];
        }

        $subtotalCabins = (int) round($subtotalCabinsRaw);
        $subtotalAmenities = (int) round($subtotalAmenitiesRaw);
        $totalAmount = $subtotalCabins + $subtotalAmenities;

        return [$subtotalCabins, $subtotalAmenities, $totalAmount];
    }

    protected function calculateBookingTotals(array $bookingData): array
    {
        $cabins = $bookingData['cabins'] ?? [];
        $amenities = $bookingData['amenities'] ?? [];

        [$subtotalCabins, $usedDb] = $this->calculateCabinsTotalFromDb($cabins, $bookingData);
        if (!$usedDb) {
            $subtotalCabins = 0.0;
            foreach ($cabins as $row) {
                $lineTotal = isset($row['total_price']) ? (float) $row['total_price'] : 0.0;
                $qty = isset($row['quantity']) ? (int) $row['quantity'] : 1;
                if ($qty < 1) {
                    $qty = 1;
                }
                $subtotalCabins += $lineTotal * $qty;
            }
        }

        [$subtotalAmenities, $usedAmenityDb] = $this->calculateAmenitiesTotalFromDb($amenities);
        if (!$usedAmenityDb) {
            $subtotalAmenities = 0.0;
        }

        $total = $subtotalCabins + $subtotalAmenities;

        return [$subtotalCabins, $subtotalAmenities, $total];
    }

    protected function calculateCabinsTotalFromDb(array $cabins, array $bookingData): array
    {
        $duration = $this->resolveItineraryDurationFromBooking($bookingData);
        if ($duration === null) {
            return [0.0, false];
        }

        $cabinIds = [];
        foreach ($cabins as $row) {
            $id = $row['cabin_id'] ?? null;
            if ($id !== null && $id !== '' && is_numeric($id)) {
                $cabinIds[] = (int) $id;
            }
        }
        $cabinIds = array_values(array_unique($cabinIds));
        if (empty($cabinIds)) {
            return [0.0, false];
        }

        $cabinMeta = DB::table('app_cabin')
            ->select(
                'id',
                'capacity',
                'over_capacity_adult',
                'over_capacity_child_6_12',
                'over_capacity_child_2_5',
                'over_capacity_infant'
            )
            ->whereIn('id', $cabinIds)
            ->get()
            ->keyBy('id');

        if ($cabinMeta->isEmpty()) {
            return [0.0, false];
        }

        $pricesData = DB::table('app_cabin_price')
            ->whereIn('cabin_id', $cabinIds)
            ->where('duration', $duration)
            ->select('cabin_id', 'guest', 'price')
            ->get();

        $pricesByCabin = [];
        foreach ($pricesData as $row) {
            $cabinId = (int) $row->cabin_id;
            $guest = (int) $row->guest;
            if (!isset($pricesByCabin[$cabinId])) {
                $pricesByCabin[$cabinId] = [];
            }
            $pricesByCabin[$cabinId][$guest] = (float) $row->price;
        }

        if (empty($pricesByCabin)) {
            return [0.0, false];
        }

        $subtotalCabins = 0.0;
        foreach ($cabins as $row) {
            $id = $row['cabin_id'] ?? null;
            if ($id === null || $id === '' || !is_numeric($id)) {
                $lineTotal = isset($row['total_price']) ? (float) $row['total_price'] : 0.0;
                $qty = isset($row['quantity']) ? (int) $row['quantity'] : 1;
                if ($qty < 1) {
                    $qty = 1;
                }
                $subtotalCabins += $lineTotal * $qty;
                continue;
            }

            $cabinId = (int) $id;
            if (!$cabinMeta->has($cabinId) || !isset($pricesByCabin[$cabinId])) {
                $lineTotal = isset($row['total_price']) ? (float) $row['total_price'] : 0.0;
                $qty = isset($row['quantity']) ? (int) $row['quantity'] : 1;
                if ($qty < 1) {
                    $qty = 1;
                }
                $subtotalCabins += $lineTotal * $qty;
                continue;
            }

            $meta = $cabinMeta->get($cabinId);
            $prices = $pricesByCabin[$cabinId];

            $guests = [
                'adults' => isset($row['adults']) ? (int) $row['adults'] : 0,
                'children_6_12' => isset($row['children_6_12']) ? (int) $row['children_6_12'] : 0,
                'children_2_5' => isset($row['children_2_5']) ? (int) $row['children_2_5'] : 0,
                'infants' => isset($row['infants']) ? (int) $row['infants'] : 0,
            ];

            $pricePerCabin = $this->calculateCabinPriceFromDb($meta, $prices, $guests);

            $qty = isset($row['quantity']) ? (int) $row['quantity'] : 1;
            if ($qty < 1) {
                $qty = 1;
            }

            $subtotalCabins += $pricePerCabin * $qty;
        }

        return [$subtotalCabins, true];
    }

    protected function calculateAmenitiesTotalFromDb(array $amenities): array
    {
        $amenityIds = [];
        foreach ($amenities as $row) {
            $id = $row['amenity_id'] ?? null;
            if ($id !== null && $id !== '' && is_numeric($id)) {
                $amenityIds[] = (int) $id;
            }
        }

        $amenityIds = array_values(array_unique($amenityIds));
        if (empty($amenityIds)) {
            return [0.0, false];
        }

        $prices = DB::table('app_service')
            ->whereIn('id', $amenityIds)
            ->pluck('price', 'id');

        if ($prices->isEmpty()) {
            return [0.0, false];
        }

        $subtotalAmenities = 0.0;
        foreach ($amenities as $row) {
            $id = $row['amenity_id'] ?? null;
            $qty = isset($row['quantity']) ? (int) $row['quantity'] : 0;
            if ($qty < 1 || $id === null || !is_numeric($id)) {
                continue;
            }

            $amenityId = (int) $id;
            if (!$prices->has($amenityId)) {
                continue;
            }

            $subtotalAmenities += (float) $prices[$amenityId] * $qty;
        }

        return [$subtotalAmenities, true];
    }

    protected function generateStatusToken(string $internalTxId): string
    {
        return hash_hmac('sha256', $internalTxId, (string) config('app.key'));
    }

    protected function isValidStatusToken(string $internalTxId, ?string $token): bool
    {
        if (!$token) {
            return false;
        }

        return hash_equals($this->generateStatusToken($internalTxId), $token);
    }

    protected function resolveItineraryDurationFromBooking(array $bookingData): ?int
    {
        $rawId = $bookingData['itinerary_id'] ?? null;
        if ($rawId === null || $rawId === '' || !is_numeric($rawId)) {
            return null;
        }

        $itineraryId = (int) $rawId;
        $duration = DB::table('app_itinerary')->where('id', $itineraryId)->value('duration');
        if ($duration === null) {
            return null;
        }

        return (int) $duration;
    }

    protected function calculateCabinPriceFromDb($meta, array $pricesByGuest, array $guests): float
    {
        $capacity = (int) ($meta->capacity ?? 0);
        if ($capacity <= 0) {
            return 0.0;
        }

        $adults = max(0, (int) ($guests['adults'] ?? 0));
        $children_6_12 = max(0, (int) ($guests['children_6_12'] ?? 0));
        $children_2_5 = max(0, (int) ($guests['children_2_5'] ?? 0));
        $infants = max(0, (int) ($guests['infants'] ?? 0));

        $totalGuests = $adults + $children_6_12 + $children_2_5;
        if ($totalGuests <= 0) {
            return 0.0;
        }

        if ($totalGuests <= $capacity) {
            $basePrice = $pricesByGuest[$totalGuests] ?? 0.0;
            if ($basePrice <= 0 && $totalGuests > 0) {
                $maxCapacityPrice = $pricesByGuest[$capacity] ?? 0.0;
                if ($maxCapacityPrice > 0) {
                    $basePrice = ($maxCapacityPrice / $capacity) * $totalGuests;
                }
            }

            return $basePrice > 0 ? $basePrice : 0.0;
        }

        $basePrice = $pricesByGuest[$capacity] ?? 0.0;
        if ($basePrice <= 0) {
            return 0.0;
        }

        $pricePerPerson = $basePrice / $capacity;

        $remainingInBase = $capacity;

        $adultsInBase = min($adults, $remainingInBase);
        $remainingInBase -= $adultsInBase;
        $adultsExceeded = $adults - $adultsInBase;

        $children_6_12_inBase = min($children_6_12, $remainingInBase);
        $remainingInBase -= $children_6_12_inBase;
        $children_6_12_exceeded = $children_6_12 - $children_6_12_inBase;

        $children_2_5_inBase = min($children_2_5, $remainingInBase);
        $children_2_5_exceeded = $children_2_5 - $children_2_5_inBase;

        $extraCharges = 0.0;
        $extraCharges += $adultsExceeded * $pricePerPerson;
        $extraCharges += $children_6_12_exceeded * $pricePerPerson * 0.75;
        $extraCharges += $children_2_5_exceeded * $pricePerPerson * 0.5;

        $totalPrice = $basePrice + $extraCharges;

        return $totalPrice > 0 ? $totalPrice : 0.0;
    }

    protected function redirectToReturnUrl(string $redirectUrl, string $result, string $tx, string $method)
    {
        $safeUrl = SafeRedirect::normalize($redirectUrl);
        if (!$safeUrl) {
            return response()->json([
                'success' => false,
                'code' => 'invalid_redirect',
                'message' => 'URL chuyển hướng không hợp lệ.',
            ], 400);
        }

        $sep = strpos($safeUrl, '?') !== false ? '&' : '?';
        $target = rtrim($safeUrl, '?&') . $sep . 'result=' . urlencode($result) . '&tx=' . urlencode($tx) . '&method=' . urlencode($method);
        return redirect()->to($target);
    }

    protected function toStorageAmount(float $amount, string $currency): int
    {
        $amount = max(0, $amount);
        $currency = strtolower($currency);

        if ($currency === 'usd') {
            return (int) round($amount * 100);
        }

        return (int) round($amount);
    }

    protected function fromStorageAmount(?int $amount, string $currency): float
    {
        $value = $amount ?? 0;
        $currency = strtolower($currency);

        if ($currency === 'usd') {
            return round($value / 100, 2);
        }

        return (float) $value;
    }
}

