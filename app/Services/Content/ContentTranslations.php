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
     */
    public static function link(string $type, array $ids): void
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

        DB::transaction(function () use ($type, $rows) {
            DB::table('content_translations')->where('entity_type', $type)->whereIn('entity_id', $rows->pluck('id'))->delete();
            $group = (string) Str::uuid();
            DB::table('content_translations')->insert($rows->map(fn ($row) => [
                'entity_type' => $type,
                'entity_id' => (int) $row->id,
                'group_key' => $group,
                'language_id' => (int) $row->language_id,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all());
        });
        self::flush();
    }

    /**
     * Forget the link of $id and of the rows linked with it: they all go back to the heuristic pairing
     * (dropping only one side would leave its partner pinned to "no translation").
     */
    public static function unlink(string $type, int $id): void
    {
        self::table($type);
        $group = DB::table('content_translations')->where('entity_type', $type)->where('entity_id', $id)->value('group_key');
        if ($group) {
            DB::table('content_translations')->where('entity_type', $type)->where('group_key', $group)->delete();
        }
        self::flush();
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
