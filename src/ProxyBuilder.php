<?php
/**
 * This file is part of Vegas package
 *
 * @author Mateusz Aniołek <mateusz.aniolek@amsterdam-standard.pl>
 * @copyright Amsterdam Standard Sp. Z o.o.
 * @homepage http://vegas-cmf.github.io
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vegas\ODM;

use Phalcon\DiInterface;
use Vegas\ODM\Proxy\Factory;

/**
 * Class ProxyBuilder
 * @package Vegas\ODM
 */
class ProxyBuilder
{
    /** @var Factory $proxyFactory */
    private static $proxyFactory;

    /**
     * @param $className
     * @param DiInterface $di
     * @return mixed
     */
    public static function getLazyLoadingClass($className, DiInterface $di)
    {
        $factory = self::getFactory();
        $factory->setDI($di);

        return $factory->createProxy($className);
    }

    /**
     * Assigns all collection property values to proxy instance
     * @param $proxyClass
     * @param $collection
     */
    public static function assignProxyValues(& $proxyClass, $collection)
    {
        $reflection = new \ReflectionClass($collection);
        $properties = $reflection->getProperties();
        foreach($properties as $property) {
            if($property->isPrivate() || $property->isProtected()) {
                $property->setAccessible(true);
            }
            $proxyClass->{$property->getName()} = $property->getValue($collection);
        }
    }

    /**
     * @return Factory
     */
    public static function getFactory()
    {
        if (self::$proxyFactory === null) {
            self::$proxyFactory = new Factory();
        }

        return self::$proxyFactory;
    }

}