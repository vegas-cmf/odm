<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\Tests\ODM;

use Fixtures\Collection\Product;
use MongoDB\BSON\ObjectID;
use Vegas\ODM\Mongo\DbRef;

class DbRefTest extends \PHPUnit_Framework_TestCase
{

    public function testShouldCreateDBRefFromMongoId()
    {
        $id = new ObjectID(null);
        $dbRef = DbRef::create((new Product())->getSource(), $id);

        $this->assertTrue(DbRef::isRef($dbRef));
        $this->assertEquals($id, $dbRef['$id']);
        $this->assertEquals((new Product())->getSource(), $dbRef['$ref']);
    }

    public function testShouldCreateDBRefFromString()
    {
        $id = new ObjectID(null);
        $dbRef = DbRef::create((new Product())->getSource(), (string)$id);

        $this->assertTrue(DbRef::isRef($dbRef));
        $this->assertEquals($id, $dbRef['$id']);
        $this->assertEquals((new Product())->getSource(), $dbRef['$ref']);
    }

    public function testShouldCreateDBRefFromCollection()
    {
        $collection = new Product();
        $collection->setName('Test');
        $collection->save();

        $dbRef = DbRef::create($collection->getSource(), $collection);
        $this->assertTrue(DbRef::isRef($dbRef));
        $this->assertEquals($collection->getId(), $dbRef['$id']);
        $this->assertEquals($collection->getSource(), $dbRef['$ref']);
    }

    public function testShouldReturnNullForInvalidArguments()
    {
        $dbRef = DbRef::create(null, null);
        $this->assertNull($dbRef);

        $dbRef = DbRef::create((new Product())->getSource(), null);
        $this->assertNull($dbRef);
    }

    public function testShouldReturnNullOneArgumentMongoId()
    {
        $id = new ObjectID(null);
        $dbRef = DbRef::create($id);
        $this->assertInstanceOf(ObjectID::class, $dbRef);
    }
}