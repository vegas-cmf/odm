<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\ODM;

use Phalcon\Di;
use Phalcon\DiInterface;
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
    private $__operation = false;

    /**
     * @var bool
     */
    public $__lazy_loading = true;

    /**
     * @var bool
     */
    public $__is_mapped = false;

    /**
     * @var bool
     */
    public $__is_property_mapped = [];

    /**
     * @var array
     */
    private $mappingFieldsCache = [];

    /**
     * @var array|null
     */
    private $__cursorFields = null;

    /**
     * @var bool
     */
    protected static $lazyLoading = [];

    /**
     * @var array
     */
    protected static $lazyLoadingCache = [];

    /**
     * Disables references lazy loading
     */
    public static function disableLazyLoading()
    {
        static::$lazyLoading[static::class] = false;
    }

    /**
     * Enables references lazy loading
     */
    public static function enableLazyLoading()
    {
        static::$lazyLoading[static::class] = true;
    }

    /**
     * Determines if lazy loading is enabled
     * By default is disabled
     *
     * @return bool
     */
    public static function isLazyLoadingEnabled()
    {
        return isset(static::$lazyLoading[static::class]) ? static::$lazyLoading[static::class]: true;
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
     * @return \Phalcon\Mvc\Collection|bool
     */
    final public static function getMapped($value)
    {
        if (!$value) {
            return false;
        }
        if (DbRef::isRef($value)) {
            $value = $value['$id'];
        } else if ($value instanceof Collection) {
            $value = $value->getId();
        }

        return static::ensureMappingCache($value);
    }

    /**
     * @param $value
     * @return mixed
     */
    protected static function ensureMappingCache($value)
    {
        $calledClass = get_called_class();
        $cacheKey = sprintf('%s:%s', $calledClass, $value);

        if (!isset(static::$lazyLoadingCache[$cacheKey])) {

            $collection = static::findById($value);

            if($collection && isset(static::$lazyLoading[$calledClass]) && static::$lazyLoading[$calledClass] == true) {
                $proxyClass = ProxyBuilder::getLazyLoadingClass($calledClass, Di::getDefault());
                ProxyBuilder::assignProxyValues($proxyClass, $collection);

                $collection = $proxyClass;
            }
            static::$lazyLoadingCache[$cacheKey] = $collection;
        }
        return static::$lazyLoadingCache[$cacheKey];
    }

    /**
     * @param $value
     */
    protected static function clearMappingCache($value)
    {
        $cacheKey = sprintf('%s:%s', get_called_class(), $value);
        if (isset(static::$lazyLoadingCache[$cacheKey])) {
            unset(static::$lazyLoadingCache[$cacheKey]);
        }
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
    public static function find(array $parameters = null)
    {
        $className = get_called_class();
        /** @var Collection $collection */
        $collection = new $className;
        $cursor = static::_getResultCursor($parameters, $collection, $collection->getConnection());

        return new LazyLoadingCursor(
            $cursor,
            $collection,
            is_array($parameters) &&  isset($parameters['fields']) ? $parameters['fields'] : null
        );
    }

    /**
     * @param array|null $parameters
     * @return Collection|false
     * @throws \Exception
     */
    public static function findFirst(array $parameters = null)
    {
        $cursor = static::find($parameters);
        if ($cursor->count() === 0) {
            return false;
        }

        $cursor->next();
        return $cursor->current();
    }

    /**
     * @return bool|void
     * @throws \Exception
     */
    public function save()
    {
        $this->__operation = 'save';

        $source = $this->getSource();
        if (empty($source)) {
            throw new \Exception("Method getSource() returns empty string");
        }

        $connection = $this->getConnection();

        /**
         * Choose a collection according to the collection name
         */
        $collection = $connection->selectCollection($source);

        /**
         * Check the dirty state of the current operation to update the current operation
         */
        $exists = parent::_exists($collection);

        if ($exists === false) {
            $this->_operationMade = self::OP_CREATE;
        } else {
            $this->_operationMade = self::OP_UPDATE;
        }

        /**
         * The messages added to the validator are reset here
         */
        $this->_errorMessages = [];

        $disableEvents = self::$_disableEvents;

        /**
         * Execute the preSave hook
         */
        if (parent::_preSave($this->getDI(), $disableEvents, $exists) === false) {
            return false;
        }

        $data = $this->toArray();
        $metadata = $this->getMetadata();
        $currentValues = [];

        if (!empty($metadata)) {
            foreach ($this->getObjectProperties() as $object => $value) {
                if (isset($metadata[$object])) {
                    $currentValues[$object] = $this->{$object};
                    if (Scalar::isScalar($metadata[$object])) {
                        $data[$object] = Scalar::map($this->{$object}, $metadata[$object]);
                    } else {
                        $reflectionClass = new \ReflectionClass($metadata[$object]);
                        if ($reflectionClass->isSubclassOf(MapperInterface::class)) {
                            $data[$object] = $reflectionClass->getMethod('createReference')
                                ->invoke(null, $this->{$object});
                        }
                    }
                }
            }
        }

        $success = false;
        $status = $collection->save($data, ["w" => true]);
        if (is_array($status)) {
            if (isset($status['ok'])) {
                if ($status['ok']) {
                    $success = true;
                    if ($exists === false) {
                        if (isset($data['_id'])) {
                            $this->_id = $data['_id'];
                        }
                    }
                }
            }
        } else {
            $success = false;
        }

        // clears cache for saved document
        if ($success) {
            $this->clearMappingCache($this->_id);
        }

        $this->__operation = false;
        /**
         * Call the postSave hooks
         */
        return parent::_postSave($disableEvents, $success, $exists);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = [];
        foreach ($this->getObjectProperties() as $k => $v) {
            if (is_object($v) && method_exists($v, 'toArray')) {
                $v = $v->toArray();
            }
            $data[$k] = $v;
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
                '_connection' => true,
                '_dependencyInjector' => true,
                '_source' => true,
                '_operationMade' => true,
                '_errorMessages' => true,
                '_modelsManager' => true,
                '_skipped' => true,
                'cache' => true,
                'metadataCache' => true,
                'di' => true,
                '_collectionManager' => true,
                'mappingFieldsCache' => true,
                '__lazy_loading' => true,
                '__is_mapped' => true,
                '__is_property_mapped' => true,
                '__operation' => true,
                '__cursorFields' => true
            ];
            self::$_reserved = $reserved;
        }
        return $reserved;
    }

    /**
     * @param $fields
     */
    public function setCursorFields($fields)
    {
        $this->__cursorFields = $fields;
    }

    /**
     * @param $className
     * @param $value
     * @return string
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
                $result = $class->getMethod('getMapped')->invoke(null, $value);

                if (is_object($result)) {
                    $object = new \ReflectionObject($result);
                    if ($object->isCloneable()) {
                        $result = clone $result;
                    }
                }

                $this->mappingFieldsCache[$cacheKey] = $result;
            }
        }

        return $this->mappingFieldsCache[$cacheKey];
    }

    /**
     * @return array
     */
    protected function getObjectProperties()
    {
        $reserved = $this->getReservedAttributes();
        return array_filter(get_object_vars($this), function($var) use ($reserved) {
            $allowed = !isset($reserved[$var]);
            if ($this->__cursorFields) {
                $allowed = isset($this->__cursorFields[$var]) && $allowed;
            }

            return $allowed;
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Apply mappings for all defined mappers in collection. It has recursive flow, so deep relation
     * resolving will be performed. For an individual property, use applyMapping(PROPERTY_NAME) method.
     */
    public function map()
    {
        if (!$this->__is_mapped) {
            $metadata = $this->getMetadata();
            foreach ($this->getObjectProperties() as $propName => $value) {
                if (isset($metadata[$propName])) {
                    $this->{$propName} = $this->mapField($metadata[$propName], $this->{$propName});
                    if (is_object($this->{$propName}) && method_exists($this->{$propName}, 'map')) {
                        $this->{$propName}->map();
                    }
                }
            }
            $this->__is_mapped = true;
        }

        return $this;
    }

    /**
     * Apply mappings for one property, given by a name
     * @param $propertyName
     */
    protected function mapProperty($propertyName)
    {
        if (!isset($this->__is_property_mapped[$propertyName])) {
            $metadata = $this->getMetadata();
            if (isset($metadata[$propertyName])) {
                $this->{$propertyName} = $this->mapField($metadata[$propertyName], $this->{$propertyName});
            }
            $this->__is_property_mapped[$propertyName] = true;
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
                $cache->save($cacheKey, $annotations);
            } else {
                $annotations = $cache->get($cacheKey);
            }
        } else {
            $annotations = (new \Vegas\ODM\Mapping\Driver\Annotation(static::class))->getAnnotations();
        }
        return $annotations ? $annotations : [];
    }

    public function __set($name, $value)
    {
        $methodName = 'set' . ucfirst($name);
        if (method_exists($this, $methodName)) {
            call_user_func_array([$this, $methodName], [$value]);
        }
    }
}