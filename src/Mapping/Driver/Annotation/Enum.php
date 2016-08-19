<?php
/**
 * This file is part of Vegas package
 *
 * @author Mateusz Aniolek <mateusz.aniolek@amsterdam-standard.pl>
 * @copyright Amsterdam Standard Sp. Z o.o.
 * @homepage http://cmf.vegas
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vegas\ODM\Mapping\Driver\Annotation;

/**
 * Class Enum
 * @package Vegas\ODM\Mapping\Driver\Annotation
 */
class Enum
{
    /**
     * Mapper type annotation
     */
    const MAPPER_ANNOTATION = '@Mapper';

    /**
     * Variable type annotation
     */
    const VAR_ANNOTATION = '@var';

    /**
     * Allowed annotation const
     */
    const ANNOTATION_ALLOWED = [
        self::MAPPER_ANNOTATION,
        self::VAR_ANNOTATION
    ];
}