<?php

/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\Tests\ODM;

use App\Collection\Category;
use App\Collection\Product;
use Phalcon\Di;

class CollectionTeest extends \PHPUnit_Framework_TestCase
{
    protected $odmMappingCache;

    public function setUp()
    {
        // disable cache
        $this->odmMappingCache = Di::getDefault()->get('odmMappingCache');
        Di::getDefault()->remove('odmMappingCache');
    }

    public function tearDown()
    {
        foreach (Category::find() as $category) {
            $category->delete();
        }
        foreach (Product::find() as $product) {
            $product->delete();
        }

        // rollback cache
        Di::getDefault()->set('odmMappingCache', $this->odmMappingCache);
    }

    public function testShouldExtractMetadataFromCollection()
    {
        Di::getDefault()->remove('odmMappingCache');

        $collection = new Product();

        $metadata = [
            "category" => "\\App\\Collection\\Category",
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

        $this->assertInstanceOf('App\\Collection\\Category', $category->getCategory());

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
        $category->setDesc('Category 1 is...');
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
        $this->assertInstanceOf('App\Collection\Category', $testProduct->getCategory());
        $this->assertInstanceOf('\MongoDate', $testProduct->getCreatedAt());
        $this->assertInternalType('boolean', $testProduct->isActive());
        $this->assertInternalType('int', $testProduct->getPrice());
        $this->assertInstanceOf('\App\Collection\Category', $testProduct->getCategory()->getCategory());
    }
}