<?php

declare(strict_types=1);

namespace webforyou\be\modelWithCache {

    !defined('ABSPATH') && exit;

    /**
     * Class BeRestWithCache
     *
     * @package webforyou\be\modelWithCache
     */
    class BeRestWithCache
    {

        /**
         * BeModelPostWithCache constructor.
         */
        function __construct(
            object $BeModelCacheInstance,
            object $BeModelCacheHelper
        ) {
            $this->cacheInstance = $BeModelCacheInstance->psr16adapter;
            $this->cacheHelper = $BeModelCacheHelper;
            $this->addFilter(
                'rest_pre_echo_response',
                function ($response, $object, $request) {
                    return $this->restPreEchoResponse($response, $object, $request);
                },
                10,
                3
            );
            $this->addAction(
                'save_post',
                [&$this, 'handleUpdatePost'],
                10,
                3
            );
        }

        /**
         * @param  string  $name
         * @param  array   $params
         * @param  int     $priority
         * @param  int     $acceptedArgs
         */
        public function addFilter(
            string $name,
            object $function,
            int $priority = 10,
            int $acceptedArgs = 1
        ): void {
            add_filter(
                $name,
                $function,
                $priority,
                $acceptedArgs
            );
        }

        /**
         * @param $key
         */
        public function deleteCacheInstance($key): void
        {
            $this->cacheInstance->delete($key);
        }

        /**
         * @param  string  $name
         * @param  array   $params
         * @param  int     $priority
         * @param  int     $acceptedArgs
         */
        public function addAction(
            string $name,
            array $params,
            int $priority = 10,
            int $acceptedArgs = 1
        ): void {
            add_action(
                $name,
                $params,
                $priority,
                $acceptedArgs
            );
        }

        /**
         * @param  string  $key
         */
        public function addKey(string $key): void
        {
            $keys = $this->getCacheInstance(
                $this->cacheHelper::CACHE_FRAGMENT_REST
            );
            $keys == null && $keys = [];
            if (!isset($keys[$key])) {
                $keys[] = $key;
                $this->setCacheInstance(
                    $this->cacheHelper::CACHE_FRAGMENT_REST,
                    $keys
                );
            }
        }

        /**
         * @param  array  $params
         *
         * @return string
         */
        public function getCacheKey(): string
        {
            return md5($_SERVER['REQUEST_URI']);
        }

        /**
         * @param  string  $key
         *
         * @return array|null
         */
        public function getCacheInstance(string $key)
        {
            return $this->cacheInstance->get($key);
        }

        /**
         * @param  string  $key
         * @param          $response
         *
         * @return bool
         */
        public function setCacheInstance(
            string $key,
            $response
        ): bool {
            $ttl = 3600;
            if (isset($this->params[$this->cacheHelper::PARAM_TTL])) {
                $ttl = $this->params[$this->cacheHelper::PARAM_TTL];
            }
            return $this->cacheInstance->set(
                $key,
                $response,
                $ttl
            );
        }

        /**
         * @param  array  $response
         * @param  object  $object
         * @param  array  $request
         * 
         * @return array
         */
        public function restPreEchoResponse($json, $object, $request): array
        {
            $key = $this->getCacheKey();
            $response = $this->getCacheInstance($key);
            if (!$response) {
                $response = $json;
                $this->setCacheInstance($key, $json);
                $this->addKey($key);
            }
            return $response;
        }

        public function handleUpdatePost(): void
        {
            $keys = $this->getCacheInstance($this->cacheHelper::CACHE_FRAGMENT_REST);
            if (is_array($keys)) {
                $this->deleteCacheInstance($this->cacheHelper::CACHE_FRAGMENT_REST);
                foreach ($keys as $key) $this->deleteCacheInstance($key);
            }
        }
    }
}
