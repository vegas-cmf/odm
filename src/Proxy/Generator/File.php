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

namespace Vegas\ODM\Proxy\Generator;

use \Vegas\ODM\Proxy\Generator\Exception\DirectoryNotFoundException;

/**
 * Class File
 * @package Vegas\ODM\Proxy\Generator
 */
class File
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * File constructor.
     * @param $directory
     * @throws DirectoryNotFoundException
     */
    public function __construct($directory)
    {
        $this->directory = realpath($directory);

        if (!$this->directory) {
            throw new DirectoryNotFoundException($directory);
        }
    }

    /**
     * @param $className
     * @return string
     */
    public function getProxyFileName($className)
    {
        return $this->directory . DIRECTORY_SEPARATOR . str_replace('\\', '', $className) . '.php';
    }

}