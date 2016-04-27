<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Fixtures\Collection;

use \Vegas\ODM\Collection;

class Tag extends Collection
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Fixtures\Collection\Status
     * @Mapper
     */
    protected $status;

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
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getSource()
    {
        return 'vegas_app_products_tags';
    }
}