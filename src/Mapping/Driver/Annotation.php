<?php
/**
 * @author Sławomir Żytko <slawek@amsterdam-standard.pl>
 * @homepage http://amsterdam-standard.pl
 */

namespace Vegas\ODM\Mapping\Driver;

use Vegas\ODM\Mapping\Driver\Exception\AnnotationNotFoundException;

class Annotation
{
    const MAPPER_ANNOTATION = '@Mapper';

    const VAR_ANNOTATION = '@var';

    private $requiredAnnotations = [
        self::MAPPER_ANNOTATION, self::VAR_ANNOTATION
    ];

    protected $reflection;

    protected $annotations = [];

    /**
     * @param $target
     */
    public function __construct($target)
    {
        $this->reflection = $this->getTargetReflection($target);
        $this->parsePropertiesAnnotation();
    }

    protected function getTargetReflection($target)
    {
        if (is_object($target)) {
            return new \ReflectionObject($target);
        } else if (is_string($target)) {
            return new \ReflectionClass($target);
        }
    }

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
        $varAnnotationIndex = array_search(self::VAR_ANNOTATION, $matches[1]);
        if ($varAnnotationIndex === false || !isset($matches[2][$varAnnotationIndex])) {
            throw new AnnotationNotFoundException();
        }

        return trim($matches[2][$varAnnotationIndex]);
    }

    public function getAnnotations()
    {
        return $this->annotations;
    }
}