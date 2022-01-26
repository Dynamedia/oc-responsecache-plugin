<?php namespace Dynamedia\ResponseCache\Models;

use Cms\Classes\Page;
use Dynamedia\ResponseCache\Classes\CacheCleaner;
use Illuminate\Support\Facades\Cache;
use October\Rain\Database\Model;

/**
 * Class Settings
 * @package Dynamedia\ResponseCache\Models
 */
class Settings extends Model
{
    /**
     *
     */
    public const CACHE_KEY = 'dynamedia.responsecache.setting';

    /**
     * @var string[]
     */
    public $implement = [
        'System.Behaviors.SettingsModel',
    ];

    /**
     * @var string
     */
    public $settingsCode = 'dynamedia_responsecache_settings';

    /**
     * @var string
     */
    public $settingsFields = 'fields.yaml';

    /**
     * Clear the cache when saving settings and cache is disabled
     */
    public function afterSave() : void
    {
        if (!$this->is_enabled) {
            CacheCleaner::clear();
        }
    }

    /**
     * @return bool
     */
    public static function isAutoClearingEnabled() : bool
    {
        return (int) Settings::instance()->get('auto_clearing', false) === 1;
    }

    /**
     * @return array
     */
    public static function getExcludeListPatterns(): array
    {
        return \array_map(static function(array $row): string {
            return $row['url_pattern'];
        }, (array) Settings::instance()->get('exclude', []));
    }

    /**
     * @return array
     */
    public function getBlogCategoryPatternOptions(): array
    {
        return self::getCmsPagesList();
    }

    /**
     * @return array
     */
    public function getBlogPostPatternOptions(): array
    {
        return self::getCmsPagesList();
    }

    /**
     * @return array
     */
    private static function getCmsPagesList(): array
    {
        $arCmsPagesRaw = Page::sortBy('baseFileName')->toArray();
        $arCmsPages = [];

        foreach ($arCmsPagesRaw as $sKey => $arCmsPage) {
            if (!isset($arCmsPage['url'])) continue;

            $sFileName = substr($sKey, 0,-4);

            $sUrl = preg_replace("(\?[^\/]*(?:/|$))",'',$arCmsPage['url']);

            $arCmsPages[$sUrl] = "{$arCmsPage['title']} | {$sFileName} | $sUrl";
        }

        return $arCmsPages;
    }
}
