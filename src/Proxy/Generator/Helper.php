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
use Vegas\ODM\Proxy\Generator\File\Writer as FileWriter;
use Zend\Code\Generator\ClassGenerator;

/**
 * Class Helper
 * @package Vegas\ODM\Proxy\Generator
 */
class Helper
{
    const PROXY_NAMESPACE = "VegasCMF";
    const PROXY_CONST = "_PM_";

    /**
     * @var FileWriter $fileWriter
     */
    public static $fileWriter;

    /**
     * @param $className
     * @return string
     */
    public static function getUserClassName($className)
    {
        $className = ltrim($className, '\\');

        if (false === $position = strrpos($className, self::PROXY_CONST)) {
            return $className;
        }

        $userClassName = substr(
            $className,
            strlen(self::PROXY_CONST) + $position,
            strrpos($className, '\\') - ($position + strlen(self::PROXY_CONST))
        );
        return $userClassName;
    }

    /**
     * @param ClassGenerator $phpClass
     * @return string
     */
    public static function saveClass($phpClass)
    {
        $tempDirectory = sys_get_temp_dir();
        $fileInstance = new File($tempDirectory);

        self::$fileWriter = self::$fileWriter ?: self::$fileWriter = new FileWriter;
        self::$fileWriter->setFile($fileInstance);

        return self::$fileWriter->generate($phpClass);
    }

    /**
     * @param $className
     * @return string
     */
    public static function getProxyClassName($className)
    {
        $classHash = md5($className . get_current_user());
        $userClassName = self::getUserClassName($className);

        return self::PROXY_NAMESPACE . self::PROXY_CONST . $userClassName . $classHash;
    }

}