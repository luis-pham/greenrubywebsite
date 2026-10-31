<?php

namespace App\Services\Content;

use App\Observers\PublishGuardObserver;
use App\Services\Publication\PublicationPolicy;
use App\Support\FrontendRevalidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\BackEnd\Entities\AdUser;

/**
 * Editorial workflow of a translation (phase 1E-d, hardened). Three separate dimensions:
 *   workflow     draft → translation_review → seo_review → [legal_review] → approved (translation_meta, or the
 *                open content_revision of a published row)
 *   publication  publish / unpublish: the row's own flag, only from 'approved' (PublishGuardObserver re-checks)
 *   freshness    computed (Freshness); never changes either of the others
 *
 * Authorization (1E hardening): every action needs its own privilege (ACTION_PRIVILEGES; gates "translation-*" from
 * ad_privilege, assigned through roles). Self-approval policy (config i18n.workflow.allow_self_approval, default
 * false): an approval step (translation, SEO, legal) can never be taken by the person who last wrote the text under
 * review (translation_meta.translated_by, or the revision's last editor). Publishing needs its own privilege and an
 * approved state; the publisher may be the translator. Every action is audited (editorial_audit_log).
 * A null actor is the operator's CLI (trusted, recorded as null). en/vi rows have no workflow.
 */
final class Workflow
{
    public const STEPS = ['draft', 'translation_review', 'seo_review', 'legal_review', 'approved'];

    /** Types whose public text has legal weight: approval needs the legal step. */
    public const LEGAL_TYPES = PublishGuardObserver::LEGAL_TYPES;

    /** action => [resource, privilege]. 'reject' takes the privilege of the review step it rejects. */
    public const ACTION_PRIVILEGES = [
        'submit' => ['translation', 'edit'],
        'approve_translation' => ['translation', 'approve'],
        'approve_seo' => ['translation', 'seo-approve'],
        'approve_legal' => ['translation', 'legal-approve'],
        'publish' => ['translation', 'publish'],
        'unpublish' => ['translation', 'publish'],
        'discard_revision' => ['translation', 'edit'],
    ];
    private const REJECT_BY_STEP = ['translation_review' => 'approve', 'seo_review' => 'seo-approve', 'legal_review' => 'legal-approve'];
    private const APPROVALS = ['approve_translation', 'approve_seo', 'approve_legal'];

    /** @return array<int, array{key: string, label: string, style: string, allowed?: bool, reason?: string}> */
    public static function actionsFor(string $type, int $id, ?int $actor = null, bool $checkActor = false): array
    {
        $state = self::state($type, $id);
        if (!$state) {
            return [];
        }
        $actions = [];
        $step = $state['step'];
        $map = [
            'draft' => [['submit', 'Gửi duyệt bản dịch', 'outline-primary']],
            'translation_review' => [['approve_translation', 'Duyệt bản dịch', 'outline-success'], ['reject', 'Trả về nháp', 'outline-danger']],
            'seo_review' => [['approve_seo', 'Duyệt SEO', 'outline-success'], ['reject', 'Trả về nháp', 'outline-danger']],
            'legal_review' => [['approve_legal', 'Duyệt pháp lý', 'outline-success'], ['reject', 'Trả về nháp', 'outline-danger']],
            'approved' => [],
        ];
        foreach ($map[$step] ?? [] as [$key, $label, $style]) {
            $actions[] = compact('key', 'label', 'style');
        }
        if ($state['revision']) {
            $actions[] = ['key' => 'discard_revision', 'label' => 'Huỷ bản sửa (giữ bản đang hiển thị)', 'style' => 'outline-secondary'];
        } else {
            if (!$state['published'] && $step === 'approved') {
                $actions[] = ['key' => 'publish', 'label' => 'Xuất bản', 'style' => 'success'];
            }
            if ($state['published']) {
                $actions[] = ['key' => 'unpublish', 'label' => 'Gỡ xuất bản', 'style' => 'outline-danger'];
            }
        }
        if ($checkActor) {
            foreach ($actions as $i => $action) {
                $reason = self::refusal($type, $id, $action['key'], $actor, $state);
                $actions[$i]['allowed'] = $reason === null;
                $actions[$i]['reason'] = $reason;
            }
        }

        return $actions;
    }

