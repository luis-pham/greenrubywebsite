<?php

namespace Modules\BackEnd\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentCustomer;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\BookingCancellationMail;
use App\Mail\BookingPaymentMail;
use App\Models\PaymentRefund;
use App\Payments\BookingAudit;
use App\Payments\PaymentLink;
use App\Payments\PaymentPlan;
use App\Payments\RefundService;
use Modules\BackEnd\Helpers\Utilities;

class BookingController extends Controller
{
    private $baseView = 'backend::booking.';

    public function __construct()
    {
        app()->setLocale('vi');
    }

    public function index(Request $request)
    {
        $title = __('backend::booking.page_index');
        \SEO::setTitle($title);

        $query = Booking::with(['payment', 'cabins', 'amenities'])
            ->orderByDesc('id');

        $fromDate = null;
        $toDate = null;
        if ($request->filled('from_date')) {
            try {
                $d = \DateTime::createFromFormat('d/m/Y', $request->get('from_date'));
                if ($d !== false) {
                    $fromDate = $d->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $fromDate = null;
            }
        }
        if ($request->filled('to_date')) {
            try {
                $d = \DateTime::createFromFormat('d/m/Y', $request->get('to_date'));
                if ($d !== false) {
                    $toDate = $d->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $toDate = null;
            }
        }

        if ($request->filled('keyword')) {
            $kw = $request->keyword;
            $query->where(function ($q) use ($kw) {
                $q->where('full_name', 'like', '%' . $kw . '%')
                    ->orWhere('email', 'like', '%' . $kw . '%')
                    ->orWhere('phone', 'like', '%' . $kw . '%')
                    ->orWhere('code', 'like', '%' . $kw . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $list = $query->paginate(config('backend.paginationLimit', 20))->withQueryString();

        $quoteQuery = QuoteRequest::orderByDesc('id');
        if ($request->filled('quote_keyword')) {
            $qkw = $request->quote_keyword;
            $quoteQuery->where(function ($q) use ($qkw) {
                $q->where('contact_name', 'like', '%' . $qkw . '%')
                    ->orWhere('phone', 'like', '%' . $qkw . '%')
                    ->orWhere('code', 'like', '%' . $qkw . '%');
            });
        }
        if ($request->filled('quote_status')) {
            $quoteQuery->where('status', $request->quote_status);
        }

        $quoteFromDate = null;
        $quoteToDate = null;
        if ($request->filled('quote_from_date')) {
            try {
                $d = \DateTime::createFromFormat('d/m/Y', $request->get('quote_from_date'));
                if ($d !== false) {
                    $quoteFromDate = $d->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $quoteFromDate = null;
            }
        }
        if ($request->filled('quote_to_date')) {
            try {
                $d = \DateTime::createFromFormat('d/m/Y', $request->get('quote_to_date'));
                if ($d !== false) {
                    $quoteToDate = $d->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $quoteToDate = null;
            }
        }
        if ($quoteFromDate) {
            $quoteQuery->whereDate('created_at', '>=', $quoteFromDate);
        }
        if ($quoteToDate) {
            $quoteQuery->whereDate('created_at', '<=', $quoteToDate);
        }
        $quoteList = $quoteQuery->paginate(config('backend.paginationLimit', 20), ['*'], 'quote_page')->withQueryString();

        return view($this->baseView . 'index', compact('title', 'list', 'quoteList'));
    }

    public function destroyQuote(Request $request)
    {
        $id = (int) $request->id;
        $quote = QuoteRequest::find($id);
        if (!$quote) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('backend::booking.quote_not_found')]);
            }
            return redirect()->back()->withErrors(__('backend::booking.quote_not_found'));
        }
        $quote->delete();
        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => __('backend::booking.quote_deleted')]);
        }
        return redirect()->back()->with('flash-message', __('backend::booking.quote_deleted'));
    }

    public function quoteStatus($id, Request $request)
    {
        $id = $request->route('id') ?? $id;
        $quote = QuoteRequest::find($id);
        if (!$quote) {
            return response()->json(['success' => false, 'message' => __('backend::booking.quote_not_found')]);
        }
        $quote->update(['status' => 'contacted']);
        return response()->json(['success' => true, 'message' => __('backend::booking.quote_status_updated')]);
    }

    public function show($id, Request $request)
    {
        $id = $request->route('id') ?? $id;
        $title = __('backend::booking.page_show');
        \SEO::setTitle($title);

        $booking = Booking::with(['payment', 'cabins', 'amenities'])->find($id);
        if (!$booking) {
            return abort(404);
        }

        $ops = $this->paymentOperations($booking);

        return view($this->baseView . 'show', compact('title', 'booking', 'ops'));
    }

    /**
     * Money panel of the booking page (TPX decisions 3, 5, 6): links, notifications sent, refunds with
     * approval and the audit trail. Null until migration 2026_10_27_000002 has run.
     */
    private function paymentOperations(Booking $booking): ?array
    {
        if (!BookingAudit::ready()) {
            return null;
        }
        $userId = $this->adminId();
        $suggestions = [];
        foreach (PaymentRefund::REASONS as $reason) {
            $suggestions[$reason] = RefundService::suggest($booking, $reason);
        }
        $names = DB::table('ad_user')->pluck('username', 'id');

        return [
            'linkPurpose' => $this->linkPurpose($booking),
            'termsConfigured' => (string) config('booking.terms_version') !== '',
            'notifications' => DB::table('booking_notifications')->where('booking_id', $booking->id)->orderBy('sent_at')->get(),
            'refunds' => $booking->refunds()->get(),
            'audit' => DB::table('booking_audit_log')->where('booking_id', $booking->id)->orderByDesc('id')->limit(50)->get(),
            'refundable' => RefundService::refundable($booking),
            'suggestions' => $suggestions,
            'isFinance' => RefundService::isFinance($userId),
            'userId' => $userId,
            'names' => $names,
        ];
    }

    /** Which secure link this booking can be sent: acceptance + first payment, or the balance. */
    private function linkPurpose(Booking $booking): ?string
    {
        if (in_array($booking->status, [Booking::statusCancelled(), Booking::statusFailed()], true) || !$booking->departure_date
            || PaymentPlan::daysBefore($booking->departure_date->toDateString()) < 0) {
            return null;
        }
        if ($booking->netPaid() === 0 && in_array($booking->payment_status, [null, Booking::PAYMENT_UNPAID], true) && (int) $booking->total_amount > 0) {
            return PaymentLink::BOOKING;
        }
        if (in_array($booking->payment_status, [Booking::PAYMENT_DEPOSIT_PAID, Booking::PAYMENT_OVERDUE], true) && $booking->balanceAmount() > 0) {
            return PaymentLink::BALANCE;
        }

        return null;
    }

    private function adminId(): ?int
    {
        $id = \Auth::guard('admin')->id() ?? \Auth::id();

        return $id === null ? null : (int) $id;
    }

    /** Reservations sends the guest a secure link to accept the terms and pay, or to pay the balance. */
    public function sendPaymentLink($id, Request $request)
    {
        $id = $request->route('id') ?? $id;
        $booking = Booking::find($id);
        $purpose = $booking && BookingAudit::ready() ? $this->linkPurpose($booking) : null;
        if (!$purpose) {
            return redirect()->back()->withErrors(__('backend::booking.link_not_available'));
        }
        if ($purpose === PaymentLink::BOOKING && (string) config('booking.terms_version') === '') {
            return redirect()->back()->withErrors(__('backend::booking.link_terms_not_configured'));
        }
        if (!$booking->email) {
            return redirect()->back()->withErrors(__('backend::booking.link_no_email'));
        }
        $departureEnd = $booking->departure_date->copy()->endOfDay()->getTimestamp();
        $expires = $purpose === PaymentLink::BOOKING
            ? min(now()->addDays((int) config('booking.payment_link_days', 14))->getTimestamp(), $departureEnd)
            : $departureEnd;
        $url = PaymentLink::url($booking, $purpose, $expires);

        try {
            Mail::to($booking->email)->send(new BookingPaymentMail($booking, $purpose === PaymentLink::BOOKING ? 'payment_link' : 'balance_request', $url, ['expires' => $expires]));
        } catch (\Throwable $e) {
            Log::error('Payment link email failed', ['booking' => $booking->code, 'error' => $e->getMessage()]);

            return redirect()->back()->withErrors(__('backend::booking.link_email_failed'));
        }
        BookingAudit::log($booking->id, 'link.sent', $this->adminId(), ['purpose' => $purpose, 'to' => $booking->email, 'expires' => date('c', $expires)]);

        return redirect()->back()->with('flash-message', __('backend::booking.link_sent', ['email' => $booking->email]));
    }

    public function requestRefund($id, Request $request)
    {
        $id = $request->route('id') ?? $id;
        $booking = Booking::findOrFail($id);
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|in:' . implode(',', PaymentRefund::REASONS),
            'note' => 'nullable|string|max:2000',
        ]);
        $amount = strtolower((string) $booking->currency) === 'usd' ? (int) round($data['amount'] * 100) : (int) round($data['amount']);

