<?php
/**
 * This file is part of Vegas package
 *
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @copyright Amsterdam Standard Sp. Z o.o.
 * @homepage http://cmf.vegas
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vegas\ODM\Mapping\Driver;

use Vegas\ODM\Mapping\Driver\Annotation\Parser;
use Vegas\ODM\Mapping\Driver\Exception\UnsupportedTargetException;

/**
 * Class Annotation
 * @package Vegas\ODM\Mapping\Driver
 */
class Annotation
{
    /**
     * @var \ReflectionClass|\ReflectionObject
     */
    protected $reflection;

    /**
     * @var array
     */
    protected $annotations = [];

    /**
     * @param $target
     */
    public function __construct($target)
    {
        $this->reflection = $this->getTargetReflection($target);
        $this->parseAnnotation();
    }

    /**
     * @return array
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }

    /**
     * @param $target
     * @return \ReflectionClass|\ReflectionObject
     * @throws UnsupportedTargetException
     */
    protected function getTargetReflection($target)
    {
        if (is_object($target)) {
            return new \ReflectionObject($target);
        } else if (is_string($target)) {
            return new \ReflectionClass($target);
        }

        throw new UnsupportedTargetException;
    }

    protected function parseAnnotation()
    {
        $parser = new Parser($this->reflection);
        $parser->run();

        $this->annotations = $parser->getAnnotations();
    }
}