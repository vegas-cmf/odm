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

use MongoDB\BSON\UTCDatetime as UTCDatetimeBSON;
use Vegas\ODM\Mapping\MapperInterface;

/**
 * Class UTCDatetime
 * @package Vegas\ODM\Mapping\Mapper
 */
class UTCDatetime implements MapperInterface
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
     * @return UTCDatetimeBSON
     */
    public static function createReference($value)
    {
        if (!$value instanceof UTCDatetimeBSON) {
            if (is_numeric($value)) {
                $value = new UTCDatetimeBSON($value);
            } else if (is_string($value)) {
                if (($strDate = strtotime($value))) {
                    $value = $strDate * 1000;
                }
                $value = new UTCDatetimeBSON(intval($value));
            } else if ($value instanceof \DateTime) {
                $value = new UTCDatetimeBSON($value->getTimestamp() * 1000);
            }
        }

        return $value;
    }
}