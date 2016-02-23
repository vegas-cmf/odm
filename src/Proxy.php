<?php
/**
 * @author Mateusz Aniolek <mateusz.aniolek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\ODM;
use ProxyManager\Factory\LazyLoadingGhostFactory;

/**
 * Class Proxy
 * @package Vegas\ODM
 */
class Proxy
{
    /** @var LazyLoadingGhostFactory $lazyLoadingFactory  */
    private static $lazyLoadingFactory;

    /**
     * @param $className
     * @return \ProxyManager\Proxy\GhostObjectInterface
     */
    public static function getLazyLoadingClass($className)
    {
        $proxy = self::getFactory()->createProxy(
            $className,
            function ($proxy, $method, $parameters, & $initializer) {
                $initializer = null;
                return true;
            }
        );

        return $proxy;
    }

    /**
     * @return LazyLoadingGhostFactory
     */
    public static function getFactory()
    {
        if (self::$lazyLoadingFactory === null) {
            self::$lazyLoadingFactory = new LazyLoadingGhostFactory();
        }

        return self::$lazyLoadingFactory;
    }

}