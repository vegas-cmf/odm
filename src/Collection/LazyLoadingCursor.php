<?php
/**
 * This file is part of Vegas package
 *
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @copyright Amsterdam Standard Sp. Z o.o.
 * @homepage http://cmf.vegas
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vegas\ODM\Collection;

use Phalcon\Di;
use Vegas\ODM\Collection;
use Vegas\ODM\Proxy;

/**
 * Class LazyLoadingCursor
 * @package Vegas\ODM\Collection
 */
class LazyLoadingCursor implements \Iterator
{
    /**
     * @var \MongoCursor
     */
    protected $cursor;

    /**
     * @var Collection
     */
    protected $collectionInstance;

    /**
     * @var array
     */
    protected $fields = null;

    /**
     * LazyLoadingCursor constructor.
     * @param \MongoCursor $cursor
     * @param Collection $collectionClass
     * @param null $fields
     */
    public function __construct(\MongoCursor $cursor, Collection $collectionClass, $fields = null)
    {
        $this->cursor = $cursor;
        $this->collectionInstance = $collectionClass;
        $this->fields = $fields;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [];
        $this->rewind();
        while ($this->valid()) {
            $array[] = $this->current()->toArray();
            $this->next();
        }
        return $array;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        $collection = clone $this->collectionInstance;
        $collection->writeAttributes((array) $this->cursor->current());
        if ($this->fields) {
            $collection->setCursorFields($this->fields);
        }
        if ($collection::isEagerLoadingEnabled() && $collection->__eager_loading) {
            $collection->applyMapping();
        } else {
            $reflection = new \ReflectionClass($collection);
            $properties = $reflection->getProperties();

            $proxyClass = Proxy::getLazyLoadingClass(get_class($collection), Di::getDefault());
            foreach($properties as $property) {
                if($property->isPrivate() || $property->isProtected()) {
                    $property->setAccessible(true);
                }
                $proxyClass->{$property->getName()} = $property->getValue($collection);
            }

            $collection = $proxyClass;
        }
        return $collection;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->cursor->next();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->cursor->key();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->cursor->valid();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->cursor->rewind();
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->cursor->count();
    }
}