<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\Tests\ODM\Mapping\Driver;

use Phalcon\Di;
use Vegas\ODM\Mapping\Driver\Annotation;
use Vegas\ODM\Mapping\Driver\Exception\UnsupportedTargetException;

class AnnotationTest extends \PHPUnit_Framework_TestCase
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

    public function testShouldExtractAnnotationsFromClass()
    {
        Di::getDefault()->remove('odmMappingCache');

        $annotationParser = new Annotation('\Fixtures\Collection\Product');

        $metadata = [
            "category" => "\\Fixtures\\Collection\\Category",
            "price" => "int",
            "tags" => "\\Fixtures\\Collection\\Tag",
            "createdAt" => "\\Vegas\\ODM\\Mapping\\Mapper\\MongoDate",
            "isActive" => "boolean"
        ];

        $this->assertEquals($metadata, $annotationParser->getAnnotations());

        $fakeClassSrc =
            "class Test {
                /**
                 * @var \\MongoId
                 * @mapper \\Vegas\\ODM\\Mapping\\Mapper\\MongoId
                 */
                protected \$_id;

                /**
                 * @var float
                 * @mapper
                 */
                protected \$float;

                /**
                 * @var int
                 * @mapper
                 */
                protected \$int;

                /**
                 * @var \\MongoDate
                 * @mapper \\Vegas\\ODM\\Mapping\\Mapper\\MongoDate
                 */
                protected \$date;

                /**
                 * @var boolean
                 */
                protected \$notMapped;
            }";

        eval($fakeClassSrc);
        $obj = new \Test();

        $annotationParser = new Annotation($obj);
        $metadata = [
            "_id" => "\\Vegas\\ODM\\Mapping\\Mapper\\MongoId",
            "float" => "float",
            "int" => "int",
            "date" => "\\Vegas\\ODM\\Mapping\\Mapper\\MongoDate"
        ];

        $this->assertEquals($metadata, $annotationParser->getAnnotations());
    }

    /**
     * @expectedException \Vegas\ODM\Mapping\Driver\Exception\UnsupportedTargetException
     */
    public function testUnsupportedTargetException()
    {
        new Annotation([]);
    }
}