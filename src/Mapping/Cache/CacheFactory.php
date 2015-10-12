<?php
/**
 * This file is part of Vegas package
 *
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @copyright Amsterdam Standard Sp. Z o.o.
 * @homepage http://cmf.vegas
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vegas\ODM\Mapping\Cache;

use Vegas\ODM\Mapping\Cache\Exception\InvalidBackendCacheException;
use Vegas\ODM\Mapping\Cache\Exception\InvalidFrontendCacheException;

/**
 * Class CacheFactory
 * @package Vegas\ODM\Mapping\Cache
 */
class CacheFactory
{
    const FRONTEND_CACHE = 'frontend';

    const BACKEND_CACHE = 'backend';

    /**
     * @param array $configuration
     * @return object
     * @throws InvalidBackendCacheException
     * @throws InvalidFrontendCacheException
     */
    public function createCache(array $configuration)
    {
        if (!isset($configuration[self::FRONTEND_CACHE])) {
            throw new InvalidFrontendCacheException();
        }
        if (!isset($configuration[self::BACKEND_CACHE])) {
            throw new InvalidBackendCacheException();
        }
        $frontendCache = $this->instantiateFrontendCacheAdapter($configuration[self::FRONTEND_CACHE]);
        $backendCache = $this->instantiateBackendCacheAdapter($frontendCache, $configuration[self::BACKEND_CACHE]);

        return $backendCache;
    }

    /**
     * @param array $parameters
     * @return object
     */
    protected function instantiateFrontendCacheAdapter(array $parameters)
    {
        $reflectionClass = new \ReflectionClass($parameters['driverClass']);
        return $reflectionClass->newInstance(isset($parameters['parameters']) ? $parameters['parameters'] : []);
    }

    /**
     * @param $frontendCache
     * @param array $parameters
     * @return object
     */
    protected function instantiateBackendCacheAdapter($frontendCache, array $parameters)
    {
        $reflectionClass = new \ReflectionClass($parameters['driverClass']);
        return $reflectionClass->newInstance(
            $frontendCache, isset($parameters['parameters']) ? $parameters['parameters'] : []
        );
    }
}