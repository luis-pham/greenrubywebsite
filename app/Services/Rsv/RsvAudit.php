<?php

namespace App\Services\Rsv;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** Access log of the reservations desk: who viewed, changed or exported guest data. */
class RsvAudit
{
    public static function log(string $action, ?string $type = null, ?int $id = null, array $detail = [], ?int $userId = null): void
    {
        DB::table('rsv_audit_log')->insert([
            'user_id' => $userId ?? (Auth::guard('admin')->id() ?: null),
            'action' => $action,
            'item_type' => $type,
            'item_id' => $id,
            'detail' => $detail ? json_encode($detail, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
