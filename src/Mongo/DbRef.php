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

/**
 * Class DbRef
 * Allows to create MongoDBRef directly from \Phalcon\Mvc\Collection object
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
class DbRef extends \MongoDBRef
{

    /**
     * Creates a new database reference
     *
     * @param string|\Phalcon\Mvc\Collection $collection
     * @param mixed|\MongoId|\Phalcon\Mvc\Collection $id
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
            if (isset($collection['$id'])) {
                $id = $collection['$id'];
            }
            if (isset($collection['$ref'])) {
                $collection = $collection['$ref'];
            }
        }
        if (!$id instanceof \MongoId && $id !== null) {
            $id = new \MongoId($id);
        }

        if ($collection instanceof \MongoId) {
            return $collection;
        }

        if ($id === null) {
            return null;
        }

        return parent::create($collection, $id, $database);
    }
}
