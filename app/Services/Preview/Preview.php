<?php

namespace App\Services\Preview;

use App\Services\Content\PathBuilder;
use App\Services\Content\TranslatableFields;
use App\Services\Content\Workflow;
use App\Services\Locale\LocaleRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Authenticated preview (phase 1E-d; 12 §8, amendment A-7). An editor's "Preview" issues a single-use token (stored
 * as a sha256 hash, 10 minutes) scoped to one entity, its language and its pending revision. The website's
 * /_draft/enter verifies it server-to-server (POST /api/v1/content/preview/verify, X-Preview-Key) and receives a
 * preview session (hash stored, 30 minutes, same scope) kept in an httpOnly cookie. Content API reads carrying a
 * valid session preview exactly that entity: its own unpublished flag and its language's non-public status are
 * ignored for it, its pending revision is shown; parents, other drafts and other languages stay public-only.
 * Tokens and session ids are never logged, never put in a URL and never in a cacheable response.
 */
final class Preview
{
    public const TOKEN_TTL = 600;
    public const SESSION_TTL = 1800;
    /**
     * Types the website can preview: detail pages with a stored-slug URL, FAQ items (on their FAQ page) and pages
     * (static pages and hubs: page-level publication; 1E hardening).
     */
    public const TYPES = ['cruise', 'cabin', 'itinerary', 'experience', 'destination', 'article', 'faq', 'page'];

    /** app_page code => website section (null = home). */
    public const PAGE_SECTIONS = [
        'service' => 'services', 'gallery' => 'gallery', 'about-us' => 'aboutUs', 'contact' => 'contact',
        'destinations' => 'destinations', 'homepage' => null, 'faq' => 'faq',
    ];

    public static function tableOf(string $type): string
    {
        return $type === 'page' ? 'app_page' : TranslatableFields::of($type)['table'];
    }
    private const SCOPE = 'preview.scope';

    /** @return string the raw token (shown once, to the editor's browser) */
    public static function issue(int $userId, string $type, int $id): string
    {
        if (!in_array($type, self::TYPES, true)) {
            throw new \RuntimeException("Chưa xem trước được loại {$type}.");
        }
        $row = DB::table(self::tableOf($type))->where('id', $id)->first();
        if (!$row) {
            throw new \RuntimeException("{$type} #{$id} không tồn tại.");
        }
        if ($type === 'page' && !array_key_exists((string) $row->code, self::PAGE_SECTIONS)) {
            throw new \RuntimeException("Trang {$row->code} chưa xem trước được.");
        }
        $language = LocaleRegistry::byId((int) $row->language_id);
        if (!$language || !LocaleRegistry::isEditable($language)) {
            throw new \RuntimeException('Ngôn ngữ này không biên tập được.');
        }
        if (property_exists($row, 'slug') && trim((string) $row->slug) === '') {
            throw new \RuntimeException('Đặt slug (URL) trước khi xem trước.');
        }
        $token = bin2hex(random_bytes(32));
        DB::table('content_preview_token')->insert([
            'token_hash' => hash('sha256', $token), 'user_id' => $userId, 'entity_type' => $type, 'entity_id' => $id,
            'language_id' => (int) $row->language_id, 'revision_id' => Workflow::pendingRevision($type, $id)?->id,
            'expires_at' => now()->addSeconds(self::TOKEN_TTL), 'created_at' => now(),
        ]);

        return $token;
    }

    /**
     * Consumes a token (single use) and opens a preview session.
     * @return array{session: string, type: string, id: int, lang: string, path: ?string, expiresAt: string}
     * @throws \RuntimeException invalid, expired or replayed token
     */
    public static function verify(string $token): array
    {
        $hash = hash('sha256', $token);
        $session = bin2hex(random_bytes(32));

        return DB::transaction(function () use ($hash, $session) {
            $row = DB::table('content_preview_token')->where('token_hash', $hash)->lockForUpdate()->first();
            if (!$row) {
                throw new \RuntimeException('preview_token_invalid');
            }
            // Single use: a replayed token is refused even within its lifetime.
            $used = DB::table('content_preview_token')->where('id', $row->id)->whereNull('used_at')->update(['used_at' => now()]);
            if ($used !== 1) {
                throw new \RuntimeException('preview_token_used');
            }
            if (now()->greaterThan($row->expires_at)) {
                throw new \RuntimeException('preview_token_expired');
            }
            $expires = now()->addSeconds(self::SESSION_TTL);
            DB::table('content_preview_session')->insert([
                'session_hash' => hash('sha256', $session), 'token_id' => $row->id, 'user_id' => $row->user_id,
                'entity_type' => $row->entity_type, 'entity_id' => $row->entity_id, 'language_id' => $row->language_id,
                'revision_id' => $row->revision_id, 'expires_at' => $expires, 'created_at' => now(),
            ]);
            $language = LocaleRegistry::byId((int) $row->language_id);

            return [
                'session' => $session, 'type' => $row->entity_type, 'id' => (int) $row->entity_id,
                'lang' => (string) $language?->code, 'path' => self::path($row->entity_type, (int) $row->entity_id),
                'expiresAt' => $expires->toIso8601String(),
            ];
        });
    }

