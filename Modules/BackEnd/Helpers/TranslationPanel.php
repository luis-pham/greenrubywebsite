<?php

namespace Modules\BackEnd\Helpers;

use App\Services\Content\BusinessFields;
use App\Services\Content\ContentTranslations;
use App\Services\Content\Freshness;
use App\Services\Content\SlugService;
use App\Services\Content\SlugValidator;
use App\Services\Content\TranslatableFields;
use App\Services\Content\TranslationMatrix;
use App\Services\Content\Workflow;
use App\Services\Locale\LocaleRegistry;
use Illuminate\Support\Facades\DB;

/**
 * The translation panel shown above every content edit form (phase 1E-c): the row's language, its publication,
 * workflow and freshness, the source text side by side (read-only), the business values it inherits from the
 * price-list row (read-only), its URL slug with the explicit change action and history, its other language
 * versions, and the workflow / preview actions (1E-d). The existing forms are not duplicated per language.
 */
final class TranslationPanel
{
    /**
     * A page of a new language (page-level publication): visibility, workflow and its actions. en/vi pages keep
     * their existing show/hide switch and get no panel.
     */
    private static function page(object $row): ?array
    {
        $fresh = empty($row->id) ? null : DB::table('app_page')->where('id', $row->id)->first();
        $language = $fresh ? LocaleRegistry::byId((int) $fresh->language_id) : null;
        if (!$fresh || !$language || LocaleRegistry::isLegacy($language)) {
            return null;
        }
        $tm = DB::table('translation_meta')->where('entity_type', 'page')->where('entity_id', $fresh->id)->first();

        return [
            'type' => 'page', 'label' => 'Trang ' . $fresh->code, 'row' => $fresh, 'language' => $language, 'controlled' => true,
            'published' => (int) $fresh->is_visible === 1, 'workflow' => $tm->workflow ?? null, 'freshness' => 'legacy',
            'source' => null, 'sourceLanguage' => null, 'sourceText' => [], 'business' => [], 'businessFields' => [],
            'slug' => null, 'slugHistory' => collect(), 'versions' => [],
            'actions' => $tm ? Workflow::actionsFor('page', (int) $fresh->id, self::actor(), true) : [],
            'pending' => \App\Services\Content\RevisionStore::open('page', (int) $fresh->id),
        ];
    }

    /**
     * A structural group of a new language (cabin facility type, experience tab, service group, suitable audience;
     * phase 2C): seeded unpublished by i18n:seed-locale, reviewed and published here (Workflow type 'group').
     */
    private static function group(object $fresh): ?array
    {
        $language = LocaleRegistry::byId((int) $fresh->language_id);
        $tm = DB::table('translation_meta')->where('entity_type', 'group')->where('entity_id', $fresh->id)->first();
        if (!$language || LocaleRegistry::isLegacy($language) || !$tm) {
            return null;
        }
        $source = $tm->source_entity_id ? DB::table('app_group')->where('id', $tm->source_entity_id)->first() : null;

        return [
            'type' => 'group', 'label' => 'Nhóm', 'row' => $fresh, 'language' => $language, 'controlled' => true,
            'published' => ($fresh->publish_state ?? 'published') === 'published', 'workflow' => $tm->workflow, 'freshness' => 'legacy',
            'source' => $source, 'sourceLanguage' => $source ? LocaleRegistry::byId((int) $source->language_id) : null,
            'sourceText' => $source ? array_map(fn ($c) => ['field' => $c, 'value' => $source->{$c} ?? null], Workflow::OWN_TYPES['group']['text']) : [],
            'business' => [], 'businessFields' => [], 'slug' => null, 'slugHistory' => collect(), 'versions' => [],
            'actions' => Workflow::actionsFor('group', (int) $fresh->id, self::actor(), true),
            'pending' => \App\Services\Content\RevisionStore::open('group', (int) $fresh->id),
        ];
    }

