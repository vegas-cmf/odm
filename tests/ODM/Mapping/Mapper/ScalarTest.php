<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\Test\ODM\Mapping\Mapper;

use Vegas\ODM\Mapping\Mapper\Scalar;

class ScalarTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldMapValueToInt()
    {
        $val = '42';
        $this->assertInternalType('int', Scalar::mapInt($val));
        $this->assertInternalType('int', Scalar::map($val, 'int'));
        $this->assertEquals(42, Scalar::mapInt($val));
        $this->assertFalse(Scalar::map(null, 'int'));
    }

    public function testShouldMapValueToBoolean()
    {
        $val = 'false';
        $this->assertInternalType('boolean', Scalar::mapBoolean($val));
        $this->assertInternalType('boolean', Scalar::map($val, 'boolean'));
        $this->assertFalse(Scalar::mapBoolean($val));
    }

    public function testShouldMapValueToFloat()
    {
        $val = '1.2';
        $this->assertInternalType('float', Scalar::mapFloat($val));
        $this->assertInternalType('float', Scalar::map($val, 'float'));
        $this->assertEquals(1.2, Scalar::mapFloat($val));
        $this->assertFalse(Scalar::map(null, 'float'));
    }

    public function testShouldMapValueToArray()
    {
        $val = null;
        $this->assertInternalType('array', Scalar::mapArray($val));
        $this->assertInternalType('array', Scalar::map($val, 'array'));
        $this->assertSame(0, count(Scalar::mapArray($val)));
    }

    public function testShouldReturnTrueForStringIndicatingScalarValue()
    {
        $this->assertTrue(Scalar::isScalar('int'));
        $this->assertTrue(Scalar::isScalar('boolean'));
        $this->assertTrue(Scalar::isScalar('float'));
        $this->assertTrue(Scalar::isScalar('array'));
    }
}