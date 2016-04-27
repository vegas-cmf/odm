<?php
/**
 * This file is part of Vegas package
 *
 * @author Mateusz Aniołek <mateusz.aniolek@amsterdam-standard.pl>
 * @copyright Amsterdam Standard Sp. Z o.o.
 * @homepage http://vegas-cmf.github.io
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vegas\ODM\Proxy;

use Vegas\ODM\Collection;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;

/**
 * Class Generator
 * @package Vegas\ODM\Proxy
 */
class Generator
{
    /**
     * @param \ReflectionClass $originalClass
     * @param ClassGenerator $classGenerator
     */
    public function generate(\ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        $classGenerator->setExtendedClass('\\' . $originalClass->getName());
        if ($originalClass->isSubclassOf(Collection::class)) {

            $reflectionMethod = new \ReflectionMethod($originalClass->getName(), 'getMetadata');
            $className = $originalClass->getName();
            $metadata = $reflectionMethod->invoke(new $className());

            foreach($metadata as $fieldName => $mappingClass) {
                $classGenerator->addMethod('get' . ucfirst($fieldName), [],
                    MethodGenerator::FLAG_PUBLIC,
                    '$this->mapProperty(\'' . $fieldName . '\');' . "\n" .'return parent::get' . ucfirst($fieldName) . '();',
                    '@return ' . $mappingClass
                );
            }

        }

    }

}