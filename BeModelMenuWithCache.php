<?php

declare(strict_types=1);

namespace webforyou\be\modelWithCache {

    /**
     * No direct access
     */
    !defined('ABSPATH') && exit;

    /**
     * Class BeModelMenuWithCache
     *
     * @package webforyou\be\modelWithCache
     */
    class BeModelMenuWithCache
    {

        /**
         * @var array
         */
        private $navMenuLocations = [];

        /**
         * @var int
         */
        private $ttl = 2592000;

        /**
         * @var array
         */
        private $params = [];

        /**
         * @var object
         */
        private $cacheInstance;

        /**
         * @var object
         */
        private $cacheHelper;

        /**
         * @var array
         */
        private $keys = [];

        /**
         * BeModelMenuWithCache constructor.
         *
         * @param  object  $BeModelCacheInstance
         * @param  object  $BeModelCacheHelper
         */
        function __construct(object $BeModelCacheInstance, object $BeModelCacheHelper)
        {
            $this->cacheInstance = $BeModelCacheInstance->psr16adapter;
            $this->cacheHelper = $BeModelCacheHelper;
            $this->navMenuLocations = $this->getNavMenuLocations();
            $this->addAction('beModelMenu', array(&$this, null));
            $this->addAction(
                'wp_update_nav_menu',
                array(&$this, 'handleUpdateNavMenu')
            );
        }

        /**
         * @return array
         */
        public function getNavMenuLocations(): array
        {
            return get_nav_menu_locations();
        }

        /**
         * @param  string  $name
         * @param  array   $params
         */
        public function addAction(string $name, array $params): void
        {
            add_action($name, $params);
        }

        /**
         * @param  int  $navId
         */
        public function handleUpdateNavMenu(int $navId): void
        {
            $this->cacheHelper->cleanCacheById(
                $this->cacheInstance,
                $this->cacheHelper::CACHE_FRAGMENT_MENU,
                $navId
            );
        }

        /**
         * @param  string  $name
         * @param  array   $params
         *
         * @return array|null
         */
        public function wpGetNavMenuItems(
            string $name,
            array $params = []
        ): ?array {
            $this->setParams($params);
            $key = $this->cacheHelper->getKey(
                $name,
                $params
            );
            $this->keys[] = $key;
            $response = $this->getCacheInstance($key);
            if (!$this->params[$this->cacheHelper::PARAM_TTL] || !$response) {
                $navId = $this->getNavMenuLocation($name);
                if (is_int($navId)) {
                    $response = [];
                    $this->addCacheKey($key, $navId);
                    $items = $this->getNavMenuItems($navId);
                    foreach ($items as $item) {
                        $response[] = array_filter($item->to_array());
                    }
                    $this->setCacheInstance($key, $response);
                }
            }
            return $response;
        }

        /**
         * @param  array  $params
         */
        private function setParams(array $params = []): void
        {
            extract($params, EXTR_SKIP);
            $this->params = $params;
            $this->params[$this->cacheHelper::PARAM_TTL]
                = ((isset($paras[$this->cacheHelper::PARAM_TTL])) ?
                    $params[$this->cacheHelper::PARAM_TTL] : $this->ttl);
        }

        /**
         * @param  string  $key
         *
         * @return array|null
         */
        public function getCacheInstance(string $key): ?array
        {
            return $this->cacheInstance->get($key);
        }

        /**
         * @param  string  $key
         * @param          $response
         *
         * @return bool
         */
        public function setCacheInstance(string $key, $response): bool
        {
            return $this->cacheInstance->set(
                $key,
                $response,
                $this->params[$this->cacheHelper::PARAM_TTL]
            );
        }

        /**
         * @param  string  $name
         *
         * @return int|null
         */
        public function getNavMenuLocation(string $name): ?int
        {
            if (isset($this->navMenuLocations[$name])) {
                return $this->navMenuLocations[$name];
            }
            return null;
        }

        /**
         * @param  string  $key
         * @param  int     $navId
         */
        public function addCacheKey(string $key, int $navId): void
        {
            $this->cacheHelper->addKey(
                $this->cacheInstance,
                $this->cacheHelper::CACHE_FRAGMENT_MENU,
                $key,
                $navId
            );
        }

        /**
         * @param  int  $navId
         *
         * @return array|null
         */
        public function getNavMenuItems(int $navId): ?array
        {
            return wp_get_nav_menu_items($navId);
        }
    }
}
