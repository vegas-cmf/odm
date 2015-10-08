<?php
/**
 * @author Sławomir Żytko <slawek@amsterdam-standard.pl>
 * @homepage http://amsterdam-standard.pl
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