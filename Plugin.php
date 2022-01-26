<?php namespace Dynamedia\ResponseCache;

use Backend, Event;
use System\Classes\PluginBase;
use Illuminate\Contracts\Http\Kernel;
use Dynamedia\ResponseCache\Models\Settings;
use Dynamedia\ResponseCache\Classes\Cache;
use Dynamedia\ResponseCache\Classes\Console\ClearCache;
use Dynamedia\ResponseCache\Classes\Contracts\Cache as PageCacheContract;
use Dynamedia\ResponseCache\Classes\Middleware\CacheResponse;
use Dynamedia\ResponseCache\Classes\Event\CacheClearHandler;
use Dynamedia\ResponseCache\ReportWidgets\CacheStatus;
use Dynamedia\ResponseCache\Classes\Schedule\CheckScheduledPosts;

/**
 * ResponseCache Plugin Information File
 *
 * This plugin is a derivative of https://github.com/Biz-mark/Quicksilver with added features to support
 * current and future Dynamedia plugins.
 *
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'dynamedia.responsecache::lang.plugin.name',
            'description' => 'dynamedia.responsecache::lang.plugin.description',
            'author'      => 'Dynamedia',
            'icon'        => 'icon-bolt'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(PageCacheContract::class, Cache::class);

        $this->registerConsoleCommand('response-cache:clear', ClearCache::class);
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app[Kernel::class]->prependMiddleware(CacheResponse::class);

        $this->addEventListeners();
    }

    public function addEventListeners()
    {
        \Event::subscribe(CacheClearHandler::class);
    }

    /**
     * Registering schedule for clearing cache for scheduled posts
     * */
    public function registerSchedule($schedule): void
    {
        CheckScheduledPosts::check($schedule);
    }

    /**
     * Returns the array with report widgets for the dashboard.
     *
     * @return array
     */
    public function registerReportWidgets(): array
    {
        return [
            CacheStatus::class => [
                'label'   => 'dynamedia.responsecache::lang.reportwidget.cachestatus.name',
                'context' => 'dashboard'
            ],
        ];
    }

    /**
     * @return array
     */
    public function registerSettings(): array
    {
        return [
            'options' => [
                'label'       => 'dynamedia.responsecache::lang.settings.label',
                'description' => 'dynamedia.responsecache::lang.settings.description',
                'class'       => Settings::class,
                'icon'        => 'icon-cog',
                'category'    => 'dynamedia.responsecache::lang.settings.label'
            ]
        ];
    }
}
