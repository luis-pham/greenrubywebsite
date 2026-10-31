<?php

namespace App\Services\Content;

use Illuminate\Support\Collection;
use Modules\BackEnd\Entities\AdLanguage;
use Modules\BackEnd\Entities\AppCabin;
use Modules\BackEnd\Entities\AppCategory;
use Modules\BackEnd\Entities\AppCruise;
use Modules\BackEnd\Entities\AppExpActivity;
use Modules\BackEnd\Entities\AppGroup;
use Modules\BackEnd\Entities\AppItinerary;
use Modules\FrontEnd\Helpers\FeHreflangUtils;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\Locale\LocaleRegistry;
use Illuminate\Support\Str;

/**
 * Explicit translation links between content rows. Every language is its own row with its own id, so
 * "which VI row is the translation of this EN row" is stored here: rows of one entity_type sharing a
 * group_key are translations of each other (at most one per language).
 *
 * A row that is linked is authoritative: its counterpart is whatever the group holds for the target
 * language, or none at all (a group of one = "explicitly has no translation"). Rows that are not linked
 * fall back to the old per-type heuristics (same image, same ord, ...) in FeHreflangUtils and the
 * Content API services.
 */
class ContentTranslations
{
    /** entity_type => source table, column used as the display name, admin label. */
    public const TYPES = [
        'cruise' => ['table' => 'app_cruise', 'name' => 'name', 'label' => 'Du thuyền'],
        'cabin' => ['table' => 'app_cabin', 'name' => 'name', 'label' => 'Cabin'],
        'itinerary' => ['table' => 'app_itinerary', 'name' => 'name', 'label' => 'Hành trình'],
        'experience' => ['table' => 'app_exp_activity', 'name' => 'name', 'label' => 'Hoạt động trải nghiệm'],
        'article' => ['table' => 'app_article', 'name' => 'title', 'label' => 'Tin tức'],
        'article_category' => ['table' => 'app_category', 'name' => 'name', 'label' => 'Chuyên mục tin tức'],
        'faq_group' => ['table' => 'app_group', 'name' => 'name', 'label' => 'Chuyên mục câu hỏi thường gặp'],
        'destination' => ['table' => 'app_destination', 'name' => 'name', 'label' => 'Điểm đến'],
    ];

    /** Per-request cache: type => [entity_id => ['group' => key, 'language_id' => id]]. */
    private static array $links = [];

    public static function isLinked(string $type, int $id): bool
    {
        return isset(self::links($type)[$id]);
    }

    /**
     * Id of the row linked to $id in $languageId ($id itself when it already is in that language), or null
     * when $id is not linked or its group has no row in that language. Callers check isLinked() first to
     * tell "no translation" from "not linked yet".
     */
    public static function linkedId(string $type, int $id, int $languageId): ?int
    {
        $links = self::links($type);
        $own = $links[$id] ?? null;
        if (!$own) {
            return null;
        }
        if ($own['language_id'] === $languageId) {
            return $id;
        }

        foreach ($links as $otherId => $link) {
            if ($link['group'] === $own['group'] && $link['language_id'] === $languageId) {
                return self::exists($type, $otherId, $languageId) ? $otherId : null;
            }
        }

        return null;
    }

    /**
     * [true, ?id] when $id is linked (the answer is authoritative, null = no translation in $languageId);
     * [false, null] when it is not, so the caller should use its heuristic.
     */
    public static function lookup(string $type, int $id, int $languageId): array
    {
        if (!self::isLinked($type, $id)) {
            return [false, null];
        }

        return [true, self::linkedId($type, $id, $languageId)];
    }

    /**
     * A heuristic guess is only usable when the guessed row is not linked: a linked row's counterpart is
     * fixed (possibly to "none"), so pairing it with something else would give one-way hreflang.
     */
    public static function unclaimed(string $type, $candidate)
    {
        $id = is_object($candidate) ? ($candidate->id ?? null) : $candidate;

        return $id && !self::isLinked($type, (int) $id) ? $candidate : null;
    }

