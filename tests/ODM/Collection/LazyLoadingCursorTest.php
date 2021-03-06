<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\Tests\ODM\Collection;

use Fixtures\Collection\Category;
use Fixtures\Collection\Product;
use Fixtures\Lib\ExtendedCursor;

class LazyLoadingCursorTest extends \PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        foreach (Category::find() as $category) {
            $category->delete();
        }
        foreach (Product::find() as $product) {
            $product->delete();
        }
    }

    public function testQueryWithGivenFields()
    {
        /** @var Product $collection */
        $collection = new Product;
        $collection->setName('test');
        $collection->setPrice(199);
        $collection->save();

        $parameters = [
            'fields' => [ 'name' ]
        ];

        $cursor = Product::_getCursor($parameters, $collection, $collection->getConnection());

        $cursor = new \Fixtures\Collection\Lib\ExtendedCursor(
            $cursor,
            $collection,
            is_array($parameters) &&  isset($parameters['fields']) ? $parameters['fields'] : null
        );

        $fields = $cursor->getFields();
        $this->assertEquals(1, count($fields));
        $item = $cursor->current();

        $this->assertEquals($collection->getName(), $item->getName());
    }

    public function testShouldReturnLazyLoadingCursor()
    {
        $parentCategory = new Category();
        $parentCategory->setName('Parent category');
        $parentCategory->setDesc('Parent category');
        $parentCategory->save();

        for ($i = 0; $i < 10; $i++) {
            $category = new Category();
            $category->setName('Category ' . $i);
            $category->setDesc('Category ' . $i . ' desc');
            $category->setCategory($parentCategory);
            $category->save();
        }

        Category::enableLazyLoading();

        $categories = Category::find([
            [
                'category' => [
                    '$ne' => null
                ]
            ]
        ]);
        $this->assertInstanceOf('\Vegas\Odm\Collection\LazyLoadingCursor', $categories);

        foreach ($categories as $category) {
            $this->assertInstanceOf('\Fixtures\Collection\Category', $category);
            $this->assertInstanceOf('\Fixtures\Collection\Category', $category->getCategory());
        }

        $categories = Category::find([
            [
                'category' => [
                    '$ne' => null
                ]
            ]
        ]);
        $this->assertInstanceOf('\Vegas\Odm\Collection\LazyLoadingCursor', $categories);
        foreach ($categories as $category) {
            $this->assertInstanceOf('\Fixtures\Collection\Category', $category);

            $reflectionClass = new \ReflectionClass(get_class($category));
            $categoryProperty = $reflectionClass->getProperty('category');
            $categoryProperty->setAccessible(true);
            $this->assertTrue(\MongoDBRef::isRef($categoryProperty->getValue($category)));
        }
    }

    public function testShouldReturnArray()
    {
        $categories = Category::find([
            [
                'category' => [
                    '$ne' => null
                ]
            ]
        ]);

        $this->assertInternalType('array', $categories->toArray());
        $this->assertSame($categories->count(), count($categories->toArray()));

        $categoriesArray = $categories->toArray();
        $i = 0;
        foreach ($categories as $category) {
            $this->assertEquals((string) $category->getId(), (string) $categoriesArray[$i++]['_id']);
        }
    }
}