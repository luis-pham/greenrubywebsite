<?php

namespace App\Services\Content;

use App\Services\Locale\LocaleRegistry;
use App\Services\Publication\PublicationContext;
use App\Services\Publication\PublicationPolicy;
use App\Support\NewsCategory;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AdLanguage;
use Modules\FrontEnd\Services\AppArticleService;
use Modules\FrontEnd\Services\AppFaqService;

/**
 * hreflang groups of blog categories and FAQ groups across EN/VI and new languages (1E hardening, item E). Listings
 * have no TranslationResolver: their EN/VI pair is today's heuristic (FeHreflangUtils), read by the website through
 * the API's counterpart redirect and by the Laravel sitemap. This class adds new-language members to such a pair
 * only where doing so cannot change what EN/VI show today:
 *
 *   - nothing at all while no new language is effectively public ([] → every output stays as it is);
 *   - the EN/VI members of the explicit group must be exactly the pair the site shows (the heuristic counterpart,
 *     both directions) and both must be listable (not empty/noindex). A pair the HTML and the sitemap disagree on —
 *     03 F-2: /blog/eco-sustainability ↔ the empty /vi/blog/du-lich-xanh — never qualifies, so it keeps today's
 *     output and its new-language page stays on its own (no hreflang), as before;
 *   - an EN or VI listing without any counterpart today may join a group with new-language members (the same rule
 *     as detail pages when a language launches);
 *   - every member is listable: valid slug, served section, at least one listed article / FAQ of its language.
 *
 * Output: the same list on every member (reciprocal by construction) with x-default, or [] (= today's form). News
 * categories (their own /news section) are never grouped here.
 */
final class GroupAlternates
{
    public const TYPES = ['article_category', 'faq_group'];

    /** @return array<int, array{lang: string, path: string}> */
    public static function for(string $type, object $row): array
    {
        if (!in_array($type, self::TYPES, true)) {
            return [];
        }
        $intl = LocaleRegistry::effectivePublic()->reject(fn (AdLanguage $l) => LocaleRegistry::isLegacy($l))->values();
        if ($intl->isEmpty() || !ContentTranslations::isLinked($type, (int) $row->id)) {
            return [];
        }

        return PublicationContext::asPublic(function () use ($type, $row, $intl) {
            $table = TranslatableFields::of($type)['table'];
            $rows = [];
            foreach (LocaleRegistry::effectivePublic() as $language) {
                $id = (int) $language->id === (int) $row->language_id ? (int) $row->id : ContentTranslations::linkedId($type, (int) $row->id, (int) $language->id);
                if ($id && ($member = DB::table($table)->where('id', $id)->where('language_id', $language->id)->first())) {
                    $rows[$language->code] = [$language, $member];
                }
            }
            foreach ($rows as [, $member]) {
                if ($type === 'article_category' && NewsCategory::isNews((string) $member->slug)) {
                    return [];
                }
            }
            if (!self::legacyPairUnchanged($type, $rows)) {
                return [];
            }
            $members = [];
            foreach ($rows as [$language, $member]) {
                if (!($path = self::listablePath($type, $member, $language))) {
                    if ((int) $member->id === (int) $row->id || LocaleRegistry::isLegacy($language)) {
                        return []; // self or an EN/VI member not listable: keep today's output
                    }
                    continue;
                }
                $members[] = ['language' => $language, 'path' => $path];
            }
            $hasIntl = collect($members)->contains(fn ($m) => !LocaleRegistry::isLegacy($m['language']));
            if (!$hasIntl || count($members) < 2) {
                return [];
            }

            return self::format($members);
        });
    }

    /**
     * The group's EN/VI members are exactly what the site pairs today: for two members, each one's heuristic
     * counterpart is the other; for one member, it has no counterpart today in the other legacy language.
     */
    private static function legacyPairUnchanged(string $type, array $rows): bool
    {
        $legacy = array_values(array_filter($rows, fn ($r) => LocaleRegistry::isLegacy($r[0])));
        $all = LocaleRegistry::effectivePublic()->filter(fn (AdLanguage $l) => LocaleRegistry::isLegacy($l))->values();
        foreach ($legacy as [$language, $member]) {
            foreach ($all as $other) {
                if ((int) $other->id === (int) $language->id) {
                    continue;
                }
                $shown = ContentTranslations::effectiveCounterpartId($type, (int) $member->id, $language, $other);
                $grouped = isset($rows[$other->code]) ? (int) $rows[$other->code][1]->id : null;
                if ($shown !== $grouped) {
                    return false;
                }
            }
        }

        return true;
    }

    /** The listing's public path when it is listable for its language, else null. */
    private static function listablePath(string $type, object $row, AdLanguage $language): ?string
    {
        if (!PublicationPolicy::isVisible($type, $row)) {
            return null;
        }
        $slug = (string) ($row->slug ?? '');
        if ($slug === '' || $slug === 'root' || (!LocaleRegistry::isLegacy($language) && !preg_match(SlugValidator::PATTERN, $slug))) {
            return null;
        }
        $section = $type === 'article_category' ? TranslationResolver::section('article_category', $slug) : 'faq';
        if (!LocaleRegistry::sectionReady($language, $section)) {
            return null;
        }
        if ($type === 'article_category') {
            // Same count as the listing's noIndex (ArticleContentService::index): an empty category is noindex.
            return AppArticleService::getPagingCount(['category_id' => $row->id, 'include_child' => true], (int) $language->id) > 0
                ? PathBuilder::blogCategory($language, $slug) : null;
        }

        return AppFaqService::getPagingCount(['group_id' => $row->id], (int) $language->id) > 0 ? PathBuilder::faqGroup($language, $slug) : null;
    }

    /** Members in language order plus x-default (lowest xdefault_priority; en/vi: the default language first). */
    private static function format(array $members): array
    {
        usort($members, fn ($a, $b) => [(int) ($a['language']->ord ?? 0), $a['language']->code] <=> [(int) ($b['language']->ord ?? 0), $b['language']->code]);
        $priority = function (AdLanguage $l): ?int {
            $p = $l->getAttribute('xdefault_priority');
            if ($p !== null) {
                return (int) $p;
            }

            return LocaleRegistry::isLegacy($l) ? ((bool) $l->is_default ? 1 : 2) : null;
        };
        $out = array_map(fn ($m) => ['lang' => (string) ($m['language']->getAttribute('hreflang') ?: $m['language']->code), 'path' => $m['path']], $members);
        $ranked = array_values(array_filter($members, fn ($m) => $priority($m['language']) !== null));
        usort($ranked, fn ($a, $b) => $priority($a['language']) <=> $priority($b['language']));
        if ($ranked !== []) {
            $out[] = ['lang' => 'x-default', 'path' => $ranked[0]['path']];
        }

        return $out;
    }
}
