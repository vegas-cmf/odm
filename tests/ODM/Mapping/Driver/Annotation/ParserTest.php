<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\Tests\ODM\Mapping\Driver;

use Fixtures\Collection\Bar;
use Fixtures\Collection\Foo;
use Phalcon\Di;
use Vegas\ODM\Mapping\Driver\Annotation;
use Vegas\ODM\Mapping\Driver\Exception\UnsupportedTargetException;

class ParserTest extends \PHPUnit_Framework_TestCase
{

    public function testSingleMapping()
    {
        $matches = [];
        $regex = "#(@Mapper|@var|@mapper)(.*?)(\n|\s|\r\t)#U";
        $docBlock = "/**\n * @var \Fixtures\Collection\Tag\n * @Mapper\n */";
        preg_match_all($regex, $docBlock, $matches);

        $this->assertEquals(count($matches[1]), 2);
        $this->assertEquals($matches[0][0], "@var \Fixtures\Collection\Tag\n");
    }

    public function testArrayMapping()
    {
        $matches = [];
        $regex = "#(@Mapper|@var|@mapper)(.*?)(\n|\s|\r\t)#U";
        $docBlock = "/**\n * @var \Fixtures\Collection\Tag []\n * @Mapper\n */";
        preg_match_all($regex, $docBlock, $matches);

        $this->assertEquals(count($matches[1]), 2);
        $this->assertEquals($matches[0][0], "@var \Fixtures\Collection\Tag []\n");
    }

    public function testParser()
    {
        $reflection = new \ReflectionClass(Bar::class);
        $parser = new Annotation\Parser($reflection);
        $parser->run();

        $this->annotations = $parser->getAnnotations();
    }

}