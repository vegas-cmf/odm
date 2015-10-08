<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */


namespace Vegas\ODM;


use Vegas\ODM\Adapter\Mongo\DbRef;
use Vegas\ODM\Collection\LazyLoadingCursor;
use Vegas\ODM\Traits\WriteAttributesTrait;

class Collection extends \Phalcon\Mvc\Collection
{
    use WriteAttributesTrait;

    public function setSource($source)
    {
        $this->_source = $source;
    }

    protected static $eagerLoading = true;

    public static function disableEagerLoading()
    {
        static::$eagerLoading = false;
    }

    public static function enableEagerLoading()
    {
        static::$eagerLoading = true;
    }

    public static function isEagerLoadingEnabled()
    {
        return static::$eagerLoading;
    }

    /**
     * Event fired when record is being created
     */
    public function beforeCreate()
    {
        $this->created_at = new \MongoInt32(time());
    }

    /**
     * Event fired when record is being updated
     */
    public function beforeUpdate()
    {
        $this->updated_at = new \MongoInt32(time());
    }

    public static function getMapped($value)
    {
        if (DbRef::isRef($value)) {
            $value = $value['$id'];
        } else if ($value instanceof Collection) {
            $value = $value->getId();
        }
        return static::findById($value);
    }

    public static function createReference($value)
    {
        return DbRef::create($value);
    }

    protected function getMetadataCacheKey()
    {
        return '_metadata_' . md5(static::class);
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

    public static function find($parameters = null)
    {
        $className = get_called_class();
        /** @var Collection $collection */
        $collection = new $className;
        $cursor = static::_getResultCursor($parameters, $collection, $collection->getConnection());

        return new LazyLoadingCursor($cursor, $collection);
    }

    public static function findFirst($parameters = null)
    {
        $row = parent::findFirst($parameters);
        if ($row && static::isEagerLoadingEnabled()) {
            $row->applyMapping();
        }

        return $row;
    }

    public function save()
    {
        $metadata = $this->getMetadata();
        foreach (get_object_vars($this) as $object => $value) {
            if (isset($metadata[$object])) {
                $reflectionClass = new \ReflectionClass($metadata[$object]);
                if ($reflectionClass->isSubclassOf(MapperInterface::class)) {
                    $this->{$object} = $reflectionClass->getMethod('createReference')->invoke(null, $this->{$object});
                }
            }
        }
        return parent::save();
    }
}