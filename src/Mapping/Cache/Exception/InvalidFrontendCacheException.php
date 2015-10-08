<?php
/**
 * @author Sławomir Żytko <slawek@amsterdam-standard.pl>
 * @homepage http://amsterdam-standard.pl
 */

namespace Vegas\ODM\Mapping\Cache\Exception;

/**
 * Class InvalidFrontendCacheException
 * @package Vegas\ODM\Mapping\Cache\Exception
 */
class InvalidFrontendCacheException extends \Exception
{
    protected $message = 'Invalid frontend cache adapter';
}