    /** Why $actor may not take $action now (privilege, self-approval, lock), or null. A null actor is the CLI. */
    public static function refusal(string $type, int $id, string $action, ?int $actor, ?array $state = null): ?string
    {
        if ($actor === null) {
            return null;
        }
        $state ??= self::state($type, $id);
        [$resource, $privilege] = $action === 'reject'
            ? ['translation', self::REJECT_BY_STEP[$state['step'] ?? ''] ?? 'approve']
            : (self::ACTION_PRIVILEGES[$action] ?? [null, null]);
        $user = AdUser::find($actor);
        if (!$resource || !$user || !$user->hasAccess($resource, $privilege)) {
            return "Bạn chưa có quyền {$resource}-{$privilege} cho thao tác này.";
        }
        if (in_array($action, self::APPROVALS, true) && !config('i18n.workflow.allow_self_approval', false)) {
            $author = $state['revision'] ? RevisionStore::owner($state['revision']) : self::translator($type, $id);
            if ($author !== null && $author === $actor) {
                return 'Không tự duyệt nội dung mình vừa viết: cần một người duyệt khác.';
            }
        }
        if ($action === 'discard_revision' && $state['revision']) {
            $owner = RevisionStore::owner($state['revision']);
            if ($owner !== null && $owner !== $actor && !$user->hasAccess('translation', 'approve')) {
                return 'Chỉ người soạn bản sửa hoặc người duyệt mới huỷ được.';
            }
        }

        return null;
    }

    /**
     * Applies one action. @return string the new step or publication state
     * @throws \RuntimeException when the action is not available now or not allowed for $actor
     */
    public static function apply(string $type, int $id, string $action, ?int $actor = null): string
    {
        $state = self::state($type, $id);
        if (!$state) {
            throw new \RuntimeException("{$type} #{$id} is not under editorial workflow (en/vi rows are edited directly).");
        }
        if (!in_array($action, array_column(self::actionsFor($type, $id), 'key'), true)) {
            throw new \RuntimeException("Action '{$action}' is not available at step '{$state['step']}'.");
        }
        if ($refusal = self::refusal($type, $id, $action, $actor, $state)) {
            throw new \RuntimeException($refusal);
        }
        $now = now();
        $result = DB::transaction(function () use ($type, $id, $action, $actor, $state, $now) {
            $revision = $state['revision'] ? RevisionStore::open($type, $id, true) : null;
            if ($action === 'publish' || $action === 'unpublish') {
                self::setPublished($type, $id, $action === 'publish');

                return $action === 'publish' ? 'published' : 'unpublished';
            }
            if ($action === 'discard_revision') {
                // The public version never changed: dropping the revision leaves it exactly as approved.
                DB::table('content_revision')->where('id', $revision->id)->update(['discarded_at' => $now, 'discarded_by' => $actor, 'updated_at' => $now]);

                return 'revision_discarded';
            }
            $next = match ($action) {
                'submit' => 'translation_review',
                'approve_translation' => 'seo_review',
                'approve_seo' => in_array($type, self::LEGAL_TYPES, true) ? 'legal_review' : 'approved',
                'approve_legal' => 'approved',
                'reject' => 'draft',
            };
            $stamps = match ($action) {
                'approve_translation' => ['reviewed_by' => $actor, 'reviewed_at' => $now],
                'approve_seo' => ['seo_reviewed_by' => $actor, 'seo_reviewed_at' => $now],
                'approve_legal' => ['legal_approved_by' => $actor, 'legal_approved_at' => $now],
                default => [],
            };
            if ($revision) {
                DB::table('content_revision')->where('id', $revision->id)->update(['workflow' => $next, 'updated_at' => $now]);
                if ($stamps !== []) {
                    DB::table('translation_meta')->where('entity_type', $type)->where('entity_id', $id)->update($stamps + ['updated_at' => $now]);
                }
                if ($next === 'approved') {
                    self::applyRevision($type, $id, $revision, $actor);

                    return 'revision_applied';
                }

                return $next;
            }
            DB::table('translation_meta')->where('entity_type', $type)->where('entity_id', $id)->update(['workflow' => $next, 'updated_at' => $now] + $stamps);

            return $next;
        });
        EditorialAudit::record($type, $id, $action, $state['published'] ? 'published:' . $state['step'] : $state['step'], $result, $actor, $state['revision']->id ?? null);
        Log::notice('Translation workflow', ['type' => $type, 'id' => $id, 'action' => $action, 'result' => $result, 'actor' => $actor]);
        if (in_array($result, ['published', 'unpublished', 'revision_applied'], true)) {
            // Expire at once: every page that lists or links the row (hreflang, switcher, sitemaps, lists).
            FrontendRevalidator::send(['content'], true);
        }

        return $result;
    }

