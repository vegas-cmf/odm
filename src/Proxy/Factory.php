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

namespace Vegas\ODM\Proxy;

use Phalcon\Di\InjectionAwareInterface;
use Phalcon\DiInterface;
use Vegas\ODM\Proxy\Generator\Helper as ProxyHelper;
use Vegas\ODM\Proxy\Generator\Helper;
use Zend\Code\Generator\ClassGenerator;

/**
 * Class Factory
 * @package Vegas\ODM\Proxy
 */
class Factory implements InjectionAwareInterface
{
    /**
     * @var DiInterface
     */
    protected $di;

    /**
     * @var Generator
     */
    protected $generator;

    /**
     * @var array
     */
    protected $generatedClasses = [];

    /**
     * @param $className
     * @return mixed
     */
    public function createProxy($className, $collection = null)
    {
        $this->generator = $this->generator ?: new Generator;

        $className = $this->generateProxy($className);
        return new $className($this->di);
    }

    /**
     * @param string $proxyClassName
     * @param string $className
     * @return void
     */
    protected function generateProxyClass($proxyClassName, $className)
    {
        $className = ProxyHelper::getUserClassName($className);
        $phpClass  = new ClassGenerator($proxyClassName);

        $this->generator->generate(new \ReflectionClass($className), $phpClass);
        $proxyClassFileName = ProxyHelper::saveClass($phpClass);

        if (file_exists($proxyClassFileName)) {
            require_once $proxyClassFileName;
        }
    }

    protected function generateProxy($className)
    {
        if (isset($this->generatedClasses[$className])) {
            return $this->generatedClasses[$className];
        }

        $proxyClassName  = Helper::getProxyClassName($className);

        if (!class_exists($proxyClassName)) {
            $this->generateProxyClass($proxyClassName, $className);
        }
        $this->generatedClasses[$className] = $proxyClassName;

        return $proxyClassName;
    }

    /**
     * @param DiInterface $di
     */
    public function setDI(DiInterface $di)
    {
        $this->di = $di;
    }

    /**
     * @return DiInterface
     */
    public function getDI()
    {
        return $this->di;
    }
}