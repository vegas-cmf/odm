<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\Tests\ODM\Collection;

use Fixtures\Collection\Category;
use Fixtures\Collection\Product;

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

    public function testShouldReturnLazyLoadingCursor()
    {
        Category::enableEagerLoading();

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

        Category::disableEagerLoading();

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
            $this->assertTrue(\MongoDBRef::isRef($category->getCategory()));
        }
    }

    public function testShouldReturnArray()
    {
        Category::enableEagerLoading();
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