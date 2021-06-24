<?php

declare(strict_types=1);

namespace webforyou\be\modelWithCache {

    use Phpfastcache\Exceptions\PhpfastcacheDriverCheckException;
    use Phpfastcache\Exceptions\PhpfastcacheDriverException;
    use Phpfastcache\Exceptions\PhpfastcacheDriverNotFoundException;
    use Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException;
    use Phpfastcache\Exceptions\PhpfastcacheInvalidConfigurationException;
    use Phpfastcache\Exceptions\PhpfastcacheLogicException;
    use ReflectionException as ReflectionExceptionAlias;

    /**
     * No direct access
     */
    !defined('ABSPATH') && exit;

    /**
     * Class BeShortcodeWithCache
     *
     * @package webforyou\be\shortcodeWithCache
     */
    class BeShortCodeWithCache
    {

        /**
         * @var int
         */
        private $ttl = 2592000;

        /**
         * @var array
         */
        private $params = [];

        /**
         * @var int
         */
        private $templateFile = 0;

        /**
         * @var string
         */
        private $cacheInstance;

        /**
         * @var string|object
         */
        private $cacheHelper;

        /**
         * BeShortCodeWithCache constructor.
         *
         * @param  object  $BeModelCacheInstance
         * @param  object  $BeModelCacheHelper
         */
        function __construct(
            object $BeModelCacheInstance,
            object $BeModelCacheHelper
        ) {
            $this->cacheInstance = $BeModelCacheInstance->psr16adapter;
            $this->cacheHelper = $BeModelCacheHelper;
            $this->addShortcode();
            $this->addAction(
                'save_post',
                [&$this, 'handleUpdatePost'],
                10,
                3
            );
        }

