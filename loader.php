<?php

/**
 * Plugin Name: BeModelAndShortCodeWithCache
 * Plugin URI: @TODO
 * Description: @TODO
 * Version: 0.2
 * Author: Davide Longo
 * Author URI: http://www.davidelongo.net/
 */

if (!defined('DS')) {
    define('DS', '/');
}

// Composer autoload
require_once('vendor/autoload.php');

/**
 * Class $BeModelCacheHelper
 */
require_once('BeModelCacheHelper.php');
$BeModelCacheHelper = new webforyou\be\modelWithCache\BeModelCacheHelper();

/**
 * Class BeModelCacheInstance
 */
require_once('BeModelCacheInstance.php');
$BeModelCacheInstance
    = webforyou\be\modelWithCache\BeModelCacheInstance::getInstance();

/**
 * Class $BeModelCacheInit
 */
require_once('BeModelCacheInit.php');
register_activation_hook(
    __FILE__,
    array(
        'webforyou\be\modelWithCache\BeModelCacheInit',
        'BeModelCacheInitActivation'
    )
);
register_deactivation_hook(
    __FILE__,
    array(
        'webforyou\be\modelWithCache\BeModelCacheInit',
        'BeModelCacheInitDeactivation'
    )
);
register_uninstall_hook(
    __FILE__,
    array(
        'webforyou\be\modelWithCache\BeModelCacheInit',
        'BeModelCacheInitDeactivation'
    )
);

/**
 * Class BeModelMenuWithCache
 */
require_once('BeModelMenuWithCache.php');
$BeModelMenuWithCache = new webforyou\be\modelWithCache\BeModelMenuWithCache(
    $BeModelCacheInstance,
    $BeModelCacheHelper
);

/**
 * Class BeModelPostWithCache
 */
require_once('BeModelPostWithCache.php');
$BeModelPostWithCache = new webforyou\be\modelWithCache\BeModelPostWithCache(
    $BeModelCacheInstance,
    $BeModelCacheHelper
);

/**
 * Class BeShortCodeWithCache
 */
require_once('BeShortCodeWithCache.php');
$BeShortCodeWithCache = new webforyou\be\modelWithCache\BeShortCodeWithCache(
    $BeModelCacheInstance,
    $BeModelCacheHelper
);

/**
 * Class BeModelCacheRest
 */
require_once('BeRestWithCache.php');
$BeRestWithCache = new webforyou\be\modelWithCache\BeRestWithCache(
    $BeModelCacheInstance,
    $BeModelCacheHelper
);
