<?php
/**
 * @author Sławomir Żytko <slawek@amsterdam-standard.pl>
 * @homepage http://amsterdam-standard.pl
 */

namespace Vegas\ODM\Mapping;


trait MetadataExtractorTrait
{
    protected $metadataCache = [];

    public function getMetadata()
    {
        $cacheKey = $this->getMetadataCacheKey();
        if (!isset($this->metadataCache[$cacheKey])) {
            $this->metadataCache[$cacheKey] = (new \Vegas\ODM\Mapping\Driver\Annotation(static::class))->getAnnotations();
        }
        return $this->metadataCache[$cacheKey];
//        return $annotations = (new \Vegas\ODM\Mapping\Driver\Annotation(static::class))->getAnnotations();;
        if ($this->getDI()->has('mappingCache')) {
            $cache = $this->getDI()->get('mappingCache');
            $cacheKey = $this->getMetadataCacheKey();
            if (!$cache->exists($cacheKey)) {
                $annotations = (new \Vegas\ODM\Mapping\Driver\Annotation(static::class))->getAnnotations();
                $this->getDI()->get('mappingCache')->save($cacheKey, $annotations);
            } else {
                $annotations = $cache->get($cacheKey);
            }
        } else {
            $annotations = (new \Vegas\ODM\Mapping\Driver\Annotation(static::class))->getAnnotations();
        }
        return $annotations ? $annotations : [];
    }

    abstract public function getDI();

    abstract protected function getMetadataCacheKey();
}