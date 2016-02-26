<?php

/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\Tests\ODM;

use Fixtures\Collection\Category;
use Fixtures\Collection\Product;
use Phalcon\Di;
use Vegas\ODM\Collection;
use Vegas\ODM\Proxy;
use Fixtures\Collection\Foo;


class ProxyTest extends \PHPUnit_Framework_TestCase
{
    protected $di;

    public function testShouldCheckClassOnReturn()
    {
        $proxy = Proxy::getLazyLoadingClass('Fixtures\Collection\Foo', Di::getDefault());
        $this->assertInstanceOf(Foo::class, $proxy);

        $proxy->setFoo('test');
        $this->assertEquals('test', $proxy->getFoo());
    }

    public function testBenchmark()
    {
        $startTime = microtime(true);

        $fooProxies = $this->getFooInstances();

//        var_dump('time after 1000 instantiations: ' . (microtime(true) - $startTime));
//
//        echo $fooProxies[0]->getFoo() . "\n";
//
//        var_dump('time after single call to doFoo: ' . (microtime(true) - $startTime));

        $proxy = Proxy::getLazyLoadingClass('Fixtures\Collection\Category', Di::getDefault());
        $proxy->setCategory(null);
        $proxy->setId(new \MongoId);
        $proxy->setName('Root category');
        $proxy->setDesc('Lorem ipsum dolor sit amet');
        //$proxy->save();


    }

    public function testOrderBenchmark()
    {
        $startTime = microtime(true);
        $this->setupDummyData();

        var_dump('Time after 1000 instantiations: ' . (microtime(true) - $startTime));

        $startTime = microtime(true);

        /** @var Product[] $products */
        $products = Product::find();

        foreach($products as $product) {

        }

        var_dump('time after call to all products: ' . (microtime(true) - $startTime));
    }

    private function getFooInstances($numberOfInterations = 1000)
    {
        $proxies = [];
        for ($i = 0; $i <= $numberOfInterations; $i++) {
            $proxy = Proxy::getLazyLoadingClass('Fixtures\Collection\Foo', Di::getDefault());
            $proxy->setFoo('test' . $i);

            $proxies[] = $proxy;
        }

        return $proxies;
    }


    private function setupDummyData()
    {
        $productProxies = [];
        $categoryProxies = [];

        $productProxy = Proxy::getLazyLoadingClass('Fixtures\Collection\Product', Di::getDefault());

        /** @var Category $proxy */
        $categoryProxy = Proxy::getLazyLoadingClass('Fixtures\Collection\Category', Di::getDefault());

        for ($i = 0; $i <= 10; $i++) {
            /** @var Category $proxy */
            $proxy = clone $categoryProxy;
            $proxy->setCategory(null);
            $proxy->setName('Category ' . $i);
            $proxy->setDesc('Lorem ipsum dolor ' . $i . 'sit amet');
            $proxy->save();

            $categoryProxies[] = $proxy;

            for ($k = 0; $k <= 100; $k++) {

                /** @var Product $product */
                $product = clone $productProxy;
                $product->setName('Product'.$k.'-'.$i);
                $product->setCategory($proxy);
                $product->setPrice(9900);
                $product->setCreatedAt(new \MongoDate(time()));
                $product->setIsActive(true);
                $product->save();

                $productProxies[] = $product;
            }

        }

        return [
            'categories' => $categoryProxies,
            'products' => $productProxies
        ];
    }
}