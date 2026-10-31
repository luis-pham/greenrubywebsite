<?php

namespace App\Providers;

use App\Observers\PublishGuardObserver;
use App\Observers\SlugObserver;
use App\Services\Locale\LocaleRegistry;
use App\Services\Publication\PublishedScope;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Modules\BackEnd\Entities\AdLanguage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('path.public', function() {
            return base_path().'/public';
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->environment('production')
            && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Any change to a language row (admin, CLI, tinker) takes effect at once: the registry cache is dropped.
        // Until publication gating and N-language sitemaps exist (phase 1C), no language beyond en/vi may be created:
        // sitemaps and admin routes still list every ad_language row (config i18n.allow_new_locales).
        AdLanguage::creating(function (AdLanguage $language) {
            if (!config('i18n.allow_new_locales') && !in_array(strtolower((string) $language->code), ['en', 'vi'], true)) {
                throw new \RuntimeException("Adding language '{$language->code}' is blocked until phase 1C (I18N_ALLOW_NEW_LOCALES).");
            }
        });
        AdLanguage::saved(fn () => LocaleRegistry::flush());
        // Row-level publication (phase 1C-2): public reads hide unpublished rows; workflow gates publishing.
        foreach (PublishGuardObserver::MODELS as $model => $type) {
            if (in_array($type, ['cruise', 'cabin', 'itinerary', 'experience', 'faq', 'faq_group', 'article_category'], true)) {
                $model::addGlobalScope(new PublishedScope());
            }
            $model::observe(PublishGuardObserver::class);
        }
        foreach (SlugObserver::MODELS as $model) {
            $model::observe(SlugObserver::class);
        }
        // Phase 1E-d: published text of a workflow-controlled language changes only through an approved revision.
        foreach (array_keys(PublishGuardObserver::MODELS) as $model) {
            $model::observe(\App\Observers\ContentRevisionObserver::class);
            $model::retrieved(fn ($row) => \App\Services\Preview\Preview::overlay($row)); // preview shows the pending revision
        }
        \Modules\BackEnd\Entities\AppCabinRoom::retrieved(fn ($row) => \App\Services\Preview\Preview::overlayChild($row));
        \Modules\BackEnd\Entities\AppItineraryDayDetail::retrieved(fn ($row) => \App\Services\Preview\Preview::overlayChild($row));
        // Phase 1E-c: a text-only language (no own price list) never writes business data.
        foreach (array_keys(\App\Observers\BusinessFieldsObserver::MODELS) as $model) {
            $model::observe(\App\Observers\BusinessFieldsObserver::class);
        }
        AdLanguage::deleted(fn () => LocaleRegistry::flush());
    }
}
