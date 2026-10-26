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
use App\Payments\BookingPricer;
use App\Payments\BookingSchema;
use App\Payments\PaymentManager;
use App\Payments\PaymentPlan;
use App\Payments\TermsAcceptance;
use App\Support\SafeRedirect;
use Modules\BackEnd\Entities\AdLanguage;

class PaymentController extends Controller
{
    protected $payments;

    protected BookingPricer $pricer;

    public function __construct(PaymentManager $payments, BookingPricer $pricer)
    {
        $this->payments = $payments;
        $this->pricer = $pricer;
    }

    /**
     * GET /api/booking/policy: what the booking page must offer and ask for. Both switches are off
     * until TPX approves (config/booking.php); the page then behaves exactly as before.
     */
    public function policy()
    {
        $termsRequired = TermsAcceptance::required();

        return response()->json([
            'success' => true,
            'deposit_enabled' => PaymentPlan::enabled(),
            'deposit_percent' => (int) config('booking.deposit_percent', 30),
            'balance_days_before' => (int) config('booking.balance_days_before', 30),
            'terms_required' => $termsRequired,
            'terms_version' => $termsRequired ? (string) config('booking.terms_version') : null,
            'today' => PaymentPlan::today()->toDateString(),
        ]);
    }

    public function storeInquiry(Request $request)
    {
        $data = $request->validate([
            'terms' => 'sometimes|array',
            'terms.accepted' => 'sometimes|boolean',
            'terms.version' => 'sometimes|nullable|string|max:32',
            'terms.locale' => 'sometimes|nullable|string|max:8',
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

        $terms = $this->acceptedTerms($data);
        if (isset($terms['error'])) {
            return $this->termsError($terms['error']);
        }

        try {
            DB::beginTransaction();
            $internalTxId = 'inquiry_' . uniqid('', true);

            // Reservation only (nothing is charged): prefer server prices, but still accept the request
            // with the guest's displayed totals when it can't be priced, so the team can follow up.
            $priced = $this->pricer->price($bookingData);
            if ($priced !== null) {
                $currency = $priced['currency'];
                [$subtotalCabinsRaw, $subtotalAmenitiesRaw, $totalRaw] = [$priced['subtotal_cabins'], $priced['subtotal_amenities'], $priced['total']];
            } else {
                [$subtotalCabinsRaw, $subtotalAmenitiesRaw, $totalRaw] = $this->clientTotals($bookingData);
            }

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
            $itineraryId = BookingPricer::parseItineraryId($bookingData['itinerary_id'] ?? null);
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
            ] + (BookingSchema::ready() ? ['payment_status' => Booking::PAYMENT_UNPAID] + ($terms ?? []) : []));
            if (BookingSchema::ready()) {
                $payment->update(['booking_id' => $booking->id]);
            }
            $this->storeLineItems($booking, $bookingData, $priced, $currency);
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
            'payment_plan' => 'sometimes|nullable|string|in:deposit,full',
            'terms' => 'sometimes|array',
            'terms.accepted' => 'sometimes|boolean',
            'terms.version' => 'sometimes|nullable|string|max:32',
            'terms.locale' => 'sometimes|nullable|string|max:8',
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
            // The amount is always computed from the database; client-side prices are ignored.
            $priced = $this->pricer->price($bookingData);
            if ($priced === null) {
                Log::warning('Payment init rejected: booking could not be priced', ['reason' => $this->pricer->error]);
                return response()->json([
                    'success' => false,
                    'code' => 'amount_calculation_failed',
                    'reason' => $this->pricer->error,
                    'message' => 'Không tính được số tiền thanh toán từ dữ liệu trên máy chủ. Vui lòng chọn lại hành trình, phòng và số khách.',
                ], 400);
            }
            $currency = $priced['currency'];

            $terms = $this->acceptedTerms($data);
            if (isset($terms['error'])) {
                return $this->termsError($terms['error']);
            }

            // Deposit or full payment (rules 1-3). Null while deposits are switched off: full payment, as before.
            $plan = $this->planFor($bookingData, (float) $priced['total'], $currency, $data['payment_plan'] ?? null);
            if (isset($plan['error'])) {
                return response()->json([
                    'success' => false,
                    'code' => $plan['error'],
                    'message' => 'Ngày khởi hành không hợp lệ cho hành trình đã chọn. Vui lòng chọn lại ngày khởi hành.',
                ], 422);
            }
            $amountForGateway = $plan ? $plan['due_now'] : $priced['total'];

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
            if (($plan['plan'] ?? null) === PaymentPlan::DEPOSIT) {
                $description = 'Deposit ' . (int) config('booking.deposit_percent', 30) . '% - ' . $description;
            }
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
                ] + (BookingSchema::ready() ? ['purpose' => $plan['plan'] ?? PaymentPlan::FULL] : []));

