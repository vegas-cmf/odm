<?php
/**
 * This file is part of Vegas package
 *
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @copyright Amsterdam Standard Sp. Z o.o.
 * @homepage http://vegas-cmf.github.io
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vegas\ODM\Adapter\Mongo\Exception;

/**
 * Class InvalidReferenceException
 * @package Vegas\ODM\Adapter\Mongo\Exception
 */
class InvalidReferenceException extends \Vegas\ODM\Exception
{
    protected $message = 'Object is not in valid database reference format';
}
