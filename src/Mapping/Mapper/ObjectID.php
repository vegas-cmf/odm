<?php
/**
 * This file is part of Vegas package
 *
 * @author Radosław Fąfara <radek@amsterdamstandard.com>
 * @copyright Amsterdam Standard Sp. Z o.o.
 * @homepage http://cmf.vegas
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vegas\ODM\Mapping\Mapper;

use MongoDB\BSON\ObjectID as ObjectIDBSON;
use MongoDB\Driver\Exception\InvalidArgumentException;
use Vegas\ODM\Mapping\MapperInterface;
use Vegas\ODM\Mongo\DbRef;

/**
 * Class ObjectID
 * @package Vegas\ODM\Mapping\Mapper
 */
class ObjectID implements MapperInterface
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
     * @return ObjectIDBSON
     */
    public static function createReference($value)
    {
        if (!$value instanceof ObjectIDBSON && $value !== null) {
            try {
                $value = new ObjectIDBSON($value);
            } catch (InvalidArgumentException $e) {
                if (DbRef::isRef($value)) {
                    $value = new ObjectIDBSON($value['$id']);
                } else {
                    $value = new ObjectIDBSON(trim($value));
                }
            }
        }

        return $value;
    }
}