        public function addShortcode(): void
        {
            add_shortcode(
                'be_shortcode',
                [
                    'webforyou\be\modelWithCache\BeShortCodeWithCache',
                    'shortcode'
                ]
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

        /**
         * @param  array  $params
         *
         * @return string
         * @throws PhpfastcacheDriverCheckException
         * @throws PhpfastcacheDriverException
         * @throws PhpfastcacheDriverNotFoundException
         * @throws PhpfastcacheInvalidArgumentException
         * @throws PhpfastcacheInvalidConfigurationException
         * @throws PhpfastcacheLogicException
         * @throws ReflectionExceptionAlias
         */
        public static function shortcode(array $params): string
        {
            $response = new BeShortCodeWithCache(
                BeModelCacheInstance::getInstance(),
                new BeModelCacheHelper()
            );
            return $response->render($params);
        }

        /**
         * @param  array  $params
         *
         * @return string
         */
        public function render(array $params): string
        {
            $this->setParams($params);
            $key = $this->getCacheKey($params);
            $this->setTemplateFile($this->cacheHelper->getThemeShortCodesDir() .
                DS . $this->getView());
            if (!$this->getTemplateFile()) return '';
            $this->isTemplateChanged($params);
            $response = $this->getCacheInstance($key);
            if (
                (!isset($this->params[$this->cacheHelper::PARAM_TTL])
                    || !$this->params[$this->cacheHelper::PARAM_TTL])
                || !$this->params[$this->cacheHelper::CACHE_FRAGMENT_POST]
                || !$response
            ) {
                ob_start('ob_gzhandler');
                $this->includeTemplateFile(
                    $this->cacheHelper->getThemeShortCodesDir() . DS
                        . $this->getView(),
                    $this->params,
                    $this->getWpQuery()
                );
                $response = ob_get_contents();
                ob_get_clean();
                $response = preg_replace(
                    ['/<!--(.*)-->/Uis', "/[[:blank:]]+/"],
                    ['', ' '],
                    str_replace(["\n", "\r", "\t"], '', $response)
                );
                $this->setCacheInstance($key, $response);
                $this->addKey($key, $params);
            }
            return $response;
        }

        /**
         * @param  array  $params
         *
         * @return string
         */
        public function getCacheKey(array $params): string
        {
            return $this->cacheHelper->getKey(
                $this->cacheHelper::CACHE_FRAGMENT_SHORT_CODE,
                $params
            );
        }

        /**
         * @return mixed
         */
        public function getView()
        {
            return $this->params['view'];
        }

        /**
         * @return bool|int
         */
        public function getTemplateFile()
        {
            if ($this->templateFile && file_exists($this->templateFile)) return $this->templateFile;
            return false;
        }

        /**
         * @param  string  $name
         */
        public function setTemplateFile(string $name): void
        {
            $this->templateFile = $this->setupTemplateFile($name);
        }

        /**
         * @param  array  $params
         */
        public function isTemplateChanged(array $params): void
        {
            $keys = $this->getCacheInstance($this->cacheHelper::CACHE_FRAGMENT_SHORT_CODE);
            $key = $this->getCacheKey($params);
            if (
                isset($keys[$key]) && $this->getTemplateFile()
                && @filemtime($this->getTemplateFile())
                > $keys[$key]['templateTime']
            ) {
                unset($keys[$key]);
                $this->setCacheInstance($this->cacheHelper::CACHE_FRAGMENT_SHORT_CODE, $keys);
                $this->deleteCacheInstance($key);
            }
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
            $ttl = 0;
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
         * @param $key
         */
        public function deleteCacheInstance($key): void
        {
            $this->cacheInstance->delete($key);
        }

        /**
         * @param  string  $name
         * @param  array   $params
         * @param  object  $wp_query
         *
         * @return bool
         */
        public function includeTemplateFile(
            string $name,
            array $params,
            object $wp_query
        ): bool {
            $templateFile = $this->getTemplateFile();
            if ($templateFile && is_array($wp_query->query_vars)) {
                $this->doAction($name);
                extract($wp_query->query_vars, EXTR_SKIP);
                extract($params, EXTR_SKIP);
                @include $templateFile;
                return true;
            }
            return false;
        }

        /**
         * @param  string  $name
         */
        public function doAction(string $name): void
        {
            do_action("get_template_part_{$name}", $name);
        }

        /**
         * @return object|\WP_Query
         */
        public function getWpQuery()
        {
            global $wp_query;
            return $wp_query;
        }

        /**
         * @param  string  $key
         * @param  array   $params
         */
        public function addKey(string $key, array $params): void
        {
            $keys = $this->getCacheInstance(
                $this->cacheHelper::CACHE_FRAGMENT_SHORT_CODE
            );
            $keys == null && $keys = [];
            if (!isset($keys[$key])) {
                $keys[$key] = $params;
                $keys[$key]['templateFile'] = $this->getTemplateFile();
                $keys[$key]['templateTime'] = filemtime(
                    $this->getTemplateFile()
                );
                $this->setCacheInstance(
                    $this->cacheHelper::CACHE_FRAGMENT_SHORT_CODE,
                    $keys
                );
            }
        }

        /**
         * @return array
         */
        public function getParams()
        {
            return $this->params;
        }

        /**
         * @param  array  $params
         */
        public function setParams(array $params = []): void
        {
            extract($params, EXTR_SKIP);
            $this->params = $params;
            $this->params[$this->cacheHelper::CACHE_FRAGMENT_POST]
                = ((isset($params[$this->cacheHelper::CACHE_FRAGMENT_POST])) ?
                    $params[$this->cacheHelper::CACHE_FRAGMENT_POST] : $this->ttl);
        }

        public function handleUpdatePost(): void
        {
            $keys = $this->getCacheInstance($this->cacheHelper::CACHE_FRAGMENT_SHORT_CODE);
            if (is_array($keys)) {
                $this->deleteCacheInstance($this->cacheHelper::CACHE_FRAGMENT_SHORT_CODE);
                foreach ($keys as $key => $params) $this->deleteCacheInstance($key);
            }
        }

        /**
         * @param  string  $name
         *
         * @return string|null
         */
        private function setupTemplateFile(string $name)
        {
            $response = get_template_directory() . DS . "{$name}.php";
            return (file_exists($response)) ? $response : null;
        }
    }
}
