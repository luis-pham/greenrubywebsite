<?php

namespace App\Services\Content;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * The open revision of a published translation (12 §2.5; 1E hardening). One open revision per entity, held by one
 * editor at a time: another editor's edit is refused (never a silent overwrite) until the revision is approved
 * (applied) or discarded. Editing a revision that is in review sends it back to draft: earlier approvals were for
 * other text. Parent fields and child text rows (`_children`: table => [row id => [column => value]]) travel together.
 */
final class RevisionStore
{
    /** The open (not applied, not discarded) revision, or null. */
    public static function open(string $type, int $id, bool $lock = false): ?object
    {
        if (!DB::getSchemaBuilder()->hasTable('content_revision')) {
            return null;
        }
        $q = DB::table('content_revision')->where('entity_type', $type)->where('entity_id', $id)->whereNull('applied_at');
        if (DB::getSchemaBuilder()->hasColumn('content_revision', 'discarded_at')) {
            $q->whereNull('discarded_at');
        }
        if ($lock) {
            $q->lockForUpdate();
        }

        return $q->orderByDesc('id')->first();
    }

    /** Who holds the open revision (its last editor). */
    public static function owner(object $revision): ?int
    {
        $owner = $revision->updated_by ?? $revision->created_by ?? null;

        return $owner === null ? null : (int) $owner;
    }

    /** Merges changes into the open revision (or opens one). @return int revision id */
    public static function merge(string $type, int $id, array $fields, array $children, ?int $actor): int
    {
        return DB::transaction(function () use ($type, $id, $fields, $children, $actor) {
            $now = now();
            $open = self::open($type, $id, true);
            if ($open) {
                $owner = self::owner($open);
                if ($owner !== null && $owner !== $actor) {
                    throw new \App\Services\Content\EditorialRefusal("Bản sửa #{$open->id} đang do người dùng #{$owner} soạn: duyệt hoặc huỷ bản sửa đó trước. Thay đổi của bạn chưa được lưu (không ghi đè).");
                }
                $data = (array) json_decode($open->fields, true);
                foreach ($children as $table => $rows) {
                    foreach ($rows as $rowId => $values) {
                        $data['_children'][$table][$rowId] = array_merge($data['_children'][$table][$rowId] ?? [], $values);
                    }
                }
                $data = array_merge($data, $fields);
                $update = ['fields' => json_encode($data, JSON_UNESCAPED_UNICODE), 'updated_at' => $now, 'updated_by' => $actor];
                if ($open->workflow !== 'draft') {
                    $update['workflow'] = 'draft';
                    EditorialAudit::record($type, $id, 'revision_reopened', $open->workflow, 'draft', $actor, (int) $open->id, 'edited during review');
                }
                DB::table('content_revision')->where('id', $open->id)->update($update);

                return (int) $open->id;
            }
            $data = $fields + ($children ? ['_children' => $children] : []);
            $revisionId = (int) DB::table('content_revision')->insertGetId([
                'entity_type' => $type, 'entity_id' => $id, 'fields' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'workflow' => 'draft', 'created_by' => $actor, 'updated_by' => $actor, 'created_at' => $now, 'updated_at' => $now,
            ]);
            EditorialAudit::record($type, $id, 'revision_opened', null, 'draft', $actor, $revisionId);

            return $revisionId;
        });
    }
}
