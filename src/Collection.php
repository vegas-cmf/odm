<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\ODM;

use Vegas\ODM\Mapping\Mapper\Scalar;
use Vegas\ODM\Mongo\DbRef;
use Vegas\ODM\Collection\LazyLoadingCursor;
use Vegas\ODM\Mapping\MapperInterface;

/**
 * Class Collection
 * @package Vegas\ODM
 */
class Collection extends \Phalcon\Mvc\Collection implements MapperInterface
{
    /**
     * @var bool
     */
    protected static $eagerLoading = [];

    /**
     * Disables references eager loading
     */
    public static function disableEagerLoading()
    {
        static::$eagerLoading[static::class] = false;
    }

    /**
     * Enables references eager loading
     */
    public static function enableEagerLoading()
    {
        static::$eagerLoading[static::class] = true;
    }

    /**
     * Determines if eager loading is enabled
     * By default is enabled
     *
     * @return bool
     */
    public static function isEagerLoadingEnabled()
    {
        return isset(static::$eagerLoading[static::class]) ? static::$eagerLoading[static::class]: true;
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
    final public static function getMapped($value)
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
    final public static function createReference($value)
    {
        return DbRef::create($value);
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

    /**
     * @param array|null $parameters
     * @return LazyLoadingCursor
     * @throws \Exception
     */
    public static function find($parameters = null)
    {
        $className = get_called_class();
        /** @var Collection $collection */
        $collection = new $className;
        $cursor = static::_getResultCursor($parameters, $collection, $collection->getConnection());

        return new LazyLoadingCursor($cursor, $collection);
    }

    /**
     * @param array|null $parameters
     * @return Collection
     * @throws \Exception
     */
    public static function findFirst($parameters = null)
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

    /**
     * @return bool
     */
    public function save()
    {
        $metadata = $this->getMetadata();

        $currentValues = [];

        if (!empty($metadata)) {
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
        }
        $result = parent::save();

        // rollback origin values to model class
        foreach ($currentValues as $object => $value) {
            $this->{$object} = $value;
        }

        return $result;
    }

    /**
     * @return array
     */
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
     * @var array
     */
    private $mappingFieldsCache = [];

    /**
     * @param $className
     * @param $value
     * @return string]
     */
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

        return is_object($this->mappingFieldsCache[$cacheKey])
            ? clone $this->mappingFieldsCache[$cacheKey] : $this->mappingFieldsCache[$cacheKey];
    }

    /**
     *
     */
    public function applyMapping()
    {
        $metadata = $this->getMetadata();
        foreach ($metadata as $field => $mapperClassName) {
            if (isset($this->{$field})) {
                $this->{$field} = $this->mapField($mapperClassName, $this->{$field});
            }
        }
    }

    /**
     * @return string
     */
    protected function getMetadataCacheKey()
    {
        return '_metadata_' . md5(static::class);
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        if ($this->getDI()->has('odmMappingCache')) {
            $cache = $this->getDI()->get('odmMappingCache');
            $cacheKey = $this->getMetadataCacheKey();
            if (!$cache->exists($cacheKey)) {
                $annotations = (new \Vegas\ODM\Mapping\Driver\Annotation(static::class))->getAnnotations();
                print_r($annotations);
                $this->getDI()->get('odmMappingCache')->save($cacheKey, $annotations);
            } else {
                $annotations = $cache->get($cacheKey);
            }
        } else {
            $annotations = (new \Vegas\ODM\Mapping\Driver\Annotation(static::class))->getAnnotations();
        }
        return $annotations ? $annotations : [];
    }
}