<?php
/**
 * @author Sławomir Żytko <slawek@amsterdam-standard.pl>
 * @homepage http://amsterdam-standard.pl
 */

namespace Vegas\ODM\Mapping\Cache\Exception;

/**
 * Class InvalidBackendCacheException
 * @package Vegas\ODM\Mapping\Cache\Exception
 */
class InvalidBackendCacheException extends \Exception
{
    protected $message = 'Invalid backend cache adapter';
}