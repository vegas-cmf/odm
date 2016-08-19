<?php
/**
 * This file is part of Vegas package
 *
 * @author Radosław Fąfara <radek@amsterdamstandard.com>
 * @copyright Amsterdam Standard Sp. Z o.o.
 * @homepage http://cmf.vegas
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vegas\ODM\Cache\Backend;

use Phalcon\Cache\Exception;
use Phalcon\Cache\FrontendInterface;
use Phalcon\Cache\Backend;
use Phalcon\Cache\BackendInterface;

/**
 * Vegas\ODM\Cache\Backend\ArrayCache
 *
 * This backend uses a database as cache backend
 *
 * @package Phalcon\Cache\Backend
 * @property \Phalcon\Cache\FrontendInterface _frontend
 */
class ArrayCache extends Backend implements BackendInterface
{
    use Backend\Prefixable;

    /**
     * @var array
     */
    private static $cache = [];

    /**
     * {@inheritdoc}
     *
     * @param  FrontendInterface $frontend
     * @param  array             $options
     * @throws Exception
     */
    public function __construct(FrontendInterface $frontend, array $options)
    {
        parent::__construct($frontend, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string     $keyName
     * @param  integer    $lifetime
     * @return mixed|null
     */
    public function get($keyName, $lifetime = null)
    {
        $prefixedKey    = $this->getPrefixedIdentifier($keyName);
        $this->_lastKey = $prefixedKey;

        if (!array_key_exists($prefixedKey, self::$cache)) {
            return null;
        }

        $cache = self::$cache[$prefixedKey];

        /** @var \Phalcon\Cache\FrontendInterface $frontend */
        $frontend = $this->getFrontend();

        // Remove the cache if expired
        if ($cache['lifetime'] < time()) {
            unset(self::$cache[$prefixedKey]);

            return null;
        }

        return $frontend->afterRetrieve($cache['data']);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $keyName
     * @param  string $content
     * @param  int    $lifetime
     * @param  bool   $stopBuffer
     * @return bool
     *
     * @throws Exception
     */
    public function save($keyName = null, $content = null, $lifetime = null, $stopBuffer = true)
    {
        if ($keyName === null) {
            $prefixedKey = $this->_lastKey;
        } else {
            $prefixedKey = $this->getPrefixedIdentifier($keyName);
        }

        if (!$prefixedKey) {
            throw new Exception('The cache must be started first');
        }

        /** @var \Phalcon\Cache\FrontendInterface $frontend */
        $frontend = $this->getFrontend();

        if ($content === null) {
            $cachedContent = $frontend->getContent();
        } else {
            $cachedContent = $content;
        }

        if (null === $lifetime) {
            $lifetime = $frontend->getLifetime();
        }

        $lifetime = time() + $lifetime;

        self::$cache[$prefixedKey] = [
            'data' => $frontend->beforeStore($cachedContent),
            'lifetime' => $lifetime
        ];

        if ($stopBuffer) {
            $frontend->stop();
        }

        if ($frontend->isBuffering()) {
            echo $content;
        }

        $this->_started = false;

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $keyName
     * @return bool
     */
    public function delete($keyName)
    {
        $prefixedKey = $this->getPrefixedIdentifier($keyName);

        if (!array_key_exists($prefixedKey, self::$cache)) {
            return false;
        }

        unset(self::$cache[$prefixedKey]);

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $prefix
     * @return array
     */
    public function queryKeys($prefix = null)
    {
        if (!$prefix) {
            $prefix = $this->_prefix;
        } else {
            $prefix = $this->getPrefixedIdentifier($prefix);
        }

        $keys = array_filter(array_keys(self::$cache), function($key) use ($prefix) {
            return empty($prefix) || strpos($key, $prefix) === 0;
        });

        return array_map(function($key) use ($prefix) {
            return !empty($prefix) ? str_replace($prefix, '', $key) : $key;
        }, $keys);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $keyName
     * @param  string  $lifetime
     * @return bool
     */
    public function exists($keyName = null, $lifetime = null)
    {
        $prefixedKey = $this->getPrefixedIdentifier($keyName);

        if (!array_key_exists($prefixedKey, self::$cache)) {
            return false;
        }

        $cache = self::$cache[$prefixedKey];

        // Remove the cache if expired
        if ($cache['lifetime'] < time()) {
            unset(self::$cache[$prefixedKey]);

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function flush()
    {
        self::$cache = [];

        return true;
    }
}