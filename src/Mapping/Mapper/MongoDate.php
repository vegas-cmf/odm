<?php

/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\ODM\Mapping\Mapper;

use Vegas\ODM\Mapping\MapperInterface;

class MongoDate implements MapperInterface
{

    /**
     * @param $value
     * @return mixed
     */
    public static function getMapped($value)
    {
        if (!$value instanceof \MongoDate) {
            $value = new \MongoDate(intval($value));
        }

        return $value;
    }

    /**
     * @param $value
     * @return mixed
     */
    public static function createReference($value)
    {
        if (!$value instanceof \MongoDate) {
            if (is_numeric($value)) {
                $value = new \MongoDate($value);
            } else if (is_string($value)) {
                $value = new \MongoDate(intval($value));
            } else if ($value instanceof \DateTime) {
                $value = new \MongoDate($value->getTimestamp());
            }
        }

        return $value;
    }
}