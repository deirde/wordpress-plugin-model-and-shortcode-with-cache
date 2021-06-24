<?php
declare(strict_types=1);

namespace webforyou\be\modelWithCache {

    /**
     * No direct access
     */
    if (!defined('ABSPATH')) {
        exit;
    }

    /**
     * Class BeModelCacheInit
     *
     * @package webforyou\be\modelWithCache
     */
    class BeModelCacheInit
    {

        static function BeModelCacheInitActivation(): void
        {
            $BeModelCacheHelper = New BeModelCacheHelper();
            add_option($BeModelCacheHelper::CACHE_ENGINE);
            add_option($BeModelCacheHelper::CACHE_PATH);
            add_option($BeModelCacheHelper::CACHE_PURGE_CYCLE);
            add_option($BeModelCacheHelper::LAST_PURGE);
        }

        static function BeModelCacheInitDeactivation(): void
        {
            global $BeModelCacheHelper, $BeModelCacheInstance;
            delete_option($BeModelCacheHelper::CACHE_ENGINE);
            delete_option($BeModelCacheHelper::CACHE_PATH);
            delete_option($BeModelCacheHelper::CACHE_PURGE_CYCLE);
            delete_option($BeModelCacheHelper::LAST_PURGE);
            $BeModelCacheInstance->psr16adapter->clear();
        }

        static function BeModelCacheInitUninstall(): void
        {
            global $BeModelCacheHelper;
            $dir = $BeModelCacheHelper->getCachePath();
            if (is_dir($dir)) {
                $files = glob($dir.'/*');
                foreach ($files as $file) {
                    is_file($file) && unlink($file);
                }
                rmdir($dir);
            }
        }

    }

}
