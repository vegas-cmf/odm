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

use MongoDB\Driver\Cursor;
use Phalcon\Di;
use Vegas\ODM\Collection;
use Vegas\ODM\ProxyBuilder;

/**
 * Class LazyLoadingCursor
 * @package Vegas\ODM\Collection
 */
class LazyLoadingCursor implements \Iterator
{
    /**
     * @var Cursor
     */
    protected $mongoCursor;

    /**
     * @var \IteratorIterator
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
     * @param Cursor $cursor
     * @param Collection $collectionClass
     * @param null $fields
     */
    public function __construct(Cursor $cursor, Collection $collectionClass, $fields = null)
    {
//        $this->mongoCursor = $cursor;
//        $this->cursor = new \IteratorIterator($cursor);
//        $this->cursor->rewind();    // @see http://php.net/manual/en/class.mongodb-driver-cursor.php#118824
        $this->mongoCursor = $cursor->toArray();
        $this->index = 0;
        $this->collectionInstance = $collectionClass;
        $this->fields = $fields;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->mongoCursor;
//        return $this->mongoCursor->toArray();
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
        $collection->writeAttributes($this->mongoCursor[$this->index]->toArray());
        if ($this->fields) {
            $collection->setCursorFields($this->fields);
        }

        if ($collection::isLazyLoadingEnabled()) {
            $proxyClass = ProxyBuilder::getLazyLoadingClass(get_class($collection), Di::getDefault());
            ProxyBuilder::assignProxyValues($proxyClass, $collection);

            $collection = $proxyClass;
        } else {
            $collection->map();
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
        ++$this->index;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->index;
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
        return array_key_exists($this->index, $this->mongoCursor);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->index = 0;
//        throw new \Vegas\Exception('Cannot rewind cursor');
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->toArray());
    }
}