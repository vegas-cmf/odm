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
 
namespace Vegas\ODM\Mongo;

use MongoDB\BSON\ObjectID;

/**
 * Class DbRef
 * Allows to create object with former MongoDBRef structure directly from \Phalcon\Mvc\Collection object
 * <code>
 * $user = \User\Models\User::findFirst();
 * $doc = new \Content\Models\Article();
 *
 * //create only from entity
 * $doc->creator = \Vegas\ODM\Adapter\Mongo\DbRef::create($user);
 * //create from collection name and entity
 * $doc->creator = \Vegas\ODM\Adapter\Mongo\DbRef::create('User', $user);
 * //create from collection name and ID
 * $doc->creator = \Vegas\ODM\Adapter\Mongo\DbRef::create('User', $user->getId());
 * </code>
 *
 * @package Vegas\ODM\Mongo
 * @return array
 */
class DbRef
{
    /**
     * @static
     * @var $refKey
     */
    protected static $refKey = '$ref';

    /**
     * @static
     * @var $idKey
     */
    protected static $idKey = '$id';

    /**
     * @static
     * @var $dbKey
     */
    protected static $dbKey = '$db';

    /**
     * Creates a new database reference
     *
     * @param string|\Phalcon\Mvc\Collection $collection
     * @param mixed|ObjectID|\Phalcon\Mvc\Collection $id
     * @param string $database
     * @return array
     */
    public static function create($collection, $id = null, $database = null)
    {
        if ($collection instanceof \Phalcon\Mvc\Collection) {
            $id = $collection->getId();
            $collection = $collection->getSource();
        }
        if ($id instanceof \Phalcon\Mvc\Collection) {
            $id = $id->getId();
        }
        if (is_array($collection) && self::isRef($collection)) {
            if (isset($collection[self::$idKey])) {
                $id = $collection[self::$idKey];
            }
            if (isset($collection[self::$refKey])) {
                $collection = $collection[self::$refKey];
            }
        }
        if (!$id instanceof ObjectID && $id !== null) {
            $id = new ObjectID($id);
        }

        if ($collection instanceof ObjectID) {
            return $collection;
        }

        if ($id === null) {
            return null;
        }

        $ref = [
            self::$idKey    => $id,
            self::$refKey   => $collection
        ];
        if (is_string($database)) {
            $ref[self::$dbKey] = $database;
        }

        return $ref;
    }

    /**
     * This not actually follow the reference, so it does not determine if it is broken or not.
     * It merely checks that $ref is in valid database reference format (in that it is an object or array with $ref and $id fields).
     *
     * @link http://php.net/manual/en/mongodbref.isref.php
     * @static
     * @param mixed $ref Array or object to check
     * @return boolean Returns true if $ref is a reference
     */
    public static function isRef($ref)
    {
        if (is_array($ref)) {
            return array_key_exists(self::$idKey, $ref) && array_key_exists(self::$refKey, $ref);
        } elseif (is_object($ref)) {
            return property_exists($ref, self::$idKey) && property_exists($ref, self::$refKey);
        }

        return false;
    }
}
