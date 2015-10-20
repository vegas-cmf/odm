<?php

/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\Tests\ODM;

use Fixtures\Collection\Category;
use Fixtures\Collection\Product;
use Phalcon\Di;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    protected $odmMappingCache;

    public function setUp()
    {
        foreach (Category::find() as $category) {
            $category->delete();
        }
        foreach (Product::find() as $product) {
            $product->delete();
        }

        // disable cache
        $this->odmMappingCache = Di::getDefault()->get('odmMappingCache');
        Di::getDefault()->remove('odmMappingCache');
    }

    public function tearDown()
    {
        // rollback cache
        Di::getDefault()->set('odmMappingCache', $this->odmMappingCache);
    }

    public function testShouldExtractMetadataFromCollection()
    {
        Di::getDefault()->remove('odmMappingCache');

        $collection = new Product();

        $metadata = [
            "category" => "\\Fixtures\\Collection\\Category",
            "price" => "int",
            "createdAt" => "\\Vegas\\ODM\\Mapping\\Mapper\\MongoDate",
            "isActive" => "boolean"
        ];

        $this->assertEquals($metadata, $collection->getMetadata());
    }

    public function testShouldSaveRecordWithCorrectReferences()
    {
        $parentParentCategory = new Category();
        $parentParentCategory->setName('Parent category parent');
        $parentParentCategory->setDesc('Parent of category parent');
        $this->assertTrue($parentParentCategory->save());

        $parentCategory = new Category();
        $parentCategory->setName('Category parent');
        $parentCategory->setDesc('Category parent');
        $parentCategory->setCategory($parentParentCategory);
        $this->assertTrue($parentCategory->save());

        $category = new Category();
        $category->setName('Category');
        $category->setDesc('Category');
        $category->setCategory($parentCategory);
        $this->assertTrue($category->save());

        $this->assertInstanceOf('\Fixtures\Collection\Category', $category->getCategory());

        $toArray = [
            'name' => 'Category',
            'desc' => 'Category',
            'category' => [
                'name' => 'Category parent',
                'desc' => 'Category parent',
                'category' => [
                    'name' => 'Parent category parent',
                    'desc' => 'Parent of category parent',
                    'category' => null,
                    '_id' => $parentParentCategory->getId()
                ],
                '_id' => $parentCategory->getId()
            ],
            '_id' => $category->getId()
        ];

        $this->assertEquals($toArray, $category->toArray());
    }

    public function testShouldMapValues()
    {
        $parentCategory = new Category();
        $parentCategory->setName('Category 0');
        $parentCategory->setDesc('Category 0 desc');
        $parentCategory->save();

        $category = new Category();
        $category->setName('Category 1');
        $category->setDesc('Category 1 desc');
        $category->setCategory($parentCategory);
        $category->save();

        $product = new Product();
        $product->setName('Product 1');
        $product->setPrice(100);
        $product->setIsActive(true);
        $product->setCategory($category);
        $product->setCreatedAt(time());

        $product->save();

        $testProduct = Product::findFirst();
        $this->assertInstanceOf('\Fixtures\Collection\Category', $testProduct->getCategory());
        $this->assertInstanceOf('\MongoDate', $testProduct->getCreatedAt());
        $this->assertInternalType('boolean', $testProduct->isActive());
        $this->assertInternalType('int', $testProduct->getPrice());
        $this->assertInstanceOf('\Fixtures\Collection\Category', $testProduct->getCategory()->getCategory());
    }

    public function testShouldSaveInfoAboutEagerLoading()
    {
        Category::disableEagerLoading();
        Product::disableEagerLoading();

        $category = new Category();
        $category->setName('Category 1');
        $category->setDesc('Category 1 desc');
        $category->save();

        $product = new Product();
        $product->setName('Product 1');
        $product->setPrice(100);
        $product->setIsActive(true);
        $product->setCreatedAt(time());

        $reflectionObj = new \ReflectionObject($product);
        $prop = $reflectionObj->getProperty('category');
        $prop->setAccessible(true);
        $prop->setValue($product, $category->getId());

        $product->save();

        $productTest = Product::findById($product->getId());
        $this->assertNotInstanceOf('Fixtures\Collection\Category', $productTest->getCategory());
        $this->assertInstanceOf('\MongoId', $productTest->getCategory());

        Product::enableEagerLoading();

        $productTest = Product::findById($product->getId());
        $this->assertNotInstanceOf('Fixtures\Collection\Category', $productTest->getCategory());
        $this->assertInstanceOf('\MongoId', $productTest->getCategory());
    }

    public function testShouldCacheAnnotations()
    {
        $mongo = Di::getDefault()->get('mongo');
        $mongo->cache->remove();

        Di::getDefault()->set('odmMappingCache', $this->odmMappingCache);
        $product = new Product();
        $product->getMetadata();

        $this->assertGreaterThan(0, $mongo->cache->find()->count());

        Di::getDefault()->remove('odmMappingCache');
    }

    public function testShouldNotCacheAnnotations()
    {
        $mongo = Di::getDefault()->get('mongo');
        $mongo->cache->remove();

        $product = new Product();
        $product->getMetadata();

        $this->assertEquals(0, $mongo->cache->find()->count());
    }
}