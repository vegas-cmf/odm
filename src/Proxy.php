<?php
/**
 * @author Mateusz Aniolek <mateusz.aniolek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\ODM;
use Phalcon\DiInterface;
use Vegas\ODM\Proxy\LazyLoadingGhostFactory;

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
     * @param DiInterface $di
     * @return \ProxyManager\Proxy\LazyLoadingInterface
     */
    public static function getLazyLoadingClass($className, DiInterface $di)
    {
        $factory = self::getFactory();
        $factory->setDI($di);

        $proxy = $factory->createProxy(
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