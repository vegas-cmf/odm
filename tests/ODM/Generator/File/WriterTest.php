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

namespace Vegas\Tests\ODM;

use Phalcon\Di;
use Vegas\ODM\Collection;
use Vegas\ODM\Proxy\Generator\File;
use Vegas\ODM\Proxy\Generator\File\Exception\FileNotWritableException;
use Zend\Code\Generator\ClassGenerator;


class WriterTest extends \PHPUnit_Framework_TestCase
{
    protected $di;

    public function testWriter()
    {
        $classGenerator = new ClassGenerator('test', 'Test\Model');
        $file = new File(sys_get_temp_dir());

        $writer = new File\Writer();
        $writer->setFile($file);
        $path = $writer->generate($classGenerator);

        $this->assertTrue(file_exists($path));
    }

    /**
     * @expectedException \Vegas\ODM\Proxy\Generator\File\Exception\FileNotWritableException
     */
    public function testPathNotWritable()
    {
        $classGenerator = new ClassGenerator('test', 'Test\Model');
        $file = new File('/root');

        $writer = new File\Writer();
        $writer->setFile($file);
        $path = $writer->generate($classGenerator);

        $this->assertTrue(file_exists($path));
    }

}