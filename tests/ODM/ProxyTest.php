<?php

/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\Tests\ODM;

use Vegas\ODM\Proxy;

class Fooo
{
    private $foo;

    public function __construct()
    {
        //sleep(5);
    }

    public function setFoo($foo)
    {
        $this->foo = (string) $foo;
    }

    public function getFoo()
    {
        return $this->foo;
    }
}

class ProxyTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function testShouldCheckClassOnReturn()
    {
        $proxy = Proxy::getLazyLoadingClass('Vegas\Tests\ODM\Fooo');
        $this->assertInstanceOf(\Vegas\Tests\ODM\Fooo::class, $proxy);

        $proxy->setFoo('test');
        $this->assertEquals('test', $proxy->getFoo());
    }

}