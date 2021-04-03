<?php

declare(strict_types=1);

namespace webforyou\be\modelWithCache {

    !defined('ABSPATH') && exit;

    /**
     * Class BeModelPostWithCache
     *
     * @package webforyou\be\modelWithCache
     */
    class BeModelPostWithCache
    {

        /**
         * @var int
         */
        private $ttl = 3600;

        /**
         * @var array
         */
        private $params = [];

        /**
         * @var string
         */
        private $cacheInstance;

        /**
         * @var string|object
         */
        private $cacheHelper;

        /**
         * BeModelPostWithCache constructor.
         */
        function __construct(
            object $BeModelCacheInstance,
            object $BeModelCacheHelper
        ) {
            $this->cacheInstance = $BeModelCacheInstance->psr16adapter;
            $this->cacheHelper = $BeModelCacheHelper;
            $this->addAction(
                'beModelPost',
                array(&$this, null)
            );
            $this->addAction(
                'save_post',
                array(&$this, 'handleUpdatePost'),
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

        public function handleUpdatePost(): void
        {
            $keys = $this->getCacheInstance(
                $this->cacheHelper::CACHE_FRAGMENT_POST
            );
            if (is_array($keys)) {
                $this->deleteCacheInstance(
                    $this->cacheHelper::CACHE_FRAGMENT_POST
                );
                foreach ($keys as $key => $params) {
                    $this->deleteCacheInstance($key);
                    if (
                        !isset($params[$this->cacheHelper::PARAM_LAZY_LOAD])
                        || !$params[$this->cacheHelper::PARAM_LAZY_LOAD]
                    ) {
                        $this->getPosts($params);
                    }
                }
            }
        }

        /**
         * @param  string  $key
         */
        public function getCacheInstance(
            string $key
        ) {
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
            return $this->cacheInstance->set(
                $key,
                $response,
                $this->params[$this->cacheHelper::PARAM_TTL]
            );
        }

        /**
         * @param $key
         */
        public function deleteCacheInstance(
            $key
        ): void {
            $this->cacheInstance->delete($key);
        }

        /**
         * @param  array  $params
         *
         * @return array|null
         */
        public function getPosts(
            array $params = []
        ): ?array {
            $this->setParams($params);
            $key = $this->cacheHelper->getKey(
                $this->cacheHelper::CACHE_FRAGMENT_POST,
                $params
            );
            $response = $this->getCacheInstance($key);
            if (
                !$this->getParams($this->cacheHelper::PARAM_TTL)
                || !$response
            ) {
                $response = $this->loadPosts($params, $key);
                $this->addCacheKey($key, $params);
                $this->setCacheInstance($key, $response);
            }
            return $response;
        }

        public function getParams(
            string $param
        ) {
            return $this->params[$param];
        }

        /**
         * @param  array  $params
         */
        public function setParams(
            array $params = []
        ): void {
            extract($params, EXTR_SKIP);
            $this->params = $params;
            $this->params[$this->cacheHelper::PARAM_TTL]
                = ((isset($params[$this->cacheHelper::PARAM_TTL]))
                    ?
                    $params[$this->cacheHelper::PARAM_TTL]
                    : $this->ttl);
        }

        /**
         * @param  array   $params
         * @param  string  $key
         *
         * @return array
         */
        public function loadPosts(
            array $params,
            string $key
        ): array {
            $response = [];
            $wpQuery = new \WP_Query($params);
            while ($wpQuery->have_posts()) :
                $wpQuery->the_post();
                $post = array_filter(get_post(get_the_ID())->to_array());
                $response[$post['ID']] = $post;
                $response[$post['ID']]['cacheKey'] = $key;
                $response[$post['ID']]['featured_image'] = $this->getFeatureImage($post['ID']);
                $response[$post['ID']]['permalink'] = get_permalink($post['ID']);
                $response[$post['ID']]['meta'] = get_post_meta($post['ID']);
                $response[$post['ID']]['custom'] = get_post_custom($post['ID']);
            endwhile;
            return $response;
        }

        /**
         * @param   int $postId
         * 
         * @return array
         */
        public function getFeatureImage(
            int $postId
        ): array {
            $response = [];
            $intermediate_image_sizes = get_intermediate_image_sizes();
            $intermediate_image_sizes[] = 'full';
            $reponse['featured_image'] = [];
            foreach ($intermediate_image_sizes as $image_size) {
                $response[$image_size] = get_the_post_thumbnail_url($postId, $image_size);
            }
            return $response;
        }

        /**
         * @param  string  $key
         * @param  array   $params
         */
        public function addCacheKey(
            string $key,
            array $params
        ): void {
            $this->cacheHelper->addKey(
                $this->cacheInstance,
                $this->cacheHelper::CACHE_FRAGMENT_POST,
                $key,
                $params
            );
        }

        /**
         * @param  array  $params
         */
        public function flushQuery(
            array $params = []
        ): void {
            $key = $this->cacheHelper->getKey(
                $this->cacheHelper::CACHE_FRAGMENT_POST,
                $params
            );
            $this->deleteCacheInstance($key);
        }
    }
}
