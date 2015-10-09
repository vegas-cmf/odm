<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */


namespace App\Entity;


use Vegas\ODM\Collection;
use Vegas\ODM\Mapping\Mapper as ODM;

class Product extends Collection
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var \App\Entity\Category
     * @Mapper
     */
    protected $category;

    /**
     * @var int
     * @Mapper
     */
    protected $price;

    /**
     * @var \Vegas\ODM\Mapping\Mapper\MongoDate
     * @Mapper
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
    public function setCategory(Category $category)
    {
        $this->category = $category;
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

}