    /**
     * Link the given rows (one per language) as translations of each other. A single id records
     * "this row has no translation". Rows previously linked elsewhere are moved; their old partners keep
     * their (now smaller) group, i.e. they become "no translation" until linked again.
     *
     * Phase 1C-4 (14 §5.3): unchanged for EN/VI, but transactional with row locks, logged
     * (content_translation_log 'regroup'), and refused when it would split off a member in a language
     * that is not among the linked rows' languages (a third language, e.g. ja): use attach()/detach().
     */
    public static function link(string $type, array $ids, ?int $actor = null, ?string $reason = null): void
    {
        $table = self::table($type);
        $ids = array_values(array_unique(array_map('intval', $ids)));
        $rows = self::scoped($type, DB::table($table))->whereIn("$table.id", $ids)->get(["$table.id", "$table.language_id"]);
        if ($ids === [] || $rows->count() !== count($ids)) {
            throw new \InvalidArgumentException("Unknown $type id in [" . implode(', ', $ids) . ']');
        }
        if ($rows->pluck('language_id')->unique()->count() !== $rows->count()) {
            throw new \InvalidArgumentException("Only one $type per language can be linked together");
        }

        DB::transaction(function () use ($type, $rows, $actor, $reason) {
            $ids = $rows->pluck('id')->map(fn ($id) => (int) $id)->all();
            $languages = $rows->pluck('language_id')->map(fn ($id) => (int) $id)->all();
            $current = DB::table('content_translations')->where('entity_type', $type)->whereIn('entity_id', $ids)->lockForUpdate()->get();
            $groups = $current->pluck('group_key')->unique()->values()->all();
            if ($groups !== []) {
                $left = DB::table('content_translations')->where('entity_type', $type)->whereIn('group_key', $groups)
                    ->whereNotIn('entity_id', $ids)->lockForUpdate()->get();
                $stranded = $left->first(fn ($m) => !in_array((int) $m->language_id, $languages, true));
                if ($stranded) {
                    throw new \RuntimeException("Refusing to regroup $type: #{$stranded->entity_id} (language {$stranded->language_id}) would be split from its group. Detach it first.");
                }
            }
            DB::table('content_translations')->where('entity_type', $type)->whereIn('entity_id', $ids)->delete();
            $group = (string) Str::uuid();
            DB::table('content_translations')->insert($rows->map(fn ($row) => [
                'entity_type' => $type,
                'entity_id' => (int) $row->id,
                'group_key' => $group,
                'language_id' => (int) $row->language_id,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all());
            $old = $current->keyBy('entity_id');
            foreach ($rows as $row) {
                self::log('regroup', $type, (int) $row->id, (int) $row->language_id, $old->get($row->id)?->group_key, $group, $actor, $reason ?? 'link()');
            }
        });
        self::flush();
    }

    /**
     * Forget the link of $id and of the rows linked with it: they all go back to the heuristic pairing
     * (dropping only one side would leave its partner pinned to "no translation").
     * Phase 1C-4: logged; refused for a group with a member in a third language (use detach()).
     */
    public static function unlink(string $type, int $id, ?int $actor = null, ?string $reason = null): void
    {
        self::table($type);
        DB::transaction(function () use ($type, $id, $actor, $reason) {
            $group = DB::table('content_translations')->where('entity_type', $type)->where('entity_id', $id)->lockForUpdate()->value('group_key');
            if (!$group) {
                return;
            }
            $members = DB::table('content_translations')->where('entity_type', $type)->where('group_key', $group)->lockForUpdate()->get();
            if ($members->contains(fn ($m) => !self::isLegacyLanguage((int) $m->language_id))) {
                throw new \RuntimeException("Refusing to unlink the whole $type group of #$id: it has a member in a third language. Use detach().");
            }
            DB::table('content_translations')->where('entity_type', $type)->where('group_key', $group)->delete();
            foreach ($members as $m) {
                self::log('detach', $type, (int) $m->entity_id, (int) $m->language_id, $group, null, $actor, $reason ?? 'unlink()');
            }
        });
        self::flush();
    }

    /**
     * Add $newId to the group of $existingId (14 §5.1). Never deletes and never touches other members.
     * Refuses when $existingId is not linked, $newId is already linked, or the group already has $newId's language.
     */
    public static function attach(string $type, int $existingId, int $newId, ?int $actor = null, ?string $reason = null): string
    {
        $table = self::table($type);
        $group = DB::transaction(function () use ($type, $table, $existingId, $newId, $actor, $reason) {
            $existing = DB::table('content_translations')->where('entity_type', $type)->where('entity_id', $existingId)->lockForUpdate()->first();
            if (!$existing) {
                throw new \RuntimeException("$type #$existingId is not linked; link() it first.");
            }
            if (DB::table('content_translations')->where('entity_type', $type)->where('entity_id', $newId)->lockForUpdate()->exists()) {
                throw new \RuntimeException("$type #$newId is already in a group; detach it first.");
            }
            $row = self::scoped($type, DB::table($table))->where("$table.id", $newId)->first(["$table.id", "$table.language_id"]);
            if (!$row) {
                throw new \InvalidArgumentException("Unknown $type id $newId");
            }
            if (DB::table('content_translations')->where('entity_type', $type)->where('group_key', $existing->group_key)
                ->where('language_id', $row->language_id)->exists()) {
                throw new \RuntimeException("The group of $type #$existingId already has a row in language {$row->language_id}.");
            }
            DB::table('content_translations')->insert([
                'entity_type' => $type, 'entity_id' => $newId, 'group_key' => $existing->group_key,
                'language_id' => (int) $row->language_id, 'created_at' => now(), 'updated_at' => now(),
            ]);
            self::log('attach', $type, $newId, (int) $row->language_id, null, $existing->group_key, $actor, $reason);

            return $existing->group_key;
        });
        self::flush();

        return $group;
    }

    /** Remove only $id from its group (14 §5.2); the other members keep their group. */
    public static function detach(string $type, int $id, ?int $actor = null, ?string $reason = null): void
    {
        self::table($type);
        DB::transaction(function () use ($type, $id, $actor, $reason) {
            $link = DB::table('content_translations')->where('entity_type', $type)->where('entity_id', $id)->lockForUpdate()->first();
            if (!$link) {
                return;
            }
            DB::table('content_translations')->where('entity_type', $type)->where('entity_id', $id)->delete();
            self::log('detach', $type, $id, (int) $link->language_id, $link->group_key, null, $actor, $reason);
        });
        self::flush();
    }

    /**
     * Undo every logged link change with id >= $sinceLogId, newest first, in one transaction (14 §5.4).
     * Returns the number of entries undone.
     */
    /**
     * Undo ONE logged link change (admin "restore", phase 1E-b), unlike restore() which undoes everything since an
     * entry. Refused unless the row is still where that change put it (nothing changed it since) and its old group
     * has no other row of its language now, so it can never regroup or drop anything else.
     */
    public static function undoEntry(int $logId, ?int $actor = null, ?string $reason = null): void
    {
        DB::transaction(function () use ($logId, $actor, $reason) {
            $e = DB::table('content_translation_log')->where('id', $logId)->lockForUpdate()->first();
            if (!$e || $e->action === 'restore') {
                throw new \RuntimeException('Không hoàn tác được mục nhật ký này.');
            }
            self::table($e->entity_type);
            $current = DB::table('content_translations')->where('entity_type', $e->entity_type)->where('entity_id', $e->entity_id)->lockForUpdate()->value('group_key');
            if ($current !== $e->new_group_key) {
                throw new \RuntimeException("{$e->entity_type} #{$e->entity_id} đã thay đổi sau mục nhật ký #{$logId}: không hoàn tác.");
            }
            if ($e->old_group_key && DB::table('content_translations')->where('entity_type', $e->entity_type)->where('group_key', $e->old_group_key)
                ->where('language_id', $e->language_id)->where('entity_id', '<>', $e->entity_id)->exists()) {
                throw new \RuntimeException("Nhóm cũ đã có bản cùng ngôn ngữ: không hoàn tác mục #{$logId}.");
            }
            DB::table('content_translations')->where('entity_type', $e->entity_type)->where('entity_id', $e->entity_id)->delete();
            if ($e->old_group_key) {
                DB::table('content_translations')->insert([
                    'entity_type' => $e->entity_type, 'entity_id' => $e->entity_id, 'group_key' => $e->old_group_key,
                    'language_id' => $e->language_id, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            self::log('restore', $e->entity_type, (int) $e->entity_id, (int) $e->language_id, $e->new_group_key, $e->old_group_key, $actor, $reason ?? "undo log #{$e->id}");
        });
        self::flush();
    }

    /** Link-log entries of every row that is or was in the group of $type #$id (newest first). */
    public static function history(string $type, int $id, int $limit = 50)
    {
        self::table($type);
        $group = DB::table('content_translations')->where('entity_type', $type)->where('entity_id', $id)->value('group_key');
        $groups = DB::table('content_translation_log')->where('entity_type', $type)->where('entity_id', $id)
            ->get(['old_group_key', 'new_group_key'])->flatMap(fn ($r) => [$r->old_group_key, $r->new_group_key])->push($group)->filter()->unique()->values();

        return DB::table('content_translation_log')->where('entity_type', $type)
            ->where(fn ($q) => $q->where('entity_id', $id)->orWhereIn('old_group_key', $groups)->orWhereIn('new_group_key', $groups))
            ->orderByDesc('id')->limit($limit)->get();
    }

    public static function restore(int $sinceLogId, ?int $actor = null): int
    {
        $count = DB::transaction(function () use ($sinceLogId, $actor) {
            $entries = DB::table('content_translation_log')->where('id', '>=', $sinceLogId)->where('action', '<>', 'restore')
                ->orderByDesc('id')->lockForUpdate()->get();
            foreach ($entries as $e) {
                DB::table('content_translations')->where('entity_type', $e->entity_type)->where('entity_id', $e->entity_id)->delete();
                if ($e->old_group_key) {
                    DB::table('content_translations')->insert([
                        'entity_type' => $e->entity_type, 'entity_id' => $e->entity_id, 'group_key' => $e->old_group_key,
                        'language_id' => $e->language_id, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
                self::log('restore', $e->entity_type, (int) $e->entity_id, (int) $e->language_id, $e->new_group_key, $e->old_group_key, $actor, "undo log #{$e->id}");
            }

            return $entries->count();
        });
        self::flush();

        return $count;
    }

    private static function log(string $action, string $type, int $id, int $languageId, ?string $old, ?string $new, ?int $actor, ?string $reason): void
    {
        if (!Schema::hasTable('content_translation_log')) {
            return; // before the 1C-2 migration: behave exactly as before
        }
        DB::table('content_translation_log')->insert([
            'action' => $action, 'entity_type' => $type, 'entity_id' => $id, 'language_id' => $languageId,
            'old_group_key' => $old, 'new_group_key' => $new, 'actor' => $actor,
            'reason' => $reason === null ? null : mb_substr($reason, 0, 255), 'created_at' => now(),
        ]);
    }

    private static function isLegacyLanguage(int $languageId): bool
    {
        $language = LocaleRegistry::byId($languageId);

        return $language !== null && LocaleRegistry::isLegacy($language);
    }

    /** Rows of $type in $languageId that can be linked (pages that exist on the site), as {id, name}. */
    public static function items(string $type, int $languageId): Collection
    {
        $table = self::table($type);

        return self::scoped($type, DB::table($table))
            ->where("$table.language_id", $languageId)
            ->orderBy("$table.id")
            ->get(["$table.id", "$table." . self::TYPES[$type]['name'] . ' as name'])
            ->map(fn ($row) => (object) ['id' => (int) $row->id, 'name' => (string) $row->name]);
    }

    /** What the site currently shows as the counterpart (explicit link, else the per-type heuristic). */
    public static function effectiveCounterpartId(string $type, int $id, AdLanguage $source, AdLanguage $target): ?int
    {
        try {
            $result = match ($type) {
                'cruise' => CruiseContentService::alternate(AppCruise::find($id), $source)['id'] ?? null,
                'cabin' => CabinContentService::alternate(AppCabin::find($id), $source)['id'] ?? null,
                'itinerary' => ItineraryContentService::alternate(AppItinerary::find($id), $source)['itineraryId'] ?? null,
                'experience' => ExperienceContentService::alternate(AppExpActivity::find($id), $source)['id'] ?? null,
                'article' => ArticleContentService::alternateFor($id, $source)['id'] ?? null,
                'article_category' => FeHreflangUtils::findArticleCategoryCounterpart(AppCategory::find($id), (int) $target->id)?->id,
                'faq_group' => FeHreflangUtils::findFaqGroupCounterpart(AppGroup::find($id), (int) $target->id)?->id,
                // Destinations have no heuristic: only explicit links count.
                'destination' => self::linkedId('destination', $id, (int) $target->id),
            };
        } catch (\Throwable) {
            return null;
        }

        return $result ? (int) $result : null;
    }

    public static function flush(): void
    {
        self::$links = [];
    }

    private static function links(string $type): array
    {
        self::table($type);
        if (!isset(self::$links[$type])) {
            self::$links[$type] = DB::table('content_translations')
                ->where('entity_type', $type)
                ->get(['entity_id', 'group_key', 'language_id'])
                ->mapWithKeys(fn ($row) => [(int) $row->entity_id => ['group' => $row->group_key, 'language_id' => (int) $row->language_id]])
                ->all();
        }

        return self::$links[$type];
    }

    /** Guards against links left behind by deleted rows. */
    private static function exists(string $type, int $id, int $languageId): bool
    {
        return DB::table(self::table($type))->where('id', $id)->where('language_id', $languageId)->exists();
    }

    private static function scoped(string $type, $query)
    {
        $table = self::table($type);

        return match ($type) {
            'cabin' => $query->join('app_group', 'app_group.id', '=', 'app_cabin.group_id')
                ->whereIn('app_group.slug', config('backend.listAccommodationSlug')),
            'article_category' => $query->where("$table.type", config('backend.categoryType.article'))->where("$table.slug", '!=', 'root'),
            'faq_group' => $query->where("$table.type", config('backend.groupType.faq'))->where("$table.slug", '!=', 'root'),
            default => $query,
        };
    }

    private static function table(string $type): string
    {
        if (!isset(self::TYPES[$type])) {
            throw new \InvalidArgumentException("Unknown translation type: $type");
        }

        return self::TYPES[$type]['table'];
    }
}
