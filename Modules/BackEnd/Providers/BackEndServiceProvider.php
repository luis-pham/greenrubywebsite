<?php

namespace Modules\BackEnd\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AdConfigService;
use Modules\BackEnd\Services\AdMenuService;
use Modules\BackEnd\Services\AdPrivilegeService;
use Modules\BackEnd\Services\AdLanguageService;

class BackEndServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'BackEnd';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'backend';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $list = AdPrivilegeService::getAllJoinAdResource();
        foreach ($list as $obj) {
            $ability = $obj->resource_alias . '-' . $obj->privilege_alias;
            Gate::define($ability, function ($user) use ($obj) {
                return $user->hasAccess($obj->resource_alias, $obj->privilege_alias);
            });
        }

        View::composer('backend::layouts.master', function ($view) {
            $listLanguage = AdLanguageService::getAll();
            $currentLanguage = LanguageUtils::getCurrentLanguage();
            $view->with('listLanguage', $listLanguage);
            $view->with('currentLanguage', $currentLanguage);

            $user = Auth::user();
            $menu = [];
            if ($user) {
                $defaultLanguage = $listLanguage->where('is_default', true)->first();
                $listMenu = AdMenuService::getMenuByUserId($user->id);
                $menu = $this->processMenu($listMenu, $defaultLanguage);
                $menu = $this->bindMenu($menu);
            }
            $view->with('menu', $menu);
        });

        View::composer(['backend::layouts.master', 'backend::layouts.popup'], function ($view) {
            $config = [];
            try {
                $language = LanguageUtils::getCurrentLanguage();
                $config = Utilities::getAllConfig($language);
            } catch (\Exception $e) {

            }
            $view->with('config', $config);
        });

        View::composer(['backend::auth.index'], function ($view) {
            $config = [];
            try {
                $language = AdLanguageService::getDefaultLanguage();
                $config = Utilities::getAllConfig($language);
            } catch (\Exception $e) {

            }
            $view->with('config', $config);
        });

        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        // Factories are auto-discovered in Laravel 9+
        // $this->registerFactories();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     * @deprecated In Laravel 9+, factories are auto-discovered. This method is kept for backward compatibility.
     */
    public function registerFactories()
    {
        // Factories are now auto-discovered in Laravel 9+
        // If you have factories in Modules, ensure they extend Illuminate\Database\Eloquent\Factories\Factory
        // and are in the Database\Factories namespace
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }

    private function bindMenuUrl($url, $languageCode)
    {
        if (!$url) {
            return $url;
        }

        if (!(Str::startsWith($url, 'http://') || Str::startsWith($url, 'https://'))) {
            if (!Str::startsWith($url, '/')) {
                $url = '/' . $url;
            }
            
            if (Str::startsWith($url, '/admincp')) {
                $listPath = explode('/', $url);
                $listPathNew = [$listPath[1], $languageCode];
                for ($i = 2; $i < count($listPath); $i++) {
                    $listPathNew[] = $listPath[$i];
                }
                $url = '/' . implode('/', $listPathNew);
            } else {
                $url = '/' . $languageCode . $url;
            }
        }

        return $url;
    }

    private function processMenu($list, $defaultLanguage)
    {
        $language = LanguageUtils::getCurrentLanguage();
        $isUpdateUrl = $language && $language->id != $defaultLanguage->id;
        for ($i = 0; $i < count($list); $i++) {
            $flag = false;
            
            if (isset($list[$i]->url) && str_contains($list[$i]->url, 'cabin-manager')) {
                $list[$i]->name = __('backend::cabin.menu_name');
            }

            if ($list[$i]->is_multi_language) {
                if ($isUpdateUrl) {
                    $list[$i]->url = $this->bindMenuUrl($list[$i]->url, $language->code);
                }
    
                if ($list[$i]->active_url) {
                    $listActiveUrl = json_decode($list[$i]->active_url);
                    foreach ($listActiveUrl as $key => $value) {
                        if ($isUpdateUrl) {
                            $listActiveUrl[$key] = $this->bindMenuUrl($value, $language->code);
                        }
                    }
                    $list[$i]->active_url = json_encode($listActiveUrl, JSON_UNESCAPED_UNICODE);
                }
            }

            if (!$list[$i]->parent_id) {
                continue;
            }

            for ($j = 0; $j < count($list); $j++) {
                if ($i == $j) {
                    continue;
                }

                if ($list[$i]->parent_id == $list[$j]->id) {
                    $flag = true;
                    break;
                }
            }

            if (!$flag) {
                $list[$i]->parent_id = null;
            }
        }

        return $list;
    }

    private function bindMenu($list, $parent_id = null)
    {
        $menu = [];

        for ($i = 0; $i < count($list); $i++) {
            if ($list[$i]->parent_id == $parent_id) {
                $obj = $list[$i];
                $obj->child = $this->bindMenu($list, $list[$i]->id);
                $menu[] = $obj;
            }
        }

        return $menu;
    }
}
