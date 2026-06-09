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

        return view($this->baseView . 'show', compact('title', 'booking'));
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
