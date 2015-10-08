<?php
/**
 * @author Sławomir Żytko <slawek@amsterdam-standard.pl>
 * @homepage http://amsterdam-standard.pl
 */

namespace Vegas\ODM\Mapping;

/**
 * Interface MapperInterface
 * @package Vegas\ODM\Mapping
 */
interface MapperInterface
{
    /**
     * @param $value
     * @return mixed
     */
    public static function getMapped($value);

    /**
     * @param $value
     * @return mixed
     */
    public static function createReference($value);
}