<?php

declare(strict_types=1);

namespace webforyou\be\modelWithCache {

    /**
     * No direct access
     */
    !defined('ABSPATH') && exit;

    class BeModelCacheHelper
    {

        const CACHE_ENGINE = 'bmsCacheEngine';
        const CACHE_PATH = 'bmsCachePath';
        const CACHE_PURGE_CYCLE = 'bmsCachePurgeCycle';
        const LAST_PURGE = 'bmsLastPurge';
        const CACHE_FRAGMENT_MENU = 'bmsCacheFragmentMenu';
        const CACHE_FRAGMENT_POST = 'bmsCacheFragmentPost';
        const CACHE_FRAGMENT_SHORT_CODE = 'bmsCacheFragmentShortCode';
        const CACHE_FRAGMENT_REST = 'bmsCacheFragmentRest';
        const THEME_SHORT_CODES_DIR = 'shortcode';
        const PARAM_TTL = 'ttl';
        const PARAM_LAZY_LOAD = 'lazyLoad';
        const DEFAULT_CACHE_ENGINE = 'Files';
        const DEFAULT_THEME_SHORT_CODES_DIR = 'shortcode';

        /**
         * @var string
         */
        private $mobileUserAgents = "/(android|getCachePurgeCycleavantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up.browser|up.link|webos|wos)/i";

        /**
         * @return string
         */
        public function getCacheEngine(): string
        {
            $response = $this->getOption(self::CACHE_ENGINE);
            !$response && $response = self::DEFAULT_CACHE_ENGINE;
            return $response;
        }

        /**
         * @param  string  $name
         *
         * @return bool
         */
        public function getOption(string $name)
        {
            return get_option($name);
        }

        /**
         * @return string
         */
        public function getCachePath(): string
        {
            $response = $this->getOption(self::CACHE_PATH);
            !$response && $response = $this->defaultCacheDir();
            return $response;
        }

        /**
         * @return string
         */
        private function defaultCacheDir(): string
        {
            return $this->getDocumentRoot() . DS . 'wp-content' . DS . 'be-cache';
        }

        /**
         * @return string
         */
        public function getDocumentRoot(): string
        {
            return $_SERVER['DOCUMENT_ROOT'];
        }

        /**
         * @return string
         */
        public function getThemeShortCodesDir(): string
        {
            $response = $this->getOption(self::THEME_SHORT_CODES_DIR);
            !$response && $response = self::DEFAULT_THEME_SHORT_CODES_DIR;
            return $response;
        }

        /**
         * @param  object  $cacheInstance
         *
         * @return bool
         */
        public function cachePurge(object $cacheInstance): bool
        {
            $now = $this->getTime();
            if ($now > ($this->getOption(self::LAST_PURGE)
                + $this->getCachePurgeCycle())) {
                $this->clearCacheInstance($cacheInstance);
                $this->updateOption(self::LAST_PURGE, $now);
                return true;
            }
            return false;
        }

        /**
         * @return int
         */
        public function getTime(): int
        {
            return time();
        }

        /**
         * @return int
         */
        public function getCachePurgeCycle(): int
        {
            $response = $this->getOption(self::CACHE_PURGE_CYCLE);
            !$response && $response = 60 * 60 * 24;
            return $response;
        }

        /**
         * @param  object  $cacheInstance
         */
        public function clearCacheInstance(object $cacheInstance)
        {
            return $cacheInstance->clear();
        }

        /**
         * @param  string  $name
         * @param          $value
         *
         * @return bool
         */
        public function updateOption(string $name, $value)
        {
            return update_option($name, $value);
        }

        /**
         * @param  string  $name
         * @param  array   $params
         *
         * @return string
         */
        public function getKey(
            string $name,
            array $params
        ): string {
            $response = $this->getUniqueId(
                $this->getHttpHost() . $this->getRequestUri() .
                    (($this->isMobile()) ? true : false) . $name . $this->toString(
                        $params
                    )
            );
            return $response;
        }

        /**
         * @param  string  $value
         *
         * @return string
         */
        private function getUniqueId(string $value): string
        {
            return md5($value);
        }

        /**
         * @return mixed
         */
        public function getHttpHost(): string
        {
            return $_SERVER['HTTP_HOST'];
        }

        /**
         * @return mixed
         */
        public function getRequestUri(): string
        {
            return $_SERVER['REQUEST_URI'];
        }

        /**
         * @return bool
         */
        public function isMobile(): bool
        {
            return (bool)preg_match(
                $this->mobileUserAgents,
                $this->getHttpUserAgent()
            );
        }

        /**
         * @return string
         */
        public function getHttpUserAgent(): string
        {
            return $_SERVER['HTTP_USER_AGENT'];
        }

        /**
         * @param  array  $params
         *
         * @return string
         */
        private function toString(array $params): string
        {
            return serialize($params);
        }

        /**
         * @param  object  $cacheInstance
         * @param  string  $name
         * @param  string  $key
         * @param          $value
         *
         * @return bool
         */
        public function addKey(
            object $cacheInstance,
            string $name,
            string $key,
            $value
        ): bool {
            $keys = $this->getCacheInstance($cacheInstance, $name);
            $keys == null && $keys = [];
            if (!isset($keys[$key])) {
                $keys[$key] = $value;
                $this->setCacheInstance($cacheInstance, $name, $keys);
                return true;
            }
            return false;
        }

        /**
         * @param  object  $cacheInstance
         * @param  string  $name
         *
         * @return array|null
         */
        public function getCacheInstance(object $cacheInstance, string $name): ?array
        {
            return $cacheInstance->get($name);
        }

        /**
         * @param  object  $cacheInstance
         * @param  string  $name
         * @param  array   $keys
         *
         * @return bool|null
         */
        public function setCacheInstance(
            object $cacheInstance,
            string $name,
            array $keys
        ): ?bool {
            return $cacheInstance->set($name, $keys);
        }

        /**
         * @param  object  $cacheInstance
         * @param  string  $name
         * @param  int     $id
         *
         * @return bool
         */
        public function cleanCacheById(
            object $cacheInstance,
            string $name,
            int $id
        ): bool {
            $keys = $this->getCacheInstance($cacheInstance, $name);
            if (is_array($keys)) {
                foreach ($this->getCacheInstance($cacheInstance, $name) as $key =>
                    $_id) {
                    if ($id === $_id) {
                        $this->deleteCacheInstance(
                            $cacheInstance,
                            $key
                        );
                        return true;
                    }
                }
            }
            return false;
        }

        /**
         * @param  object  $cacheInstance
         * @param  string  $key
         *
         * @return bool|null
         */
        public function deleteCacheInstance(object $cacheInstance, string $key): ?bool
        {
            return $cacheInstance->delete($key);
        }

        /**
         * @param  object  $cacheInstance
         */
        public function flushCache(object $cacheInstance): void
        {
            $this->clearCacheInstance($cacheInstance);
        }
    }
}
