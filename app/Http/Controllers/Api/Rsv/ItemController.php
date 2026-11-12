<?php

namespace App\Http\Controllers\Api\Rsv;

use App\Http\Middleware\RsvAuth;
use App\Services\Rsv\RsvAudit;
use App\Services\Rsv\RsvInbox;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** One enquiry: a contact message or a booking, with the desk's status, notes and the guest's other enquiries. */
class ItemController extends Controller
{
    public function contact(int $id)
    {
        $row = DB::table('contact_messages')->find($id);
        abort_unless($row, 404);
        RsvAudit::log('view', 'contact', $id);

        return response()->json([
            'item' => $row,
            'state' => RsvInbox::state('contact', $id),
            'notes' => RsvInbox::notes('contact', $id),
            'related' => RsvInbox::related($row->email, $row->phone, 'contact', $id),
        ]);
    }

    public function booking(int $id)
    {
        $b = DB::table('bookings')->find($id);
        abort_unless($b, 404);
        RsvAudit::log('view', 'booking', $id);

        $payments = DB::table('payments')->where('booking_id', $id)->orWhere('id', $b->payment_id)->orderBy('id')
            ->get(['id', 'gateway', 'purpose', 'amount', 'currency', 'status', 'error_code', 'error_message', 'completed_at', 'created_at', 'internal_tx_id']);
        $customer = DB::table('payment_customers')->whereIn('payment_id', $payments->pluck('id'))->orderByDesc('id')
            ->first(['full_name', 'email', 'phone', 'country', 'city', 'address_line1', 'address_line2', 'postal_code', 'nationality']);
        $first = $payments->firstWhere('id', $b->payment_id);

        return response()->json([
            'item' => $b,
            'kind' => $first && $first->gateway === 'cash' && str_starts_with((string) $first->internal_tx_id, 'inquiry_') ? 'inquiry' : 'booking',
            'cabins' => DB::table('booking_cabins')->where('booking_id', $id)->get(),
            'amenities' => DB::table('booking_amenities')->where('booking_id', $id)->get(['amenity_name', 'unit_price', 'quantity', 'total_price']),
            'payments' => $payments->map(fn ($p) => collect($p)->except('internal_tx_id')),
            'refunds' => DB::table('payment_refunds')->where('booking_id', $id)->orderBy('id')
                ->get(['amount', 'currency', 'reason', 'status', 'requested_at', 'decided_at', 'processed_at']),
            'customer' => $customer,
            'state' => RsvInbox::state('booking', $id),
            'notes' => RsvInbox::notes('booking', $id),
            'related' => RsvInbox::related($b->email, $b->phone, 'booking', $id),
        ]);
    }

    public function update(Request $request, string $type, int $id)
    {
        abort_unless(in_array($type, ['contact', 'booking'], true), 404);
        abort_unless(DB::table($type === 'contact' ? 'contact_messages' : 'bookings')->where('id', $id)->exists(), 404);
        $data = $request->validate([
            'status' => 'sometimes|in:' . implode(',', config('rsv.statuses')),
            'assigned_to' => 'sometimes|nullable|integer|in:' . implode(',', RsvAuth::allowedUserIds() ?: [0]),
            'follow_up_at' => 'sometimes|nullable|date_format:Y-m-d',
        ]);
        abort_if(!$data, 422, 'Nothing to update');

        $userId = (int) Auth::guard('admin')->id();
        $before = RsvInbox::state($type, $id);
        DB::table('rsv_states')->updateOrInsert(
            ['item_type' => $type, 'item_id' => $id],
            $data + ['updated_by' => $userId, 'updated_at' => now()] + ($before ? [] : ['created_at' => now()])
        );
        RsvAudit::log('update', $type, $id, $data);

        return response()->json(['state' => RsvInbox::state($type, $id)]);
    }

    public function note(Request $request, string $type, int $id)
    {
        abort_unless(in_array($type, ['contact', 'booking'], true), 404);
        abort_unless(DB::table($type === 'contact' ? 'contact_messages' : 'bookings')->where('id', $id)->exists(), 404);
        $data = $request->validate(['body' => 'required|string|max:5000']);
        DB::table('rsv_notes')->insert([
            'item_type' => $type, 'item_id' => $id, 'user_id' => (int) Auth::guard('admin')->id(),
            'body' => trim($data['body']), 'created_at' => now(),
        ]);
        RsvAudit::log('note', $type, $id);

        return response()->json(['notes' => RsvInbox::notes($type, $id)], 201);
    }
}
