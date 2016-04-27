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

namespace Vegas\ODM\Proxy\Generator\File;

use Vegas\ODM\Proxy\Generator\File\Exception\FileNotWritableException;
use Vegas\ODM\Proxy\Generator\File;
use Vegas\ODM\Proxy\Generator\Helper;
use Zend\Code\Generator\ClassGenerator;

/**
 * Class Writer
 * @package Vegas\ODM\Proxy\Generator\File
 */
class Writer
{
    /**
     * @var File
     */
    protected $file;

    /**
     * @param ClassGenerator $classGenerator
     * @return string
     * @throws FileNotWritableException
     */
    public function generate(ClassGenerator $classGenerator)
    {
        $className = trim($classGenerator->getNamespaceName(), '\\') . '\\' . trim($classGenerator->getName(), '\\');
        $generatedCode = $classGenerator->generate();
        $fileName = $this->file->getProxyFileName($className);

        set_error_handler(function(){});

        try {
            $this->write("<?php\n\n" . $generatedCode, $fileName);
        } catch (FileNotWritableException $exception) {
            throw $exception;
        } finally {
            restore_error_handler();
        }

        return $fileName;
    }

    /**
     * @param $source
     * @param $location
     * @throws FileNotWritableException
     */
    protected function write($source, $location)
    {
        $tmpFileName = $location . '.' . uniqid('', true);

        if (!file_put_contents($tmpFileName, $source)) {
            throw new FileNotWritableException($tmpFileName);
        }

        if (!rename($tmpFileName, $location)) {
            unlink($tmpFileName);
            throw new FileNotWritableException($tmpFileName);
        }
    }

    /**
     * @param File $file
     */
    public function setFile(File $file)
    {
        $this->file = $file;
    }
}
