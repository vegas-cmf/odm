<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\Test\ODM\Mapping\Mapper;

use Vegas\ODM\Mapping\Mapper\MongoId;

class MongoIdTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldMapStringToMongoId()
    {
        $id = '51b14c2de8e185801f000006';
        $mongoId = (new MongoId())->createReference($id);
        $this->assertInstanceOf('\MongoId', $mongoId);
        $this->assertEquals((string) $id, (string) $mongoId);
    }

    public function testShouldMapMongoIdToMongoId()
    {
        $id = new \MongoId('51b14c2de8e185801f000006');
        $mongoId = (new MongoId())->createReference($id);
        $this->assertInstanceOf('\MongoId', $mongoId);
        $this->assertEquals((string) $id, (string) $mongoId);
    }

    public function testShouldNotMapNullToMongoId()
    {
        $id = null;
        $mongoId = (new MongoId())->createReference($id);
        $this->assertNull($mongoId);
    }
}