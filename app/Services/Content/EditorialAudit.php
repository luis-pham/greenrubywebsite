<?php

namespace App\Services\Content;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Actor, action and time of every editorial decision (workflow steps, slug changes, locale status; 1E hardening). */
final class EditorialAudit
{
    public static function record(string $entityType, int $entityId, string $action, ?string $from, ?string $to, ?int $actor, ?int $revisionId = null, ?string $detail = null): void
    {
        if (!Schema::hasTable('editorial_audit_log')) {
            return; // before the 2026_11_05 migration
        }
        // from_state/to_state are 32 characters wide; a longer value (a slug) is kept in full in the detail.
        $long = array_filter(['from' => $from, 'to' => $to], fn ($v) => $v !== null && mb_strlen($v) > 32);
        if ($long !== []) {
            $detail = trim(implode(' ', array_map(fn ($k, $v) => "{$k}={$v}", array_keys($long), $long)) . ' ' . ($detail ?? ''));
            $from = $from === null ? null : mb_substr($from, 0, 32);
            $to = $to === null ? null : mb_substr($to, 0, 32);
        }
        DB::table('editorial_audit_log')->insert([
            'entity_type' => $entityType, 'entity_id' => $entityId, 'action' => $action, 'from_state' => $from, 'to_state' => $to,
            'revision_id' => $revisionId, 'actor' => $actor, 'detail' => $detail === null ? null : mb_substr($detail, 0, 255), 'created_at' => now(),
        ]);
    }
}
