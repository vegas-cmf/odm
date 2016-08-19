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

namespace Vegas\ODM\Mapping\Driver\Annotation;

use Vegas\ODM\Mapping\Driver\Exception\AnnotationNotFoundException;

/**
 * Class Parser
 * @package Vegas\ODM\Mapping\Driver\Annotation
 */
class Parser
{
    /**
     * @var \ReflectionClass
     */
    protected $reflection;

    /**
     * @var array
     */
    protected $annotations = [];

    /**
     * @var array
     */
    protected $allowedAnnotations = [];

    /**
     * Metadata constructor.
     * @param \ReflectionClass $reflection
     */
    public function __construct(\ReflectionClass $reflection)
    {
        $this->reflection = $reflection;
        $this->allowedAnnotations = array_merge(Enum::getAllowedAnnotations(), [strtolower(Enum::MAPPER_ANNOTATION)]);
    }

    /**
     *
     */
    public function run()
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
     * Check correctness of given annotations
     * @param $annotations
     * @return bool
     */
    protected function isValid($annotations)
    {
        $valid = [];
        foreach ($this->allowedAnnotations as $annotation) {
            if (in_array($annotation, $annotations)) {
                $valid[strtolower($annotation)] = true;
            }
        }

        return count($valid) == 2;
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
        $matches = $this->getMatches($docBlock);

        $mapperAnnotationIndex = array_search(Enum::MAPPER_ANNOTATION, $matches[1]);
        if ($mapperAnnotationIndex === false) {
            $mapperAnnotationIndex = array_search(strtolower(Enum::MAPPER_ANNOTATION), $matches[1]);
        }
        if ($mapperAnnotationIndex !== false) {
            $type = trim($matches[2][$mapperAnnotationIndex]);
        }
        if (!isset($type) || !$type) {
            $varAnnotationIndex = array_search(Enum::VAR_ANNOTATION, $matches[1]);
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
     * @param $docBlock
     * @return array
     * @throws AnnotationNotFoundException
     */
    protected function getMatches($docBlock)
    {
        $matches = [];
        $regex = sprintf("#(%s)(.*?)(\n|\s|\r\t)#U", implode('|', $this->allowedAnnotations));
        preg_match_all($regex, $docBlock, $matches);

        if (!$this->isValid($matches[1])) {
            throw new AnnotationNotFoundException();
        }

        return $matches;
    }

    /**
     * @return array
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }
}