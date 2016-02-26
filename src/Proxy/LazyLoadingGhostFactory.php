<?php
/**
 * @author Mateusz Aniolek <mateusz.aniolek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\ODM\Proxy;

use Phalcon\DiInterface;
use Vegas\ODM\Proxy\Generator\LazyLoadingGhostGenerator;

/**
 * Class LazyLoadingGhostFactory
 * @package Vegas\ODM\Proxy
 */
class LazyLoadingGhostFactory extends AbstractLazyFactory
{
    /**
     * @var \ProxyManager\ProxyGenerator\LazyLoadingGhostGenerator|null
     */
    private $generator;

    /**
     * {@inheritDoc}
     */
    protected function getGenerator()
    {
        return $this->generator ?: $this->generator = new LazyLoadingGhostGenerator;
    }

    /**
     * @param DiInterface $di
     */
    public function setDI(DiInterface $di)
    {
        $this->di = $di;
    }
}