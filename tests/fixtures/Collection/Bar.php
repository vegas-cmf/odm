<?php
/**
 * @author Mateusz Aniolek <mateusz.aniolek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */

namespace Fixtures\Collection;

use \Vegas\ODM\Collection;

class Bar extends Collection
{
    /**
     * @type
     */
    private $bar;

    public function setBar($bar)
    {
        $this->bar = (string) $bar;
    }

    public function getBar()
    {
        return $this->bar;
    }

    public function getSource()
    {
        return 'vegas_foo';
    }
}