        try {
            RefundService::request($booking, $amount, $data['reason'], $data['note'] ?? null, (int) $this->adminId());
        } catch (\DomainException $e) {
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }

        return redirect()->back()->with('flash-message', __('backend::booking.refund_requested'));
    }

    public function decideRefund($refundId, Request $request)
    {
        $refundId = $request->route('refundId') ?? $refundId;
        $refund = PaymentRefund::findOrFail($refundId);
        $data = $request->validate(['decision' => 'required|in:approve,reject', 'note' => 'nullable|string|max:2000']);

        try {
            RefundService::decide($refund, $data['decision'] === 'approve', $data['note'] ?? null, (int) $this->adminId());
        } catch (\DomainException $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect()->back()->with('flash-message', __('backend::booking.refund_decided'));
    }

    public function processRefund($refundId, Request $request)
    {
        $refundId = $request->route('refundId') ?? $refundId;
        $refund = PaymentRefund::findOrFail($refundId);
        $data = $request->validate(['provider_reference' => 'required|string|max:191']);

        try {
            RefundService::markProcessed($refund, $data['provider_reference'], (int) $this->adminId());
        } catch (\DomainException $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect()->back()->with('flash-message', __('backend::booking.refund_processed'));
    }

    public function detailModal($id, Request $request)
    {
        $id = $request->route('id') ?? $id;
        $booking = Booking::with(['payment', 'cabins', 'amenities'])->find($id);
        if (!$booking) {
            return response()->json(['success' => false], 404);
        }
        $html = view($this->baseView . 'partials.detail-modal-content', compact('booking'))->render();
        $title = $booking->code;
        $journeyName = $booking->itinerary_name ?? '';
        $p = $booking->payment;
        $showConfirm = in_array($booking->status, [Booking::statusPending(), Booking::statusPaid()], true);
        return response()->json([
            'success' => true,
            'title' => $title,
            'journey_name' => $journeyName,
            'html' => $html,
            'id' => $booking->id,
            'status' => $booking->status,
            'show_confirm' => $showConfirm,
        ]);
    }

    public function confirm($id, Request $request)
    {
        $id = $request->route('id') ?? $id;
        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json(['success' => false, 'message' => __('backend::booking.not_found')]);
        }
        if ($booking->status === Booking::statusConfirmed()) {
            return response()->json(['success' => true, 'message' => __('backend::booking.msg_confirm_already')]);
        }
        if (!in_array($booking->status, [Booking::statusPending(), Booking::statusPaid()], true)) {
            return response()->json(['success' => false, 'message' => __('backend::booking.confirm_only_pending')]);
        }
        $booking->update(['status' => Booking::statusConfirmed()]);
        return response()->json(['success' => true, 'message' => __('backend::booking.msg_confirm_success')]);
    }

    public function cancel($id, Request $request)
    {
        $id = $request->route('id') ?? $id;
        $booking = Booking::find($id);
        if (!$booking) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('backend::booking.not_found')]);
            }
            return redirect()->back()->withErrors(__('backend::booking.not_found'));
        }
        if ($booking->status === Booking::statusCancelled()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('backend::booking.already_cancelled')]);
            }
            return redirect()->back()->withErrors(__('backend::booking.already_cancelled'));
        }
        $booking->update(['status' => Booking::statusCancelled()]);

        try {
            $customerEmail = $booking->email ? trim((string) $booking->email) : '';
            if ($customerEmail !== '') {
                Mail::send(new BookingCancellationMail($booking));
                Log::info('Booking cancellation email sent', ['booking_code' => $booking->code, 'email' => $customerEmail]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send booking cancellation email', [
                'booking_code' => $booking->code,
                'error' => $e->getMessage()
            ]);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => __('backend::booking.msg_cancel_success')]);
        }
        return redirect()->back()->with('flash-message', __('backend::booking.msg_cancel_success'));
    }

    public function destroy(Request $request)
    {
        $id = (int) $request->id;
        $booking = Booking::find($id);
        if (!$booking) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('backend::booking.not_found')]);
            }
            return redirect()->back()->withErrors(__('backend::booking.not_found'));
        }
        if ($booking->status !== Booking::statusCancelled()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('backend::booking.delete_only_cancelled')]);
            }
            return redirect()->back()->withErrors(__('backend::booking.delete_only_cancelled'));
        }

        // Money was received: keep the booking and its payments for the audit trail (TPX decision 5).
        if (BookingAudit::ready() && (int) $booking->amount_paid > 0) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => __('backend::booking.delete_paid_blocked')]);
            }
            return redirect()->back()->withErrors(__('backend::booking.delete_paid_blocked'));
        }

        DB::beginTransaction();
        try {
            $booking->cabins()->delete();
            $booking->amenities()->delete();
            if ($booking->payment_id) {
                PaymentCustomer::where('payment_id', $booking->payment_id)->delete();
                Payment::where('id', $booking->payment_id)->delete();
            }
            $booking->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
            return redirect()->back()->withErrors(__('backend::booking.msg_delete_error'));
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => __('backend::booking.msg_delete_success')]);
        }
        return redirect(route('backend.booking.index'))->with('flash-message', __('backend::booking.msg_delete_success'));
    }
}
