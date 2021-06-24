<?php

declare(strict_types=1);

namespace webforyou\be\modelWithCache {

    use Phpfastcache\CacheManager;
    use Phpfastcache\Config\ConfigurationOption;
    use Phpfastcache\Helper\Psr16Adapter;

    /**
     * No direct access
     */
    if (!defined('ABSPATH')) {
        exit;
    }

    /**
     * Class BeModelCacheInstance
     *
     * @package webforyou\be\modelWithCache
     */
    class BeModelCacheInstance
    {

        /**
         * @var null
         */
        private static $instance = null;
        /**
         * @var \phpFastCache\Core\DriverAbstract|\Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface
         */
        public $cacheInstance;
        /**
         * @var Psr16Adapter
         */
        public $psr16adapter;
        /**
         * @var string
         */
        private $cacheEngine = 'Files';
        /**
         * @var string
         */
        private $cachePath;
        /**
         * @var float|int
         */
        private $cachePurgeCycle;

        /**
         * BeModelCacheInstance constructor.
         *
         * @param  string  $cacheEngine
         * @param  string  $cachePath
         * @param  int     $cachePurgeCycle
         *
         * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverCheckException
         * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverException
         * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverNotFoundException
         * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException
         * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidConfigurationException
         * @throws \Phpfastcache\Exceptions\PhpfastcacheLogicException
         * @throws \ReflectionException
         */
        private function __construct()
        {
            $BeModelCacheHelper = new BeModelCacheHelper();
            $this->cacheEngine = $BeModelCacheHelper->getCacheEngine();
            $this->cachePath = $BeModelCacheHelper->getCachePath();
            CacheManager::setDefaultConfig(
                new ConfigurationOption(
                    [
                        'path' => $this->cachePath
                    ]
                )
            );
            $this->psr16adapter = new Psr16Adapter($this->cacheEngine);
            $this->cachePurgeCycle = $BeModelCacheHelper->getCachePurgeCycle();
            $BeModelCacheHelper->cachePurge($this->psr16adapter);
        }

        /**
         * @return object
         * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverCheckException
         * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverException
         * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverNotFoundException
         * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException
         * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidConfigurationException
         * @throws \Phpfastcache\Exceptions\PhpfastcacheLogicException
         * @throws \ReflectionException
         */
        public static function getInstance(): object
        {
            self::$instance == null
                && self::$instance = new BeModelCacheInstance();
            return self::$instance;
        }
    }
}
