<?php

/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\Tests\ODM;

use Fixtures\Collection\_PM_\Test\Proxy;
use Fixtures\Collection\Status;
use Fixtures\Collection\Tag;
use Phalcon\Di;
use Phalcon\DiInterface;
use Vegas\ODM\Collection;
use Vegas\ODM\Proxy\Generator\Helper;
use Vegas\ODM\ProxyBuilder;
use Fixtures\Collection\Foo;


class ProxyTest extends \PHPUnit_Framework_TestCase
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

    public function testMapByNonCollectionInstanceClass()
    {
        $status = new Status();
        $status->setName('status1');
        $status->save();

        $tag = new Tag();
        $tag->setName('tag1');
        $tag->setStatus($status);
        $tag->save();

        /** @var Tag $tag */
        $tag = Tag::findFirst();
        $this->assertEquals('status1', $tag->getStatus()->getName());
        $this->assertEquals('status1', $tag->getStatus()->getName());
    }
}