<?php namespace Dynamedia\ResponseCache\Classes\Schedule;

use Dynamedia\ResponseCache\Classes\CacheCleaner;
use System\Classes\PluginManager;

class CheckScheduledPosts
{
    public static function check($schedule)
    {
        if (PluginManager::instance()->hasPlugin('RainLab.Blog')) {
            $schedule->call(function () {
                CacheCleaner::checkScheduledPosts();
            })->everyMinute();
        }
    }
}
