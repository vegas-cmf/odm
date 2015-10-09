<?php
/**
 * @author Sławomir Żytko <slawek@amsterdam-standard.pl>
 * @homepage http://amsterdam-standard.pl
 */

namespace Vegas\ODM\Collection;
use Vegas\ODM\Collection;

/**
 * Class CursorLazyLoading
 * @package Vegas\ODM\Decorator
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
     * @param \MongoCursor $cursor
     * @param Collection $collectionClass
     */
    public function __construct(\MongoCursor $cursor, Collection $collectionClass)
    {
        $this->cursor = $cursor;
        $this->collectionInstance = $collectionClass;
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
        if ($collection::isEagerLoadingEnabled()) {
            $collection->applyMapping();
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
}