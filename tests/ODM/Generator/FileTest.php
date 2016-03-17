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

namespace Vegas\Tests\ODM\Generator;

use Phalcon\Di;
use Vegas\ODM\Collection;
use Vegas\ODM\Proxy\Generator\File;


class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Vegas\ODM\Proxy\Generator\Exception\DirectoryNotFoundException
     */
    public function testDirectoryNotFound()
    {
        $file = new File('/test');
    }

}