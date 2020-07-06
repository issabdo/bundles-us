<?php

namespace Us\Bundle\SecurityBundle\Document\Embedded;

use Us\Bundle\SecurityBundle\Business\AccessSecurity\Authorizer;
use Doctrine\Common\Util\Inflector;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Us\Bundle\SecurityBundle\Document\Traits\DynamicConstantsTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * @ODM\EmbeddedDocument
 * @Assert\Callback("validateType")
 */
class ACLStrategy
{
    use DynamicConstantsTrait;

    /**
     * @ODM\Field(type="string")
     */
    protected $type;

    /**
     * @ODM\Field(type="raw")
     */
    protected $value;



    /*
        GETTERS / SETTERS
    */

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }


    public function validateType(ExecutionContextInterface $context)
    {
        if ($this->getType()) {
            if (false === Authorizer::dynamicConstantExist('STRATEGY_', $this->getType())) {
                $context->addViolation('Unexisting authorizer\'s strategy given');
            }
        }
    }
}