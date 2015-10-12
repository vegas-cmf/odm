<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */


namespace Vegas\ODM\Mapping\Cache;


class Memory
{
    protected static $cache = [];

    public function set($name, $value)
    {
        self::$cache[$name] = $value;
    }

    public function get($name)
    {

    }
}