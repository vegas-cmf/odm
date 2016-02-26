<?php
/**
 * @author Mateusz Aniolek <mateusz.aniolek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Fixtures\Collection;

use Phalcon\DiInterface;
use Phalcon\Mvc\Collection\ManagerInterface;
use \Vegas\ODM\Collection;

class Foo extends Collection
{
    private $foo;

    public function setFoo($foo)
    {
        $this->foo = (string) $foo;
    }

    public function getFoo()
    {
        return $this->foo;
    }

    public function getSource()
    {
        return 'vegas_foo';
    }
}