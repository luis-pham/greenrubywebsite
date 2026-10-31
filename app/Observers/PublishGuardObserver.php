<?php

namespace App\Observers;

use App\Services\Content\SlugValidator;
use App\Services\Content\Slugs;
use App\Services\Locale\LocaleRegistry;
use App\Services\Publication\PublicationPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Workflow gates publication transitions (12 §4.2, phase 1C-2). For a language under editorial workflow
 * (ad_language.workflow_controlled; en/vi are not), a row is created unpublished and can only become published
 * when its translation_meta workflow is 'approved' (legal-class types also need legal approval). It covers every
 * Eloquent path: admin forms, the editor API, tinker and commands. Legacy en/vi rows are never touched.
 */
class PublishGuardObserver
{
    /** model class => PublicationPolicy type */
    public const MODELS = [
        \Modules\BackEnd\Entities\AppCruise::class => 'cruise',
        \Modules\BackEnd\Entities\AppCabin::class => 'cabin',
        \Modules\BackEnd\Entities\AppItinerary::class => 'itinerary',
        \Modules\BackEnd\Entities\AppExpActivity::class => 'experience',
        \Modules\BackEnd\Entities\AppFaq::class => 'faq',
        \Modules\BackEnd\Entities\AppGroup::class => 'faq_group',
        \Modules\BackEnd\Entities\AppCategory::class => 'article_category',
        \Modules\BackEnd\Entities\AppArticle::class => 'article',
        \Modules\BackEnd\Entities\AppDestination::class => 'destination',
        \Modules\BackEnd\Entities\AppPage::class => 'page',
    ];

    /** Types whose public text has legal weight: publishing also needs legal approval. */
    public const LEGAL_TYPES = ['faq', 'faq_group'];

    public function creating(Model $model): void
    {
        if (!self::controlled($model)) {
            return;
        }
        foreach (PublicationPolicy::unpublishedAttributes(self::type($model)) as $column => $value) {
            if ($column !== 'publish_state' || PublicationPolicy::hasColumn($model->getTable())) {
                $model->setAttribute($column, $value);
            }
        }
    }

    public function updating(Model $model): void
    {
        if (!self::controlled($model)) {
            return;
        }
        $type = self::type($model);
        $before = (object) $model->getOriginal();
        $after = (object) $model->getAttributes();
        if (PublicationPolicy::isPublishedFlag($type, $before) || !PublicationPolicy::isPublishedFlag($type, $after)) {
            return; // not a transition to published
        }
        $meta = DB::table('translation_meta')->where('entity_type', $type)->where('entity_id', $model->getKey())->first();
        if (!$meta || $meta->workflow !== 'approved') {
            throw new \App\Services\Content\EditorialRefusal("Cannot publish {$type} #{$model->getKey()}: its translation is not approved (workflow: " . ($meta->workflow ?? 'none') . ').');
        }
        // A public URL needs a valid slug (phase 1C-3): a Japanese name derives none, so an editor must set one.
        if (array_key_exists($type, SlugValidator::TYPES)) {
            $slug = Slugs::stored($model); // controlled (non en/vi) languages: an explicit slug, never a derived one
            if (!preg_match(SlugValidator::PATTERN, $slug)) {
                throw new \App\Services\Content\EditorialRefusal("Cannot publish {$type} #{$model->getKey()}: it has no valid slug (set one with php artisan content:slug).");
            }
        }
        if (in_array($type, self::LEGAL_TYPES, true) && empty($meta->legal_approved_at)) {
            throw new \App\Services\Content\EditorialRefusal("Cannot publish {$type} #{$model->getKey()}: legal approval is missing.");
        }
    }

    public static function type(Model $model): string
    {
        return self::MODELS[get_class($model)];
    }

    /** True for rows of a language under editorial workflow. Unknown or legacy languages: false. */
    public static function controlled(Model $model): bool
    {
        $language = LocaleRegistry::byId((int) $model->getAttribute('language_id'));
        if (!$language || LocaleRegistry::isLegacy($language)) {
            return false;
        }
        $flag = $language->getAttribute('workflow_controlled');

        return $flag === null ? true : (bool) $flag;
    }
}
