<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 17/05/16
 * Time: 13:24
 */

namespace Us\Bundle\SecurityBundle\Document\Traits;


use ReflectionClass;

trait DynamicConstantsTrait
{
    public static function dynamicConstantExist($constantStartingPattern, $searchingValue)
    {
        $refl = new ReflectionClass(get_called_class());
        $constantsList = $refl->getConstants();
        foreach ($constantsList as $constantName => $constantValue) {
            if ($constantStartingPattern === substr($constantName, 0, strlen($constantStartingPattern))) {
                if ($constantValue === $searchingValue) {
                    return true;
                }
            }
        }
        return false;
    }
}