    /** Edit view => [content type, view variable holding the row]. */
    public const VIEWS = [
        'backend::cruise.detail' => ['cruise', 'cruise'],
        'backend::cabin.edit' => ['cabin', 'obj'],
        'backend::itinerary.detail' => ['itinerary', 'itinerary'],
        'backend::exp-activity.edit' => ['experience', 'obj'],
        'backend::destination.edit' => ['destination', 'obj'],
        'backend::article.edit' => ['article', 'obj'],
        'backend::category.edit' => ['article_category', 'obj'],
        'backend::group.edit' => ['faq_group', 'obj'],
        'backend::faq.edit' => ['faq', 'obj'],
        'backend::service.edit' => ['service', 'obj'],
        'backend::amenity.edit' => ['amenity', 'obj'],
        // Page-level publication (phase 1E-c): static pages and hubs of a new language.
        'backend::page-config.index' => ['page', 'page'],
    ];

    public static function for(string $type, object $row): ?array
    {
        if ($type === 'page') {
            return self::page($row);
        }
        if (empty($row->id) || !TranslatableFields::has($type)) {
            return null;
        }
        $meta = TranslatableFields::of($type);
        $fresh = DB::table($meta['table'])->where('id', $row->id)->first();
        if ($fresh && $type === 'faq_group' && (int) $fresh->type !== (int) config('backend.groupType.faq')) {
            return self::group($fresh);
        }
        if (!$fresh) {
            return null;
        }
        if ($type === 'article_category' && (int) $fresh->type !== (int) config('backend.categoryType.article')) {
            return null;
        }
        $language = LocaleRegistry::byId((int) $fresh->language_id);
        if (!$language) {
            return null;
        }
        $tm = DB::table('translation_meta')->where('entity_type', $type)->where('entity_id', $fresh->id)->first();
        $source = $tm && $tm->source_entity_id ? DB::table($meta['table'])->where('id', $tm->source_entity_id)->first() : null;
        $sourceLanguage = $source ? LocaleRegistry::byId((int) $source->language_id) : null;

        $business = [];
        if (!BusinessFields::ownsBusinessData((int) $fresh->language_id)) {
            $inventory = BusinessFields::overlay($type, $fresh);
            foreach ($meta['business'] as $column) {
                $business[] = ['field' => $column, 'inherited' => $inventory->{$column} ?? null];
            }
        }

        $versions = [];
        if ($meta['grouped'] && ContentTranslations::isLinked($type, (int) $fresh->id)) {
            foreach (LocaleRegistry::editable() as $other) {
                if ((int) $other->id === (int) $fresh->language_id) {
                    continue;
                }
                $otherId = ContentTranslations::linkedId($type, (int) $fresh->id, (int) $other->id);
                $versions[] = ['language' => $other, 'id' => $otherId, 'edit' => $otherId ? AdminLinks::edit($type, $otherId, $other) : null];
            }
        }

        $slugType = array_key_exists($type, SlugValidator::TYPES);

        return [
            'type' => $type,
            'label' => $meta['label'],
            'row' => $fresh,
            'language' => $language,
            'controlled' => !LocaleRegistry::isLegacy($language),
            'published' => TranslationMatrix::published($type, $fresh),
            'workflow' => $tm->workflow ?? null,
            'freshness' => $tm ? Freshness::status($type, (int) $fresh->id) : 'legacy',
            'source' => $source,
            'sourceLanguage' => $sourceLanguage,
            'sourceText' => $source ? array_map(fn ($c) => ['field' => $c, 'value' => $source->{$c} ?? null], $meta['text']) : [],
            'business' => $business,
            'businessFields' => $meta['business'],
            'slug' => $slugType ? SlugService::current($type, $fresh) : null,
            'slugHistory' => $slugType ? SlugService::history($type, (int) $fresh->id) : collect(),
            'versions' => $versions,
            'actions' => $tm ? Workflow::actionsFor($type, (int) $fresh->id, self::actor(), true) : [],
            'pending' => \App\Services\Content\RevisionStore::open($type, (int) $fresh->id),
        ];
    }

    private static function actor(): ?int
    {
        $id = auth('admin')->id();

        return $id === null ? null : (int) $id;
    }
}