    /** The scope of a valid (not expired, not revoked) session, or null. */
    public static function session(string $session): ?object
    {
        $row = DB::table('content_preview_session')->where('session_hash', hash('sha256', $session))->first();
        if (!$row || $row->revoked_at || now()->greaterThan($row->expires_at)) {
            return null;
        }
        $row->table = self::tableOf($row->entity_type);

        return $row;
    }

    public static function enter(object $scope): void
    {
        app()->instance(self::SCOPE, $scope);
    }

    public static function leave(): void
    {
        app()->forgetInstance(self::SCOPE);
    }

    public static function active(): ?object
    {
        return app()->bound(self::SCOPE) ? app(self::SCOPE) : null;
    }

    /** True when the current read previews this row (table + id). */
    public static function allows(string $table, $id): bool
    {
        $scope = self::active();

        return $scope && $scope->table === $table && (int) $scope->entity_id === (int) $id;
    }

    /** The previewed language id, or null. */
    public static function languageId(): ?int
    {
        return ($scope = self::active()) ? (int) $scope->language_id : null;
    }

    /** Shows the scope's pending revision on the previewed row (model retrieved event; nothing is saved). */
    public static function overlay(Model $model): void
    {
        $scope = self::active();
        if (!$scope || !$scope->revision_id || !self::allows($model->getTable(), $model->getKey())) {
            return;
        }
        foreach (self::revisionFields($scope) as $column => $value) {
            if ($column !== '_children') {
                $model->setAttribute($column, $value);
            }
        }
        $model->syncOriginal();
    }

    /** Shows the pending revision's text on the previewed entity's child rows (cabin rooms, programme lines). */
    public static function overlayChild(Model $model): void
    {
        $scope = self::active();
        if (!$scope || !$scope->revision_id) {
            return;
        }
        $values = self::revisionFields($scope)['_children'][$model->getTable()][$model->getKey()] ?? null;
        foreach ((array) $values as $column => $value) {
            if (in_array($column, ['title', 'description'], true)) {
                $model->setAttribute($column, $value);
            }
        }
        $model->syncOriginal();
    }

    /** The scope's revision fields while it is still open (not applied, not discarded). */
    private static function revisionFields(object $scope): array
    {
        $q = DB::table('content_revision')->where('id', $scope->revision_id)->whereNull('applied_at');
        if (DB::getSchemaBuilder()->hasColumn('content_revision', 'discarded_at')) {
            $q->whereNull('discarded_at');
        }

        return (array) json_decode((string) $q->value('fields'), true);
    }

    /** Website path of the previewed entity (PathBuilder), or null when it has none yet. */
    public static function path(string $type, int $id): ?string
    {
        $row = DB::table(self::tableOf($type))->where('id', $id)->first();
        $language = $row ? LocaleRegistry::byId((int) $row->language_id) : null;
        if (!$row || !$language) {
            return null;
        }
        try {
            return match ($type) {
                // An FAQ item is shown on its group's FAQ page (or the FAQ hub without a group slug).
                'faq' => ($group = DB::table('app_group')->where('id', $row->group_id)->value('slug'))
                    ? PathBuilder::faqGroup($language, (string) $group) : PathBuilder::section($language, 'faq'),
                'page' => self::PAGE_SECTIONS[$row->code] === null ? PathBuilder::home($language) : PathBuilder::section($language, self::PAGE_SECTIONS[$row->code]),
                'article' => PathBuilder::article($language, (string) DB::table('app_category')->where('id', $row->category_id)->value('slug'), (string) $row->slug),
                'itinerary' => PathBuilder::itinerary($language, (string) $row->slug, (int) DB::table('app_cruise_itinerary')->where('itinerary_id', $id)->value('cruise_id'), $id),
                'destination' => PathBuilder::destination($language, (string) $row->slug),
                'cruise' => PathBuilder::cruise($language, (string) $row->slug, $id),
                'cabin' => PathBuilder::cabin($language, (string) $row->slug, $id),
                'experience' => PathBuilder::experience($language, (string) $row->slug, $id),
            };
        } catch (\Throwable $e) {
            return null;
        }
    }
}
