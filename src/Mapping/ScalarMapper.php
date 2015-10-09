<?php

/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\ODM\Mapping;

class ScalarMapper
{
    private static $scalarTypes = [
        'int',
        'float',
        'boolean',
        'array'
    ];

    public static function isScalar($typeName)
    {
        return in_array($typeName, self::$scalarTypes);
    }

    /**
     * @param $value
     * @return int
     */
    public static function mapInt($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT);
    }

    /**
     * @param $value
     * @return int
     */
    public static function mapFloat($value)
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT);
    }

    /**
     * @param $value
     * @return boolean
     */
    public static function mapBoolean($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param $value
     * @return array
     */
    public static function mapArray($value)
    {
        return (array) filter_var_array($value);
    }

    /**
     * @param $value
     * @param $type
     * @return mixed
     */
    public static function map($value, $type)
    {
        return forward_static_call([static::class, 'map'.ucfirst($type)], $value);
    }
}