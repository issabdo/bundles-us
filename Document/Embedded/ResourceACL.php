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
 */
class ResourceACL
{
    use DynamicConstantsTrait;

    /**
     * @ODM\EmbedMany(targetDocument="ACLStrategy")
     * @Assert\NotNull()
     */
    protected $strategies;


    /*
        GETTERS / SETTERS
    */


    /**
     * @return mixed
     */
    public function getStrategies()
    {
        return $this->strategies;
    }

    /**
     * @param mixed $strategies
     */
    public function setStrategies($strategies)
    {
        $this->strategies = $strategies;
        return $this;
    }

    public function addStrategies($strategy)
    {
        $this->strategies[] = $strategy;
        return $this;
    }
}