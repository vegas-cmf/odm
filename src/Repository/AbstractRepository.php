<?php

/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Vegas\ODM\Repository;

use Phalcon\Mvc\Collection;
use Vegas\ODM\EntityInterface;

abstract class AbstractRepository
{
    protected $collection;

    public function __construct(EntityInterface $entity)
    {
        $collection = new \Vegas\ODM\Collection();
        $collection->setSource($entity->getSource());
        $this->collection = $collection;
    }

    public function findById($id)
    {

    }

    public function findAll($parameters)
    {

    }

    public function findOne($parameters)
    {

    }
}