<?php
/**
 * This file is part of Vegas package
 *
 * @author Mateusz Aniolek <mateusz.aniolek@amsterdam-standard.pl>
 * @copyright Amsterdam Standard Sp. Z o.o.
 * @homepage http://cmf.vegas
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vegas\ODM\Mapping\Driver\Exception;

/**
 * Class UnsupportedTargetException
 * @package Vegas\ODM\Mapping\Driver\Exception
 */
class UnsupportedTargetException extends \Vegas\ODM\Exception
{
    /**
     * @var string
     */
    protected $message = 'Unsupported target type given';
}