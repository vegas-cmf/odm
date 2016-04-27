<?php

/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\Tests\ODM;

use Fixtures\Collection\_PM_\Test\Proxy;
use Phalcon\Di;
use Phalcon\DiInterface;
use Vegas\ODM\Collection;
use Vegas\ODM\Proxy\Generator\Helper;
use Vegas\ODM\ProxyBuilder;
use Fixtures\Collection\Foo;


class ProxyBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $di;

    public function testShouldCheckClassOnReturn()
    {
        $proxy = ProxyBuilder::getLazyLoadingClass('Fixtures\Collection\Foo', Di::getDefault());
        $this->assertInstanceOf(Foo::class, $proxy);

        $this->assertInstanceOf(DiInterface::class, ProxyBuilder::getFactory()->getDI());

        $proxy->setFoo('test');
        $this->assertEquals('test', $proxy->getFoo());
    }

    public function testShouldCheckValidUserClassName()
    {
        $proxy = ProxyBuilder::getLazyLoadingClass(Proxy::class, Di::getDefault());
        $this->assertEquals(0, strpos(
            get_class($proxy),
            Helper::PROXY_NAMESPACE . Helper::PROXY_CONST . Helper::getUserClassName(Proxy::class)
        ));
    }
}