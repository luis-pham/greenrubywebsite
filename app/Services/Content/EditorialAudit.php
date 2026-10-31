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
        DB::table('editorial_audit_log')->insert([
            'entity_type' => $entityType, 'entity_id' => $entityId, 'action' => $action, 'from_state' => $from, 'to_state' => $to,
            'revision_id' => $revisionId, 'actor' => $actor, 'detail' => $detail === null ? null : mb_substr($detail, 0, 255), 'created_at' => now(),
        ]);
    }
}
