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

namespace Fixtures\Collection\Lib;

use Phalcon\Di;
use Vegas\ODM\Collection;
use Vegas\ODM\Proxy;

/**
 * Class ExtendedCursor
 * @package Vegas\ODM\Collection
 */
class ExtendedCursor extends Collection\LazyLoadingCursor
{
    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

}