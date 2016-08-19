<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\ODM\Mapping\Mapper;

use Vegas\ODM\Mapping\MapperInterface;

/**
 * Class MongoId
 * @deprecated use ObjectID mapper instead
 * @package Vegas\ODM\Mapping\Mapper
 */
class MongoId implements MapperInterface
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
        if (!$value instanceof \MongoId && $value !== null) {
            if (\MongoId::isValid($value)) {
                $value = new \MongoId($value);
            } else {
                try {
                    if (is_array($value) && isset($value['$id'])) {
                        $value = new \MongoId($value['$id']);
                    } else {
                        $value = new \MongoId(trim($value));
                    }
                } catch (\MongoException $e) {

                }
            }
        }

        return $value;
    }
}