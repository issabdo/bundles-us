<?php

namespace Us\Bundle\SecurityBundle\Document\Traits;

use Doctrine\Common\Collections\ArrayCollection;

trait SerializableTrait
{
    protected function collectionSetter($propertyName, $value, $collectionObject = null)
    {
        if (is_array($value)) {
            $this->$propertyName = !$collectionObject ? new ArrayCollection() : $collectionObject;
            if (!$this->$propertyName instanceof ArrayCollection) {
                foreach ($value as $i => $val) {
                    $setter = 'set' . ucfirst($i);
                    if (method_exists($this->$propertyName, $setter)) {
                        $this->$propertyName->$setter($val);
                    }
                }
            } else {
                foreach ($value as $i => $val) {
                    $adder = 'add' . ucfirst($propertyName);
                    $this->$adder($val);
                    //                $this->$propertyName->add($embedObject);
                }
            }
        } else {
            $this->$propertyName = $value;
        }
    }

    protected function addToCollection($propertyName, $embedObject, $value = null, $collectionObject = null)
    {
        if ($this->$propertyName == null) {
//            $this->$propertyName =  !$collectionObject ? new ArrayCollection() : $collectionObject;
            $this->$propertyName = new ArrayCollection();
        }

        if (null == $embedObject) {
            return;
        }

        if (is_array($value)) {
            foreach ($value as $i => $prop) {
                $setter = 'set' . ucfirst($i);
                if (method_exists($embedObject, $setter)) {
                    $embedObject->$setter($prop);
                }
            }
            return;
        }

        if (is_object($value)) {
            $this->$propertyName->add($value);
            return;
        }

        throw new \LogicException(sprintf('Can not add such $value for field "%s"', $propertyName));
    }
}