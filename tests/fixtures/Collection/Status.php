<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Fixtures\Collection;

use \Vegas\ODM\Collection;
use Vegas\ODM\Mapping\MapperInterface;
use Vegas\ODM\Mongo\DbRef;

class Status implements MapperInterface
{
    protected static $list = [];

    /**
     * @var string
     */
    protected $name;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public static function createReference($value)
    {
        return (string)$value->_id;
    }

    public static function getMapped($value)
    {
        if (!$value or !\MongoId::isValid($value)) {
            return false;
        }

        return self::$list[(string)$value];
    }

    public function save()
    {
        $this->_id = new \MongoId();
        self::$list[(string)$this->_id] = $this;
    }
}