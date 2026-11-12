<?php

namespace App\Http\Controllers\Api\Rsv;

use App\Services\Rsv\RsvAudit;
use App\Services\Rsv\RsvInbox;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InboxController extends Controller
{
    private function filters(Request $request): array
    {
        return $request->validate([
            'kind' => 'nullable|in:contact,inquiry,booking',
            'status' => 'nullable|string|max:16',
            'assigned' => 'nullable|in:me,none',
            'follow' => 'nullable|in:due',
            'from' => 'nullable|date_format:Y-m-d',
            'to' => 'nullable|date_format:Y-m-d',
            'locale' => 'nullable|string|max:16',
            'q' => 'nullable|string|max:120',
            'page' => 'nullable|integer|min:1',
        ]);
    }

    public function index(Request $request)
    {
        $f = $this->filters($request);
        $page = $f['page'] ?? 1;
        $q = RsvInbox::filtered($f, (int) Auth::guard('admin')->id());
        $total = (clone $q)->count();
        $items = $q->orderByDesc('created_at')->orderByDesc('id')->forPage($page, 30)->get();

        $notes = DB::table('rsv_notes')->selectRaw('item_type, item_id, COUNT(*) n')->groupBy('item_type', 'item_id')->get()
            ->keyBy(fn ($r) => $r->item_type . ':' . $r->item_id);
        foreach ($items as $item) {
            $item->notes = (int) ($notes[$item->item_type . ':' . $item->id]->n ?? 0);
        }

        return response()->json(['items' => $items, 'total' => $total, 'page' => $page, 'perPage' => 30]);
    }

    public function stats()
    {
        $userId = (int) Auth::guard('admin')->id();
        $all = RsvInbox::union();
        $byStatus = (clone $all)->selectRaw('status, COUNT(*) n')->groupBy('status')->pluck('n', 'status');
        $byKind = (clone $all)->whereIn('status', RsvInbox::OPEN)->selectRaw('kind, COUNT(*) n')->groupBy('kind')->pluck('n', 'kind');
        $today = now()->toDateString();

        $revenue = DB::table('payments')->where('status', 'succeeded')->where('gateway', '!=', 'cash')
            ->where('completed_at', '>=', now()->subDays(30))
            ->selectRaw('LOWER(currency) currency, SUM(amount) total, COUNT(*) n')->groupBy(DB::raw('LOWER(currency)'))->get();

        return response()->json([
            'byStatus' => $byStatus,
            'openByKind' => $byKind,
            'new7d' => (clone $all)->where('created_at', '>=', now()->subDays(7))->count(),
            'followDue' => (clone $all)->whereIn('status', RsvInbox::OPEN)->whereNotNull('follow_up_at')->where('follow_up_at', '<=', $today)->count(),
            'mine' => (clone $all)->whereIn('status', RsvInbox::OPEN)->where('assigned_to', $userId)->count(),
            'unassigned' => (clone $all)->whereIn('status', RsvInbox::OPEN)->whereNull('assigned_to')->count(),
            'revenue30d' => $revenue,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $f = $this->filters($request);
        $rows = RsvInbox::filtered($f, (int) Auth::guard('admin')->id())->orderByDesc('created_at')->limit(5000)->get();
        RsvAudit::log('export', null, null, ['filters' => $f, 'rows' => $rows->count()]);

        $cols = ['created_at', 'kind', 'status', 'name', 'email', 'phone', 'locale', 'code', 'cruise', 'itinerary', 'departure_date',
            'guests', 'total', 'currency', 'booking_status', 'payment_status', 'attempt_status', 'gateway', 'excerpt', 'follow_up_at'];

        return response()->streamDownload(function () use ($rows, $cols) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Vietnamese correctly
            fputcsv($out, $cols);
            foreach ($rows as $r) {
                fputcsv($out, array_map(fn ($c) => self::cell($r->{$c} ?? ''), $cols));
            }
            fclose($out);
        }, 'rsv-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Neutralise spreadsheet formulas in guest-typed text. */
    private static function cell($v): string
    {
        $v = (string) $v;

        return $v !== '' && in_array($v[0], ['=', '+', '-', '@'], true) && !is_numeric($v) ? "'" . $v : $v;
    }
}
