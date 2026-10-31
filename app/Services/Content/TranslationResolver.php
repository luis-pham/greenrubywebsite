<?php

namespace App\Services\Content;

use App\Observers\PublishGuardObserver;
use App\Services\Locale\LocaleRegistry;
use App\Services\Publication\PublicationContext;
use App\Services\Publication\PublicationPolicy;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AdLanguage;
use Modules\FrontEnd\Services\AppCruiseItineraryService;

/**
 * hreflang alternates for any number of languages (12 §5.3 and §6, phase 1C-4), served as the additive
 * `alternates` field of detail payloads (the legacy single `alternate` is unchanged until G4).
 *
 * A group member is eligible when it is publicly visible (PublicationPolicy: effectively public language, own
 * flag, parent), has a usable slug and a canonical path (PathBuilder). Unlinked en/vi rows keep today's heuristic
 * counterpart when it is reciprocal (rule 7). Output: every eligible member plus x-default (the member with the
 * lowest xdefault_priority) when self is eligible and at least two members are; otherwise [] — the page then uses
 * today's single-language form.
 */
final class TranslationResolver
{
    /** @return array<int, array{lang: string, path: string}> */
    public static function alternates(string $type, object $row): array
    {
        return PublicationContext::asPublic(function () use ($type, $row) {
            $self = self::member($type, $row);
            if ($self === null) {
                return [];
            }
            $members = [$self];
            $source = LocaleRegistry::byId((int) $row->language_id);
            foreach (LocaleRegistry::effectivePublic() as $language) {
                if ((int) $language->id === (int) $row->language_id) {
                    continue;
                }
                $otherId = self::counterpartId($type, (int) $row->id, $source, $language);
                $other = $otherId ? self::load($type, $otherId) : null;
                if ($other && ($m = self::member($type, $other))) {
                    $members[] = $m;
                }
            }
            if (count($members) < 2) {
                return [];
            }
            usort($members, fn ($a, $b) => [$a['ord'], $a['lang']] <=> [$b['ord'], $b['lang']]);
            $withPriority = array_values(array_filter($members, fn ($m) => $m['priority'] !== null));
            usort($withPriority, fn ($a, $b) => $a['priority'] <=> $b['priority']);
            $out = array_map(fn ($m) => ['lang' => $m['hreflang'], 'path' => $m['path']], $members);
            if ($withPriority !== []) {
                $out[] = ['lang' => 'x-default', 'path' => $withPriority[0]['path']];
            }

            return $out;
        });
    }

    /** The row's own public path when it is an eligible member (visible, valid slug, section served), else null. */
    public static function selfPath(string $type, object $row): ?string
    {
        return PublicationContext::asPublic(fn () => self::member($type, $row)['path'] ?? null);
    }

    /** Explicit link first; for an unlinked legacy row, today's heuristic counterpart if it points back. */
    private static function counterpartId(string $type, int $id, ?AdLanguage $source, AdLanguage $target): ?int
    {
        if (ContentTranslations::isLinked($type, $id)) {
            return ContentTranslations::linkedId($type, $id, (int) $target->id);
        }
        if (!$source || !LocaleRegistry::isLegacy($source) || !LocaleRegistry::isLegacy($target)) {
            return null;
        }
        $guess = ContentTranslations::effectiveCounterpartId($type, $id, $source, $target);
        if (!$guess || ContentTranslations::isLinked($type, $guess)) {
            return null;
        }
        $back = ContentTranslations::effectiveCounterpartId($type, $guess, $target, $source);

        return $back === $id ? $guess : null;
    }

    /** @return array{lang: string, hreflang: string, path: string, priority: ?int, ord: int}|null */
    private static function member(string $type, object $row): ?array
    {
        if (!PublicationPolicy::isVisible($type, $row)) {
            return null;
        }
        $language = LocaleRegistry::byId((int) $row->language_id);
        $path = $language ? self::path($type, $row, $language) : null;
        if ($path === null) {
            return null;
        }

        return [
            'lang' => (string) $language->code,
            'hreflang' => (string) ($language->getAttribute('hreflang') ?: $language->code),
            'path' => $path,
            'priority' => $language->getAttribute('xdefault_priority') === null
                ? (LocaleRegistry::isLegacy($language) ? ((bool) $language->is_default ? 1 : 2) : null)
                : (int) $language->getAttribute('xdefault_priority'),
            'ord' => (int) ($language->ord ?? 0),
        ];
    }

    private static function path(string $type, object $row, AdLanguage $language): ?string
    {
        if (!LocaleRegistry::isLegacy($language)) {
            $slug = Slugs::stored($row); // never derived for intl languages
        } else {
            $slug = $type === 'article'
                ? (Slugs::stored($row) ?: Slugs::derive((string) ($row->title ?? '')))
                : Slugs::of($row);
        }
        if ($slug === '' || (!LocaleRegistry::isLegacy($language) && !preg_match(SlugValidator::PATTERN, $slug))) {
            return null; // 12 §5.3 rule 3: no usable slug, no URL
        }

        // 12 §5.3 rule 4 (phase 1C-5): the route must exist — every web instance serves this section for the language.
        $category = $type === 'article' ? DB::table('app_category')->where('id', $row->category_id ?? 0)->value('slug') : null;
        if (!LocaleRegistry::sectionReady($language, self::section($type, $category))) {
            return null;
        }

        return match ($type) {
            'cruise' => PathBuilder::cruise($language, $slug, (int) $row->id),
            'cabin' => PathBuilder::cabin($language, $slug, (int) $row->id),
            'experience' => PathBuilder::experience($language, $slug, (int) $row->id),
            'itinerary' => ($c = AppCruiseItineraryService::resolveCanonicalShowParams((int) $row->id, (int) $language->id))
                ? PathBuilder::itinerary($language, $slug, (int) $c['cruise_id'], (int) $row->id) : null,
            'destination' => DestinationContentService::hubVisible($language) ? PathBuilder::destination($language, $slug) : null,
            'article' => $category ? PathBuilder::article($language, (string) $category, $slug) : null,
            default => null,
        };
    }

    /** The frontend section (route family) of an entity type, as listed in the web manifest. */
    public static function section(string $type, ?string $categorySlug = null): string
    {
        return match ($type) {
            'experience' => 'experiences',
            'destination' => 'destinations',
            'article', 'article_category' => in_array($categorySlug, PathBuilder::NEWS_CATEGORY, true) ? 'news' : 'blog',
            'faq_group', 'faq' => 'faq',
            default => $type, // cruise, cabin, itinerary
        };
    }

    private static function load(string $type, int $id): ?object
    {
        $model = array_search($type, PublishGuardObserver::MODELS, true);

        return $model ? $model::find($id) : null;
    }
}
