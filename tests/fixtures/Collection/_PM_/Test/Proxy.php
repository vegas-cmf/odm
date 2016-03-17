<?php
/**
 * This file is part of Vegas package
 *
 * @author Mateusz Aniołek <mateusz.aniolek@amsterdam-standard.pl>
 * @copyright Amsterdam Standard Sp. Z o.o.
 * @homepage http://vegas-cmf.github.io
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fixtures\Collection\_PM_\Test;

use \Vegas\ODM\Collection;

class Proxy extends Collection
{
    private $proxy;

    public function setProxy($proxy)
    {
        $this->proxy = (string) $proxy;
    }

    public function getProxy()
    {
        return $this->proxy;
    }

    public function getSource()
    {
        return 'vegas_proxy';
    }
}