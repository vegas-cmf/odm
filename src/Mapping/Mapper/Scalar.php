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

namespace Vegas\ODM\Mapping\Mapper;

/**
 * Class Scalar
 * @package Vegas\ODM\Mapping\Mapper
 */
class Scalar
{
    /**
     * @var array
     */
    private static $scalarTypes = [
        'int',
        'float',
        'boolean',
        'array'
    ];

    /**
     * @param $typeName
     * @return bool
     */
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
        return (array) filter_var_array((array) $value);
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