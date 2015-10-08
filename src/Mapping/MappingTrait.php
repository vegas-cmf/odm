<?php
/**
 * @author Sławomir Żytko <slawek@amsterdam-standard.pl>
 * @homepage http://amsterdam-standard.pl
 */

namespace Vegas\ODM\Mapping;


trait MappingTrait
{
    private $cache = [];

    private function getCacheKey($className, $value)
    {
        if (!is_string($value)) {
            $value = serialize($value);
        }
        return md5($className . $value);
    }

    private function mapField($className, $value)
    {
        $cacheKey = $this->getCacheKey($className, $value);
        if (!isset($this->cache[$cacheKey])) {
            $class = new \ReflectionClass($className);
            $this->cache[$cacheKey] = $class->getMethod('getMapped')->invoke(null, $value);
        }
        return clone $this->cache[$cacheKey];
    }

    public function applyMapping()
    {
        $metadata = $this->getMetadata();
        foreach ($metadata as $field => $mapperClassName) {
            if (isset($this->{$field})) {
                $this->{$field} = $this->mapField($mapperClassName, $this->{$field});
            }
        }
    }

    abstract public function getMetadata();
}