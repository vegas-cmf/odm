<?php

/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\Tests\ODM;

use App\Entity\Category;
use App\Entity\Product;

class Test extends \PHPUnit_Framework_TestCase
{
    public function testApp()
    {
        $cat = new Category();
        $cat->setName('Category 1');
        $cat->setDesc('Category 1 is...');
        $cat->save();

        $prod = new Product();
        $prod->setName('Product 1');
        $prod->setPrice(100);
        $prod->setIsActive(true);
        $prod->setCategory($cat);
        $prod->setCreatedAt(time());

        $prod->save();

        $test = Product::findFirst();
        $this->assertInstanceOf('App\Entity\Category', $test->getCategory());
        $this->assertInstanceOf('\MongoDate', $test->getCreatedAt());
        $this->assertInternalType('boolean', $test->isActive());
        $this->assertInternalType('int', $test->getPrice());
    }
}