                $bookingData = $data['booking'] ?? null;
                if ($bookingData && !empty($bookingData['full_name'])) {
                    $bookingCode = 'BK' . date('Ymd') . strtoupper(substr(uniqid(), -6));
                    $subtotalCabins = $this->toStorageAmount($priced['subtotal_cabins'], $currency);
                    $subtotalAmenities = $this->toStorageAmount($priced['subtotal_amenities'], $currency);
                    // The booking keeps the full price; the payment holds what is charged now (all of it, or the deposit).
                    $totalAmount = $this->toStorageAmount($priced['total'], $currency);
                    $itineraryId = BookingPricer::parseItineraryId($bookingData['itinerary_id'] ?? null);
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
                    ] + (BookingSchema::ready() ? [
                        'payment_plan' => $plan['plan'] ?? PaymentPlan::FULL,
                        'payment_status' => Booking::PAYMENT_UNPAID,
                        'balance_due_date' => $plan['balance_due_date'] ?? null,
                    ] + ($terms ?? []) : []));
                    if (BookingSchema::ready()) {
                        $payment->update(['booking_id' => $booking->id]);
                    }
                    $this->storeLineItems($booking, $bookingData, $priced, $currency);
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
            ] + ($plan ? [
                'payment_plan' => $plan['plan'],
                'currency' => $currency,
                'amount_due_now' => $plan['due_now'],
                'balance_amount' => $plan['balance'],
                'balance_due_date' => $plan['balance_due_date'],
            ] : []));
        } catch (\Throwable $e) {
            Log::error('Payment init error', ['exception' => $e]);

            if (isset($internalTxId)) {
                $payment = Payment::where('internal_tx_id', $internalTxId)->first();
                if ($payment && $payment->status === Payment::statusPending()) {
                    $payment->markIfPending([
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
                ] + (BookingSchema::ready() && $booking->payment_plan !== null ? [
                    'payment_plan' => $booking->payment_plan,
                    'payment_status' => $booking->payment_status,
                    'amount_paid' => $this->fromStorageAmount($booking->amount_paid, $currency),
                    'balance_amount' => $this->fromStorageAmount($booking->balanceAmount(), $currency),
                    'balance_due_date' => $booking->balance_due_date ? $booking->balance_due_date->toDateString() : null,
                ] : []) + [
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
            $payment->markIfPending([
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
                if (!$this->isValidStatusToken($internalTxId, $request->query('st'))) {
                    Log::warning('Payment callback: cancel ignored, invalid status token', ['tx' => $internalTxId]);
                } elseif ($payment->status === Payment::statusPending()) {
                    $payment->markIfPending([
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

    /**
     * Booking attributes recording the guest's acceptance of the Booking Terms, [] when acceptance is
     * not required (yet), or ['error' => code].
     */
    protected function acceptedTerms(array $data): array
    {
        if (!TermsAcceptance::required()) {
            return [];
        }
        [$attributes, $error] = TermsAcceptance::check($data['terms'] ?? null);

        return $error ? ['error' => $error] : $attributes;
    }

    protected function termsError(string $code)
    {
        $messages = [
            'terms_required' => 'Vui lòng đọc và đồng ý với Điều khoản đặt du thuyền trước khi xác nhận. / Please accept the Booking Terms & Conditions to continue.',
            'terms_version_mismatch' => 'Điều khoản đặt du thuyền vừa được cập nhật. Vui lòng tải lại trang. / The Booking Terms have been updated. Please reload the page.',
            'terms_not_configured' => 'Tạm thời chưa thể đặt chỗ trực tuyến. Vui lòng liên hệ chúng tôi. / Online booking is temporarily unavailable. Please contact us.',
        ];
        Log::warning('Booking refused: terms', ['code' => $code]);

        return response()->json([
            'success' => false,
            'code' => $code,
            'message' => $messages[$code] ?? $messages['terms_required'],
        ], $code === 'terms_version_mismatch' ? 409 : ($code === 'terms_not_configured' ? 503 : 422));
    }

    /**
     * @return array{plan: string, due_now: float, balance: float, balance_due_date: ?string}|array{error: string}|null
     *         null while deposits are switched off (full payment, as before)
     */
    protected function planFor(array $bookingData, float $total, string $currency, ?string $requested): ?array
    {
        if (!PaymentPlan::enabled()) {
            return null;
        }
        $date = (string) ($bookingData['departure_date'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return ['error' => 'departure_date_required'];
        }
        if (PaymentPlan::daysBefore($date) < 0) {
            return ['error' => 'departure_date_past'];
        }
        if (!PaymentPlan::isScheduledDeparture($bookingData['itinerary_id'] ?? null, $date)) {
            return ['error' => 'departure_date_unknown'];
        }
        $plan = PaymentPlan::choose($requested, $date);

        return ['plan' => $plan]
            + PaymentPlan::amounts($total, $currency, $plan)
            + ['balance_due_date' => $plan === PaymentPlan::DEPOSIT ? PaymentPlan::balanceDueDate($date) : null];
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
        // "st" (status token) proves the callback came from a URL we issued. It is not called "token"
        // because PayPal appends its own token (the order id) to return URLs.
        $query = 'tx=' . urlencode($internalTxId) . '&method=' . urlencode($method) . '&st=' . urlencode($statusToken);
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

    /**
     * Booking line items. Prices come from $priced (server-side) when available; names and guest
     * counts come from the request because they are display-only.
     */
    protected function storeLineItems(Booking $booking, array $bookingData, ?array $priced, string $currency): void
    {
        foreach (array_values($bookingData['cabins'] ?? []) as $i => $row) {
            $line = $priced['cabins'][$i] ?? null;
            $payload = [
                'booking_id' => $booking->id,
                'cabin_name' => $row['cabin_name'] ?? '',
                'cabin_description' => $row['cabin_description'] ?? null,
                'unit_price' => $this->toStorageAmount($line['unit_price'] ?? (float) ($row['unit_price'] ?? 0), $currency),
                'quantity' => $line['quantity'] ?? (int) ($row['quantity'] ?? 1),
                'adults' => (int) ($row['adults'] ?? 0),
                'children_6_12' => (int) ($row['children_6_12'] ?? 0),
                'children_2_5' => (int) ($row['children_2_5'] ?? 0),
                'infants' => (int) ($row['infants'] ?? 0),
                'total_price' => $this->toStorageAmount($line['total_price'] ?? (float) ($row['total_price'] ?? 0), $currency),
            ];
            if (is_numeric($row['cabin_id'] ?? null)) {
                $payload['cabin_id'] = (int) $row['cabin_id'];
            }
            BookingCabin::create($payload);
        }

        // BookingPricer skips amenity rows with quantity 0, so match them up in the same order.
        $pricedAmenities = $priced['amenities'] ?? [];
        $index = 0;
        foreach ($bookingData['amenities'] ?? [] as $row) {
            $line = (int) ($row['quantity'] ?? 0) > 0 ? ($pricedAmenities[$index++] ?? null) : null;
            BookingAmenity::create([
                'booking_id' => $booking->id,
                'amenity_id' => $line['amenity_id'] ?? (is_numeric($row['amenity_id'] ?? null) ? (int) $row['amenity_id'] : null),
                'amenity_name' => $row['amenity_name'] ?? '',
                'unit_price' => $this->toStorageAmount($line['unit_price'] ?? (float) ($row['unit_price'] ?? 0), $currency),
                'quantity' => $line['quantity'] ?? (int) ($row['quantity'] ?? 0),
                'total_price' => $this->toStorageAmount($line['total_price'] ?? (float) ($row['total_price'] ?? 0), $currency),
            ]);
        }
    }

    /** Totals as displayed to the guest. Only used for reservation-only inquiries, never for charging. */
    protected function clientTotals(array $bookingData): array
    {
        $sum = fn (array $rows) => array_sum(array_map(
            fn ($row) => (float) ($row['total_price'] ?? 0) * max(1, (int) ($row['quantity'] ?? 1)),
            $rows
        ));
        $cabins = $sum($bookingData['cabins'] ?? []);
        // The booking page sends amenity total_price as the unit price, so multiply by quantity here.
        $amenities = array_sum(array_map(
            fn ($row) => (float) ($row['unit_price'] ?? 0) * max(0, (int) ($row['quantity'] ?? 0)),
            $bookingData['amenities'] ?? []
        ));

        return [$cabins, $amenities, $cabins + $amenities];
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

