<?php
error_reporting(E_ALL);
//Test Suite bootstrap
include __DIR__ . "/../vendor/autoload.php";

use Phalcon\Loader;

define('TESTS_ROOT_DIR', dirname(__FILE__));
define('APP_ROOT', dirname(__FILE__) . '/tests/fixures');

$configArray = require_once TESTS_ROOT_DIR . '/config.php';

$_SERVER['HTTP_HOST'] = 'vegas.dev';
$_SERVER['REQUEST_URI'] = '/';

$config = new \Phalcon\Config($configArray);
$di = new Phalcon\DI\FactoryDefault();

$loader = new Loader();
$loader->registerNamespaces(
    [
        "Fixtures\\Collection"    => __DIR__ . "/fixtures/Collection/"
    ]
);
$loader->register();

$di->set('collectionManager', function() use ($di) {
    return new \Phalcon\Mvc\Collection\Manager();
}, true);
$di->set('mongo', function() use ($config) {
    $mongo = new \MongoClient();
    return $mongo->selectDb($config->mongo->db);
}, true);

$di->set('odmMappingCache', function() use ($di, $config) {
    $frontCacheClass = $config->mapping->cache->frontend->driverClass;
    $frontCache = new $frontCacheClass(
        $config->mapping->cache->frontend->parameters->toArray()
    );
    $backCacheClass = $config->mapping->cache->backend->driverClass;
    $cache = new $backCacheClass(
        $frontCache,
        $config->mapping->cache->backend->parameters->toArray()
    );

    return $cache;
}, true);

Phalcon\DI::setDefault($di);