<?php

/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\ODM\Mapping\Mapper;

use Vegas\ODM\Mapping\MapperInterface;

/**
 * Class MongoDate
 * @deprecated use UTCDateTime mapper instead
 * @package Vegas\ODM\Mapping\Mapper
 */
class MongoDate implements MapperInterface
{

    /**
     * @param $value
     * @return mixed
     */
    public static function getMapped($value)
    {
        return static::createReference($value);
    }

    /**
     * @param $value
     * @return \MongoDate
     */
    public static function createReference($value)
    {
        if (!$value instanceof \MongoDate) {
            if (is_numeric($value)) {
                $value = new \MongoDate($value);
            } else if (is_string($value)) {
                if (($strDate = strtotime($value))) {
                    $value = $strDate;
                }
                $value = new \MongoDate(intval($value));
            } else if ($value instanceof \DateTime) {
                $value = new \MongoDate($value->getTimestamp());
            }
        }

        return $value;
    }
}