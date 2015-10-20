Vegas CMF ODM
=============

Example usage
-------------

*Collections definition*
```php
namespace Fixtures\Collection;

use \Vegas\ODM\Collection;

class Category extends Collection
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $desc;

    /**
     * @var \Fixtures\Collection\Category
     * @Mapper
     */
    protected $category;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDesc()
    {
        return $this->desc;
    }

    /**
     * @param mixed $desc
     */
    public function setDesc($desc)
    {
        $this->desc = $desc;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function getSource()
    {
        return 'vegas_app_categories';
    }
}

//---------------------
namespace Fixtures\Collection;

use \Vegas\ODM\Collection;

class Product extends Collection
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Fixtures\Collection\Category
     * @Mapper
     */
    protected $category;

    /**
     * @var int
     * @Mapper
     */
    protected $price;

    /**
     * @var \MongoDate
     * @Mapper \Vegas\ODM\Mapping\Mapper\MongoDate
     */
    protected $createdAt;

    /**
     * @var boolean
     * @Mapper
     */
    protected $isActive;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return \MongoDate
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    public function getSource()
    {
        return 'vegas_app_products';
    }
}
```

*Working with documents*
```php
$parentCategory = new Category();
$parentCategory->setName('Category 0');
$parentCategory->setDesc('Category 0 desc');
$parentCategory->save();

$category = new Category();
$category->setName('Category 1');
$category->setDesc('Category 1 desc');
$category->setCategory($parentCategory);
$category->save();

$product = new Product();
$product->setName('Product 1');
$product->setPrice(100);
$product->setIsActive(true);
$product->setCategory($category);
$product->setCreatedAt(time());

$product->save();

// by default Eager loading is enabled

$testProduct = Product::findFirst();
var_dump($testProduct->getCategory()->getName()); // Category 1
var_dump($testProduct->getCreatedAt()); // \MongoDate
var_dump($testProduct->getPrice()); // 100
var_dump($testProduct->getCategory()->getCategory()->getName()); // Category 0

// with disabled eager loading - efficient for big dataset

$testProduct = Product::findFirst();
var_dump($testProduct->getCategory()); // MongoId
var_dump($testProduct->getCreatedAt()); // int
var_dump($testProduct->isActive()); // true
var_dump($testProduct->getPrice()); // 100
var_dump($testProduct->getCategory()->getCategory()->getName()); // error!
```

Mapping cache
-------------

```php
$config = new \Phalcon\Config([
    'mapping' => [
        'cache' => [
            'frontend' => [
                'driverClass' => 'Phalcon\Cache\Frontend\Output',
                'parameters' => [
                    'lifetime' => 3600
                ]
            ],
            'backend' => [
                'driverClass' => '\Phalcon\Cache\Backend\Mongo',
                'parameters' => [
                    'server' => 'localhost',
                    'db' => 'vegas_test',
                    'collection' => 'cache'
                ]
            ],
        ]
    ]
]);

$di->set('odmMappingCache', function() use ($di, $config) {
    $frontCacheClass = $config->mapping->cache->frontend->driverClass;
    $frontCache = new $frontCacheClass(
        $config->mapping->cache->frontend->parameters->toArray()
    );
    $backCacheClass = $config->mapping->cache->backend->driverClass;
    $cache = new $backCacheClass(
        $frontCache,
        $config->mapping->cache->backend->parameters->toArray()
    );

    return $cache;
}, true);
```

Mapping
-------

Vegas ODM resolves referenced documents automatically. References must be defined in collection class by annotation.
Consider the following code
```php
class Test extends \Vegas\ODM\Collection {
    /**
     * @var \MongoId
     * @mapper \Vegas\ODM\Mapping\Mapper\MongoId
     */
    protected $_id;

    /**
     * @var int
     * @mapper
     */
    protected $int;

    /**
     * @var \MongoDate
     * @Mapper \Vegas\ODM\Mapping\Mapper\MongoDate
     */
    protected $date;
}
```

Annotation *@var* determines the variable type.
Annotation *@mapper* (*@Mapper*) determines that property value will be mapped (casted) to value defined by *@var*.
In *@mapper* annotation you can specify custom mapper class.
(Note! Mapping class must implements interface *\Vegas\ODM\Mapping\MapperInterface*)