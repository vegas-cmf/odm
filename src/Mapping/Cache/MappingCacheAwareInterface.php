<?php
/**
 * @author Sławomir Żytko <slawek@amsterdam-standard.pl>
 * @homepage http://amsterdam-standard.pl
 */

namespace Vegas\ODM\Mapping\Cache;

/**
 * Interface MappingCacheAwareInterface
 * @package Vegas\ODM\Mapping\Cache
 */
interface MappingCacheAwareInterface
{
    /**
     * @param $value
     * @return mixed
     */
    public function getMappingCacheKey($value);

    /**
     * @return mixed
     */
    public function getDI();
}