    /** null for legacy rows; else the step (of the open revision when the row is published and has one). */
    public static function state(string $type, int $id): ?array
    {
        if (!TranslatableFields::has($type) && $type !== 'page') {
            return null;
        }
        $meta = DB::table('translation_meta')->where('entity_type', $type)->where('entity_id', $id)->first();
        if (!$meta) {
            return null;
        }
        $table = TranslatableFields::has($type) ? TranslatableFields::of($type)['table'] : 'app_page';
        $row = DB::table($table)->where('id', $id)->first();
        if (!$row) {
            return null;
        }
        $published = $type === 'page' ? (int) $row->is_visible === 1 : TranslationMatrix::published($type, $row);
        $revision = $published ? RevisionStore::open($type, $id) : null;

        return ['published' => $published, 'revision' => $revision, 'step' => $revision->workflow ?? $meta->workflow];
    }

    /** The open revision (kept for callers of 1E-d). */
    public static function pendingRevision(string $type, int $id): ?object
    {
        return RevisionStore::open($type, $id);
    }

    /** Who last wrote the row's text (null: unknown or the CLI). */
    private static function translator(string $type, int $id): ?int
    {
        $by = DB::table('translation_meta')->where('entity_type', $type)->where('entity_id', $id)->value('translated_by');

        return $by === null ? null : (int) $by;
    }

    /** Writes the revision's fields and child text into the live rows, in the caller's transaction. */
    private static function applyRevision(string $type, int $id, object $revision, ?int $actor): void
    {
        $table = TranslatableFields::of($type)['table'];
        $allowed = array_merge(TranslatableFields::of($type)['text'], TranslatableFields::of($type)['copy']);
        $data = (array) json_decode($revision->fields, true);
        $fields = array_intersect_key($data, array_flip($allowed));
        if ($fields !== []) {
            DB::table($table)->where('id', $id)->update($fields + (DB::getSchemaBuilder()->hasColumn($table, 'updated_at') ? ['updated_at' => now()] : []));
        }
        ChildRevisions::apply($type, $id, (array) ($data['_children'] ?? []));
        DB::table('content_revision')->where('id', $revision->id)->update(['applied_at' => now(), 'applied_by' => $actor]);
    }

    /** Sets the row's own publication flag through its model, so PublishGuardObserver re-checks approval and slug. */
    private static function setPublished(string $type, int $id, bool $on): void
    {
        if ($type === 'service') {
            DB::table('app_service')->where('id', $id)->update(['status' => $on ? 1 : 0]);

            return;
        }
        $class = array_search($type, PublishGuardObserver::MODELS, true);
        if ($class === false) {
            throw new \RuntimeException("{$type} has no publication flag.");
        }
        $model = $class::withoutGlobalScopes()->findOrFail($id);
        $flag = PublicationPolicy::TYPES[$type]['flag'];
        $model->setAttribute($flag, match ($flag) {
            'publish_state' => $on ? 'published' : 'unpublished',
            'is_published' => $on ? 1 : 0,
            'status' => $on ? 'published' : 'draft',
            'is_visible' => $on ? 1 : 0,
        });
        if ($on && $flag === 'is_published' && empty($model->getAttribute('publish_date'))) {
            $model->setAttribute('publish_date', now());
        }
        $model->save();
    }
}
