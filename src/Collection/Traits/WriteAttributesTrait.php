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
 
namespace Vegas\ODM\Collection\Traits;

/**
 * Class WriteAttributesTrait
 * @package Vegas\ODM\Decorator
 */
trait WriteAttributesTrait
{
    /**
     * Simple method that sets attributes from specified array
     *
     * @param $attributes
     */
    public function writeAttributes($attributes)
    {
        foreach ($attributes as $attribute => $value) {
            $this->writeAttribute($attribute, $value);
        }
    }

    /**
     * Writes given value to attribute
     *
     * @param $name
     * @param $value
     * @return mixed
     */
    abstract public function writeAttribute($name, $value);
}
