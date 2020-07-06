<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 19/05/16
 * Time: 13:42
 */

namespace Us\Bundle\SecurityBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Us\Bundle\SecurityBundle\Document\Behavior\Traversable;
//use Store\BaseBundle\Document\Traits\CountriesIsoCodesTrait;
use Us\Bundle\SecurityBundle\Document\Traits\DocumentToArrayTrait;
use Us\Bundle\SecurityBundle\Document\Traits\SerializableTrait;

abstract class BaseDocument extends Traversable
{
    const VIOLATION_PREFIX = '[VIOLATION]';

    const VIOLATION_TYPE_CONFLICT = 'VIOLATION_TYPE_CONFLICT';
    const VIOLATION_TYPE_WRONG_VALUE = 'VIOLATION_TYPE_WRONG_VALUE';
    protected static $violationTypes = [
        self::VIOLATION_TYPE_CONFLICT => 'CONFLIT',
        self::VIOLATION_TYPE_WRONG_VALUE => 'VALEUR INCORRECTE'
    ];

    use DocumentToArrayTrait;

    use SerializableTrait;

//    /**
//     * @ODM\Id
//     */
//    protected $id;

    static function formatViolationAt($violatonType, $message)
    {
        $violation = array_key_exists($violatonType, self::$violationTypes) ? self::VIOLATION_PREFIX . '[' . self::$violationTypes[$violatonType] . ']' : self::VIOLATION_PREFIX;
        return sprintf('%s %s', $violation, $message);
    }

    abstract public function _getName();

    public final function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public final function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    function __clone()
    {
        foreach ($this as $key => $value) {
            $fu = '';
            if (is_object($value)) {
                $this->$key = clone $this->$key;
            } else if (is_array($value)) {
                $newArray = array();
                foreach ($value as $arrayKey => $arrayValue) {
                    $newArray[$arrayKey] = is_object($arrayValue) ?
                        clone $arrayValue : $arrayValue;
                }
                $this->$key = $newArray;
            }
        }
    }
}