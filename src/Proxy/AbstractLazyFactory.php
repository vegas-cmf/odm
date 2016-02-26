<?php
/**
 * @author Mateusz Aniolek <mateusz.aniolek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\ODM\Proxy;

use Closure;
use Phalcon\DiInterface;

/**
 * @inheritdoc
 */
abstract class AbstractLazyFactory extends \ProxyManager\Factory\AbstractLazyFactory
{
    /** @var DiInterface $di */
    protected $di;

    /**
     * @inheritdoc
     */
    public function createProxy($className, Closure $initializer)
    {
        $proxyClassName = $this->generateProxy($className);

        return new $proxyClassName($this->di);
    }
}
