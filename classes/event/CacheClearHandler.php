<?php namespace Dynamedia\ResponseCache\Classes\Event;

use Cms\Classes\Page;
use System\Classes\PluginManager;
use Dynamedia\ResponseCache\Models\Settings;
use Dynamedia\ResponseCache\Classes\CacheCleaner;

class CacheClearHandler
{
    public function subscribe($event)
    {
        $event->listen('cache:cleared', static function (): void {
            CacheCleaner::clear();
        });

        if (Settings::isAutoclearingEnabled() === true) {

            $pluginManager = PluginManager::instance();

            $this->cmsPagesClearing();
            if ($pluginManager->hasPlugin('RainLab.Blog')) {
                $this->rainlabStaticPagesClearing();
            }

            if ($pluginManager->hasPlugin('RainLab.Pages')) {
                $this->rainlabBlogClearing();
            }
        }
    }

    public function cmsPagesClearing()
    {
        Page::extend(function ($model) {
            $model->bindEvent('model.afterSave', function () use ($model) {
                CacheCleaner::clearUrl($model->url);
            });
        });
    }

    public function rainlabStaticPagesClearing()
    {

        \RainLab\Pages\Classes\Page::extend(function ($model) {
            $model->bindEvent('model.afterSave', function () use ($model) {
                CacheCleaner::clearUrl($model->url);
            });
        });

        \RainLab\Pages\Classes\Menu::extend(function ($model) {
            $model->bindEvent('model.afterSave', function () use ($model) {
                CacheCleaner::clear();
            });
        });
    }

    public function rainlabBlogClearing()
    {
        \RainLab\Blog\Models\Post::extend(function ($model) {
            $model->bindEvent('model.afterSave', function () use ($model) {
                CacheCleaner::scheduleOrClearPost($model);
            });
        });

        \RainLab\Blog\Models\Category::extend(function ($model) {
            $model->bindEvent('model.afterSave', function () use ($model) {
                CacheCleaner::clearCategory($model);
            });
        });
    }
}
