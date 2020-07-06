<?php
/**
 * Created by PhpStorm.
 * User: fpicard
 * Date: 05/09/16
 * Time: 10:00
 */

namespace Us\Bundle\SecurityBundle\Document\Behavior;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\MongoDB\ArrayIterator;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Doctrine\ODM\MongoDB\PersistentCollection\PersistentCollectionTrait;

/**
 * Behavior permettant la validation du document sur 1 niveau ("EmbeddedDocument" contenus dans les embedMany ou embedOne)
 *
 * Class Traversable
 * @package EEAPIBundle\Document
 */
class Traversable implements \IteratorAggregate
{

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */

    public function getIterator()
    {
        $propertiesToValidateIn = [];

        // TODO: Implement getIterator() method.
        foreach (get_class_methods($this) as $methodName) {

            $isGetter = substr($methodName, 0, 3) === 'get';
            if (!$isGetter) {
                continue;
            }
            $propertyName = strtolower(str_replace('get', '', $methodName));


            if (property_exists($this, $propertyName)) {
                $value = $this->{$methodName}();
                if ($value instanceof ArrayCollection || $value instanceof PersistentCollection) {
                    $propertiesToValidateIn[$propertyName] = $value;
                }
            }
        }

        $iterator = new ArrayIterator($propertiesToValidateIn);

        return $iterator;
    }
}