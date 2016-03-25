<?php

/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\Tests\ODM;

use Fixtures\Collection\_PM_\Test\Proxy;
use Fixtures\Collection\Category;
use Fixtures\Collection\Product;
use Phalcon\Di;
use Phalcon\DiInterface;
use Vegas\ODM\Collection;
use Vegas\ODM\Proxy\Generator\Helper;
use Vegas\ODM\ProxyBuilder;
use Fixtures\Collection\Foo;


class BenchmarkTest extends \PHPUnit_Framework_TestCase
{
    public function clearModels()
    {
        foreach (Product::find() as $product) {
            $product->delete();
        }
        foreach (Category::find() as $category) {
            $category->delete();
        }
    }

    public function testBenchmark()
    {
//        $this->benchmarkModels1();
//        $this->benchmarkModels2();
        //$this->benchmarkModels3();
    }

    public function benchmarkModels1()
    {
        $this->clearModels();
        echo 'Test 1' . PHP_EOL;
        $this->benchmark(1, 5, 10);
    }

    public function benchmarkModels2()
    {
        $this->clearModels();
        echo 'Test 2' . PHP_EOL;
        $this->benchmark(1, 10, 50);
    }

    public function benchmarkModels3()
    {
        $this->clearModels();
        echo 'Test 3' . PHP_EOL;
        $this->benchmark(1, 10, 100);
    }

    public function benchmark($mainCategoryNum, $subCategoryNum, $productsNum)
    {
        $totalIterations = $mainCategoryNum * $subCategoryNum * $productsNum;
        $this->setupDummyData($mainCategoryNum, $subCategoryNum, $productsNum);

        Product::enableLazyLoading();
        Category::enableLazyLoading();

        $time = microtime(true);
        $products = Product::find();
        /** @var Product $product */
        foreach($products as $product) {
            $subCategory = $product->getCategory();
            if ($subCategory) {
                $mainCategory = $subCategory->getCategory();
            }
            $productName = $product->getName();
        }
        echo $totalIterations . ' iterations with Lazy Loading enabled: ' . (microtime(true) - $time) . PHP_EOL;

        Product::disableLazyLoading();
        Category::disableLazyLoading();
        $time = microtime(true);
        $products = Product::find();
        /** @var Product $product */
        foreach($products as $product) {
            $product->map();
            $subCategory = $product->getCategory();
            if ($subCategory) {
                $subCategory->map();
                $mainCategory = $subCategory->getCategory();
            }
            $productName = $product->getName();
        }
        echo $totalIterations . ' iterations with Lazy Loading disabled: ' . (microtime(true) - $time) . PHP_EOL;
    }

    private function setupDummyData($mainCategoryNum = 10, $subCategoryNum = 100, $productNum = 40)
    {
        for ($i = 0; $i <= $mainCategoryNum; $i++) {

            /** @var Category $parent */
            $parent = new Category();
            $parent->setCategory(null);
            $parent->setName('Category ' . $i);
            $parent->setDesc('Lorem ipsum dolor ' . $i . 'sit amet');
            $parent->save();

            for ($k = 0; $k <= $subCategoryNum; $k++) {

                /** @var Category $category */
                $category = new Category();
                $category->setCategory($parent);
                $category->setName('Subcategory'.$k.'-'.$i);
                $category->setDesc('test ' . uniqid('', true));
                $category->save();

                for ($j = 0; $j <= $productNum; $j++) {

                    /** @var Product $parent */
                    $parent = new Product();
                    $parent->setCategory($category);
                    $parent->setName('Product ' . $j);
                    $parent->setPrice(rand(89, 8899));
                    $parent->setIsActive(true);
                    $parent->save();
                }
            }

        }
    }
}