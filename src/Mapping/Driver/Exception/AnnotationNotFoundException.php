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

namespace Vegas\ODM\Mapping\Driver\Exception;

/**
 * Class AnnotationNotFoundException
 * @package Vegas\ODM\Mapping\Driver\Exception
 */
class AnnotationNotFoundException extends \Vegas\ODM\Exception
{
    /**
     * @var string
     */
    protected $message = 'Annotation not found';
}