<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */


namespace Vegas\ODM;


use Vegas\ODM\Mapping\Cache\MappingCacheAwareInterface;
use Vegas\ODM\Mapping\Mapper\Scalar;
use Vegas\ODM\Mongo\DbRef;
use Vegas\ODM\Collection\LazyLoadingCursor;
use Vegas\ODM\Mapping\MapperInterface;
use Vegas\ODM\Mapping\MappingTrait;
use Vegas\ODM\Mapping\MetadataExtractorTrait;

class Collection extends \Phalcon\Mvc\Collection implements MapperInterface
{
    use MappingTrait;

    use MetadataExtractorTrait;

    /**
     * @var bool
     */
    protected static $eagerLoading = true;

    /**
     * Disables references eager loading
     */
    public static function disableEagerLoading()
    {
        static::$eagerLoading = false;
    }

    /**
     * Enables references eager loading
     */
    public static function enableEagerLoading()
    {
        static::$eagerLoading = true;
    }

    /**
     * Determines if eager loading is enabled
     *
     * @return bool
     */
    public static function isEagerLoadingEnabled()
    {
        return static::$eagerLoading;
    }

    /**
     * Event fired when record is being created
     */
    public function beforeCreate()
    {
    }

    /**
     * Event fired when record is being updated
     */
    public function beforeUpdate()
    {
    }

    /**
     * @param $value
     * @return \Phalcon\Mvc\Collection
     */
    public static function getMapped($value)
    {
        if (DbRef::isRef($value)) {
            $value = $value['$id'];
        } else if ($value instanceof Collection) {
            $value = $value->getId();
        }
        return static::findById($value);
    }

    /**
     * @param $value
     * @return array
     */
    public static function createReference($value)
    {
        return DbRef::create($value);
    }

    /**
     * @return string
     */
    protected function getMetadataCacheKey()
    {
        return '_metadata_' . md5(static::class);
    }

    /**
     * Simple method that sets attributes from specified array
     *
     * @param $attributes
     */
    public function writeAttributes($attributes)
    {
        foreach ($attributes as $attribute => $value) {
            $this->writeAttribute($attribute, $value);
        }
    }

    /**
     * @param $params
     * @param Collection $collection
     * @param $connection
     * @return mixed
     * @throws \Exception
     */
    protected static function _getResultCursor($params, $collection, $connection)
    {
        $source = $collection->getSource();
        if (empty($source)) {
            throw new \Exception('Method getSource() returns empty string');
        }

        /** @var \MongoCollection $mongoCollection */
        $mongoCollection = $connection->selectCollection($source);

        if (!is_object($mongoCollection)) {
            throw new \Exception('Couldn\'t select mongo collection');
        }

        /**
         * Convert the string to an array
         */
        if (!isset($params[0])) {
            if (!isset($params['conditions'])) {
                $conditions = [];
            } else {
                $conditions = $params['conditions'];
            }
        } else {
            $conditions = $params[0];
        }
        if (!is_array($conditions)) {
            throw new \Exception('Find parameters must be an array');
        }

        /**
         * Perform the find
         */
        if (isset($params['fields'])) {
            $documentsCursor = $mongoCollection->find($conditions, $params['fields']);
        } else {
            $documentsCursor = $mongoCollection->find($conditions);
        }

        /**
         * Check if a 'limit' clause was defined
         */
        if (isset($params['limit'])) {
            $documentsCursor->limit($params['limit']);
        }

        /**
         * Check if a 'sort' clause was defined
         */
        if (isset($params['sort'])) {
            $documentsCursor->sort($params['sort']);
        }

        /**
         * Check if a 'skip' clause was defined
         */
        if (isset($params['skip'])) {
            $documentsCursor->skip($params['skip']);
        }

        /**
         * If a group of specific fields are requested we use a Phalcon\Mvc\Collection\Document instead
         */
        if (isset($params['fields'])) {
            $documentsCursor->fields($params['fields']);
        }

        return $documentsCursor;
    }

    public static function find(array $parameters = null)
    {
        $className = get_called_class();
        /** @var Collection $collection */
        $collection = new $className;
        $cursor = static::_getResultCursor($parameters, $collection, $collection->getConnection());

        return new LazyLoadingCursor($cursor, $collection);
    }

    public function writeAttribute($a,$v) {
        $this->{$a} = $v;
    }

    public static function findFirst(array $parameters = null)
    {
        $className = get_called_class();
        /** @var Collection $collection */
        $collection = new $className;
        $cursor = static::_getResultCursor($parameters, $collection, $collection->getConnection());

        $cursor->next();
        $collection->writeAttributes((array) $cursor->current());
        if ($collection::isEagerLoadingEnabled()) {
            $collection->applyMapping();
        }
        return $collection;
    }

    public function save()
    {
        $metadata = $this->getMetadata();

        $currentValues = [];

        foreach (get_object_vars($this) as $object => $value) {
            if (isset($metadata[$object])) {
                $currentValues[$object] = $this->{$object};
                if (Scalar::isScalar($metadata[$object])) {
                    $this->{$object} = Scalar::map($this->{$object}, $metadata[$object]);
                } else {
                    $reflectionClass = new \ReflectionClass($metadata[$object]);
                    if ($reflectionClass->isSubclassOf(MapperInterface::class)) {
                        $this->{$object} = $reflectionClass->getMethod('createReference')->invoke(null, $this->{$object});
                    }
                }
            }
        }

        $result = parent::save();

        foreach ($currentValues as $object => $value) {
            $this->{$object} = $value;
        }

        return $result;
    }

    public function toArray()
    {
        $reserved = $this->getReservedAttributes();
        $data = [];
        foreach (get_object_vars($this) as $k => $v) {
            if (!isset($reserved[$k])) {
                $data[$k] = $v;
            }
        }

        return $data;
    }

    /**
     * Returns an array with reserved properties that cannot be part of the insert/update
     */
    public function getReservedAttributes()
    {
        $reserved = self::$_reserved;
        if ($reserved === null) {
            $reserved = [
                "_connection" => true,
                "_dependencyInjector" => true,
                "_source" => true,
                "_operationMade" => true,
                "_errorMessages" => true,
                "_modelsManager" => true,
                "_skipped" => true,
                "cache" => true,
                "metadataCache" => true
            ];
            self::$_reserved = $reserved;
        }
        return $reserved;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getMappingCacheKey($value)
    {
        // TODO: Implement getMappingCacheKey() method.
    }

    private $mappingFieldsCache = [];

    private function getCacheKey($className, $value)
    {
        if (!is_string($value)) {
            $value = serialize($value);
        }
        return md5($className . $value);
    }

    /**
     * @param $type
     * @param $value
     * @return mixed
     */
    private function mapField($type, $value)
    {
        $cacheKey = $this->getCacheKey($type, $value);
        if (!isset($this->mappingFieldsCache[$cacheKey])) {
            if (Scalar::isScalar($type)) {
                $this->mappingFieldsCache[$cacheKey] = Scalar::map($value, $type);
            } else {
                $class = new \ReflectionClass($type);
                $this->mappingFieldsCache[$cacheKey] = $class->getMethod('getMapped')->invoke(null, $value);
            }

        }

        return is_object($this->mappingFieldsCache[$cacheKey]) ? clone $this->mappingFieldsCache[$cacheKey] : $this->mappingFieldsCache[$cacheKey];
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
}