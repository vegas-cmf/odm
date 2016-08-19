<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\Test\ODM\Mapping\Mapper;

use Vegas\ODM\Mapping\Mapper\MongoDate;

class MongoDateTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->markTestIncomplete('This class is deprecated');
    }

    public function testShouldMapIntToMongoDate()
    {
        $date = time();
        $mongoDate = (new MongoDate())->createReference($date);
        $this->assertInstanceOf('\MongoDate', $mongoDate);
        $this->assertSame($date, $mongoDate->sec);
    }

    public function testShouldMapDateTimeToMongoDate()
    {
        $date = new \DateTime();
        $mongoDate = (new MongoDate())->createReference($date);
        $this->assertInstanceOf('\MongoDate', $mongoDate);
        $this->assertSame($date->getTimestamp(), $mongoDate->sec);
    }

    public function testShouldMapMongoDateToMongoDate()
    {
        $date = new \MongoDate();
        $mongoDate = (new MongoDate())->createReference($date);
        $this->assertInstanceOf('\MongoDate', $mongoDate);
        $this->assertSame($date->sec, $mongoDate->sec);
    }

    public function testShouldMapStringDateToMongoDate()
    {
        $date = '2015-10-10 20:10:10';
        $mongoDate = (new MongoDate())->createReference($date);
        $this->assertInstanceOf('\MongoDate', $mongoDate);
        $this->assertSame(strtotime($date), $mongoDate->sec);
    }
}