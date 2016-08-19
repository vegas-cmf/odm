<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Fixtures\Collection;

use \Vegas\ODM\Collection;

class Product extends Collection
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Fixtures\Collection\Category
     * @Mapper
     */
    protected $category;

    /**
     * @var \Fixtures\Collection\Tag
     * @Mapper
     */
    protected $tags;

    /**
     * @var int
     * @Mapper
     */
    protected $price;

    /**
     * @var \MongoDate
     * @Mapper \Vegas\ODM\Mapping\Mapper\UTCDateTime
     */
    protected $createdAt;

    /**
     * @var boolean
     * @Mapper
     */
    protected $isActive;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return Tag
     */
    public function getTag()
    {
        return $this->tags;
    }

    /**
     * @param Tag $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return \MongoDate
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    public function getSource()
    {
        return 'vegas_app_products';
    }

    /**
     * @param $params
     * @param $collection
     * @param $connection
     * @return mixed
     * @throws \Exception
     */
    public static function _getCursor($params, $collection, $connection) {
        return static::_getResultCursor($params, $collection, $connection);
    }
}