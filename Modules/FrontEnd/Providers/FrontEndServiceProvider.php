<?php

namespace Modules\FrontEnd\Providers;

use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AdLanguageService;
use Modules\BackEnd\Services\AppMenuFrontEndService;
use Modules\BackEnd\Services\AppTestimonialService;
use Modules\FrontEnd\Helpers\FeLanguageUtils;
use Modules\FrontEnd\Helpers\FeUtils;

class FrontEndServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'FrontEnd';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'frontend';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        \View::composer(['frontend::layouts.master'], function ($view) {
            $listLanguage = AdLanguageService::getAll();
            $currentLanguage = FeLanguageUtils::getCurrentLanguage();
            $view->with('listLanguage', $listLanguage);
            $view->with('currentLanguage', $currentLanguage);

            $config = [];
            try {
                $config = Utilities::getAllConfig($currentLanguage);
                $view->with('config', $config);
            } catch (\Exception $e) {

            }

            $listMenuPrimary = [];
            try {
                $listMenuPrimary = $this->getMenu('primary-menu');
            } catch (\Exception $e) {

            }
            $view->with('listMenuPrimary', $listMenuPrimary);

            $listMenuFooter = [];
            try {
                $listMenuFooter = $this->getMenu('footer-menu');
            } catch (\Exception $e) {

            }
            $view->with('listMenuFooter', $listMenuFooter);
        });

        \View::composer('frontend::shared.section.section-testimonial', function ($view) {
            $list = [];
            try {
                $currentLanguage = FeLanguageUtils::getCurrentLanguage();
                $list = AppTestimonialService::getAll($currentLanguage->id);
            } catch (\Exception $e) {

            }
            $view->with('list', $list);
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
            module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
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
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }

    private function getMenu($code)
    {
        $language = FeLanguageUtils::getCurrentLanguage();
        $obj = AppMenuFrontEndService::getByCode($code, $language->id);
        $data = json_decode($obj->menu);
        $data = $this->processMenu($data, $language);
        $data = $this->bindMenuDeep($data);
        $data = $this->bindMenu($data);

        return $data;
    }

    private function processMenu($list, $language)
    {
        for ($i = 0; $i < count($list); $i++) {
            $flag = false;

            if (!Str::startsWith($list[$i]->url, 'javascript:') && !Str::startsWith($list[$i]->url, '#')) {
                $list[$i]->url = FeUtils::localizeMenuUrl($list[$i]->url, $language);
            }

            $list[$i]->url = $this->canonicalizeMenuUrl($list[$i]->url);

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

    private function bindMenu($list, $parentId = null)
    {
        $menu = [];

        for ($i = 0; $i < count($list); $i++) {
            if ($list[$i]->parent_id == $parentId) {
                $obj = $list[$i];
                $obj->child = $this->bindMenu($list, $list[$i]->id);
                $menu[] = $obj;
            }
        }

        return $menu;
    }

    private function bindMenuDeep($list)
    {
        for ($i = 0; $i < count($list); $i++) {
            $list[$i]->deep = $this->getMenuDeep($list, $list[$i]);
        }

        return $list;
    }

    private function getMenuDeep($list, $obj)
    {
        $list = $this->getMenuChild($list, $obj);
        if (count($list) == 0) {
            return 1;
        }

        $maxLevel = $list[0]->level;
        if (count($list) > 1) {
            for ($i = 1; $i < count($list); $i++) {
                if ($maxLevel < $list[$i]->level) {
                    $maxLevel = $list[$i]->level;
                }
            }
        }

        return $maxLevel - $obj->level + 1;
    }

    private function canonicalizeMenuUrl($url)
    {
        $path = FeUtils::getAbsoluteUrl($url);
        if (!$path) {
            return $url;
        }

        $normalized = FeUtils::normalizeMenuPath($path);
        if ($normalized === $path) {
            return $url;
        }

        return str_replace($path, $normalized, $url);
    }

    private function getMenuChild($list, $obj)
    {
        $data = [];

        for ($i = 0; $i < count($list); $i++) {
            if ($list[$i]->parent_id == $obj->id) {
                $data[] = $list[$i];
                $data = array_merge($data, $this->getMenuChild($list, $list[$i]));
            }

            // if ($obj->lft < $list[$i]->lft && $obj->rgt > $list[$i]->rgt) {
            //     $data[] = $list[$i];
            // }
        }

        return $data;
    }
}
