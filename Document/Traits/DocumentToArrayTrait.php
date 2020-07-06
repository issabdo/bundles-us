<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 17/05/16
 * Time: 13:24
 */

namespace Us\Bundle\SecurityBundle\Document\Traits;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\PersistentCollection;

trait DocumentToArrayTrait
{
    public function toArrayWithSuffix($suffix, $propsToExclude = [], $exceptNull = false)
    {
        return $this->toArray($propsToExclude, $suffix, $exceptNull);
    }

    public function toArray($propsToExclude = [], $suffix = '', $exceptNull = false, $propsToInclude = [])
    {
        $getter_names = get_class_methods(get_class($this));
        $gettable_attributes = array();
        foreach ($getter_names as $key => $funcName) {
            $propName = strtolower(substr($funcName, 3, 1));
            $propName .= substr($funcName, 4);
            if (in_array($propName, $propsToExclude)) {
                continue;
            }
            $tempPropName = !$suffix ? $propName : str_replace($suffix, '' , $propName);
            $propExists = function(&$propName) {
                $snakeName = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $propName));
                if (property_exists(get_class($this), $propName)) {
                    return true;
                } else if (property_exists(get_class($this), $snakeName)) {
                    $propName = $snakeName;
                    return true;
                }
                return false;
            };

            $isPictureField = $this->isPictureField($tempPropName);
            if ((substr($funcName, 0, 3) === 'get' || substr($funcName, 0, 3) === 'has') || $isPictureField && $propExists($tempPropName)) {
                if ($isPictureField) {
                    $value = $this->$tempPropName;
                } else {
                    $value = $this->$funcName();
                }
//                if (is_object($value) && get_class($value) == 'Doctrine\ODM\MongoDB\PersistentCollection') {
//                if ($suffix && $suffix === substr($funcName.$suffix, (strlen($funcName.$suffix)-strlen($suffix)))) {
                if ($suffix) {
                    $suffixFuncName = $funcName . $suffix;
                    if (method_exists($this, $suffixFuncName)) {
                        $value = $this->$suffixFuncName();
                    }
                }
                if ($value instanceof PersistentCollection || $value instanceof ArrayCollection) {
                    $values = array();
                    $collection = $value;
                    foreach ($collection as $obj) {
                        $values[] = $obj->toArray($propsToExclude);
                    }
                    if ($exceptNull && $values === null) {
                        continue;
                    }
                    $gettable_attributes[$tempPropName] = $values;
                } else {
                    if (!$value instanceof \DateTime && is_object($value) && !isset($gettable_attributes[$propName])) {
                        $gettable_attributes[$tempPropName] = $value->toArray($propsToExclude);
                        continue;
                    }
                    if ($exceptNull && $value === null) {
                        continue;
                    }
                    $gettable_attributes[$tempPropName] = $value;
                }
            }
        }
        return $gettable_attributes;
    }

    public function isPictureField($propName)
    {
        if (substr($propName, 0, strlen('temp')) === 'temp') {
            return false;
        }
        if (substr($propName, -strlen('Picture')) !== 'Picture') {
            return false;
        }
        return true;
    }
}

