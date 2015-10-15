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

use Vegas\ODM\Mapping\Driver\Exception\AnnotationNotFoundException;

/**
 * Class Annotation
 * @package Vegas\ODM\Mapping\Driver
 */
class Annotation
{
    /**
     * Required annotation
     */
    const MAPPER_ANNOTATION = '@Mapper';

    /**
     * Variable type annotation
     */
    const VAR_ANNOTATION = '@var';

    /**
     * @var array
     */
    private $requiredAnnotations = [
        self::MAPPER_ANNOTATION, self::VAR_ANNOTATION
    ];

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
        $this->parsePropertiesAnnotation();
    }

    /**
     * @param $target
     * @return \ReflectionClass|\ReflectionObject
     */
    protected function getTargetReflection($target)
    {
        if (is_object($target)) {
            return new \ReflectionObject($target);
        } else if (is_string($target)) {
            return new \ReflectionClass($target);
        }
    }

    /**
     *
     */
    protected function parsePropertiesAnnotation()
    {
        $annotations = [];
        foreach ($this->reflection->getProperties() as $property) {
            $docBlock = $property->getDocComment();
            if (!$docBlock) {
                continue;
            }
            try {
                $annotations[$property->getName()] = $this->extractMapperAnnotation($property->getDocComment());
            } catch (AnnotationNotFoundException $e) {
                continue;
            }
        }
        $this->annotations = $annotations;
    }

    /**
     * @param $annotations
     * @return bool
     */
    private function isValid($annotations)
    {
        $valid = true;
        foreach ($this->requiredAnnotations as $annotation) {
            if (!in_array($annotation, $annotations)) {
                $valid = false;
                break;
            }
        }

        return $valid;
    }

    /**
     * Extract variable type and search for @mapper annotation
     *
     * @param $docBlock
     * @return string
     * @throws AnnotationNotFoundException
     */
    protected function extractMapperAnnotation($docBlock)
    {
        $regex = sprintf("#(%s)(.*?)(\n|\s|\r)#U", implode('|', $this->requiredAnnotations));
        preg_match_all($regex, $docBlock, $matches);
        if (empty($matches) || count($matches) < 3) {
            throw new AnnotationNotFoundException();
        }
        if (!$this->isValid($matches[1])) {
            throw new AnnotationNotFoundException();
        }
        $mapperAnnotationIndex = array_search(self::MAPPER_ANNOTATION, $matches[1]);
        if ($mapperAnnotationIndex === false) {
            $mapperAnnotationIndex = array_search(strtolower(self::MAPPER_ANNOTATION), $matches[1]);
        }
        if ($mapperAnnotationIndex !== false) {
            $type = trim($matches[2][$mapperAnnotationIndex]);
        }
        if (!isset($type) || !$type) {
            $varAnnotationIndex = array_search(self::VAR_ANNOTATION, $matches[1]);
            $type = trim($matches[2][$varAnnotationIndex]);
            if ($varAnnotationIndex === false || !isset($matches[2][$varAnnotationIndex])) {
                throw new AnnotationNotFoundException();
            }
        } else {
            $type = trim($matches[2][$mapperAnnotationIndex]);
        }

        return trim($type);
    }

    /**
     * @return array
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }
}