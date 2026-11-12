<?php

namespace App\Services\Rsv;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * One list of everything guests sent through the website: contact-form messages and bookings (an
 * "inquiry" is a booking sent without online payment, payments.gateway = cash). The desk's own status
 * lives in rsv_states; without a row a contact is "new" and a booking with a successful online payment "won".
 */
class RsvInbox
{
    public const OPEN = ['new', 'contacted', 'quoted'];

    private const PAID = "('paid','deposit_paid','partially_refunded')";

    public static function union(): Builder
    {
        $contacts = DB::table('contact_messages as c')
            ->leftJoin('rsv_states as s', fn ($j) => $j->on('s.item_id', '=', 'c.id')->where('s.item_type', 'contact'))
            ->selectRaw("'contact' as item_type, 'contact' as kind, c.id, c.created_at, c.name, c.email, c.phone, c.locale,
                NULL as code, NULL as cruise, NULL as itinerary, NULL as departure_date, NULL as guests, NULL as total, NULL as currency,
                NULL as booking_status, NULL as payment_status, NULL as attempt_status, NULL as gateway,
                LEFT(c.message, 160) as excerpt, c.mail_status,
                COALESCE(s.status, 'new') as status, s.assigned_to, s.follow_up_at");

        $bookings = DB::table('bookings as b')
            ->leftJoin('payments as p', 'p.id', '=', 'b.payment_id')
            ->leftJoin('rsv_states as s', fn ($j) => $j->on('s.item_id', '=', 'b.id')->where('s.item_type', 'booking'))
            ->selectRaw("'booking' as item_type,
                CASE WHEN p.gateway = 'cash' AND p.internal_tx_id LIKE 'inquiry\\_%' THEN 'inquiry' ELSE 'booking' END as kind,
                b.id, b.created_at, b.full_name as name, b.email, b.phone, b.locale,
                b.code, b.cruise_name as cruise, b.itinerary_name as itinerary, b.departure_date, b.guests_total as guests,
                b.total_amount as total, b.currency, b.status as booking_status, b.payment_status, p.status as attempt_status, p.gateway,
                NULL as excerpt, NULL as mail_status,
                COALESCE(s.status, CASE WHEN b.payment_status IN " . self::PAID . " OR b.status IN ('paid','confirmed')
                    OR (p.status = 'succeeded' AND p.gateway <> 'cash') THEN 'won' ELSE 'new' END) as status,
                s.assigned_to, s.follow_up_at");

        return DB::query()->fromSub($contacts->unionAll($bookings), 'i');
    }

    /** @param array<string, mixed> $f filters from the request */
    public static function filtered(array $f, int $userId): Builder
    {
        $q = self::union();
        if (!empty($f['kind'])) {
            $q->where('kind', $f['kind']);
        }
        if (($f['status'] ?? '') === 'open') {
            $q->whereIn('status', self::OPEN);
        } elseif (!empty($f['status'])) {
            $q->where('status', $f['status']);
        }
        if (($f['assigned'] ?? '') === 'me') {
            $q->where('assigned_to', $userId);
        } elseif (($f['assigned'] ?? '') === 'none') {
            $q->whereNull('assigned_to');
        }
        if (($f['follow'] ?? '') === 'due') {
            $q->whereNotNull('follow_up_at')->where('follow_up_at', '<=', now()->toDateString())->whereIn('status', self::OPEN);
        }
        if (!empty($f['from'])) {
            $q->where('created_at', '>=', $f['from'] . ' 00:00:00');
        }
        if (!empty($f['to'])) {
            $q->where('created_at', '<=', $f['to'] . ' 23:59:59');
        }
        if (!empty($f['locale'])) {
            $q->where('locale', $f['locale']);
        }
        if (($term = trim((string) ($f['q'] ?? ''))) !== '') {
            $like = '%' . addcslashes($term, '%_\\') . '%';
            $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like)
                ->orWhere('phone', 'like', $like)->orWhere('code', 'like', $like));
        }

        return $q;
    }

    /** Other enquiries from the same guest (same e-mail, or same last 9 digits of the phone number). */
    public static function related(?string $email, ?string $phone, string $type, int $id): array
    {
        $email = $email ? mb_strtolower(trim($email)) : null;
        $digits = $phone ? substr(preg_replace('/\D/', '', $phone), -9) : null;
        if (!$email && (!$digits || strlen($digits) < 8)) {
            return [];
        }

        return self::union()
            ->where(function ($w) use ($email, $digits) {
                if ($email) {
                    $w->orWhereRaw('LOWER(email) = ?', [$email]);
                }
                if ($digits && strlen($digits) >= 8) {
                    $w->orWhereRaw("RIGHT(REGEXP_REPLACE(COALESCE(phone, ''), '[^0-9]', ''), 9) = ?", [$digits]);
                }
            })
            ->whereRaw('NOT (item_type = ? AND id = ?)', [$type, $id])
            ->orderByDesc('created_at')->limit(50)->get()->all();
    }

    public static function notes(string $type, int $id): array
    {
        return DB::table('rsv_notes as n')->leftJoin('ad_user as u', 'u.id', '=', 'n.user_id')
            ->where('n.item_type', $type)->where('n.item_id', $id)->orderByDesc('n.created_at')
            ->get(['n.id', 'n.body', 'n.created_at', 'n.user_id', DB::raw('COALESCE(u.fullname, u.username) as user_name')])->all();
    }

    public static function state(string $type, int $id): ?object
    {
        return DB::table('rsv_states')->where('item_type', $type)->where('item_id', $id)->first();
    }
}
