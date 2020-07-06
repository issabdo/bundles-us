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
 * @Assert\Callback("validateRole")
 * @Assert\Callback("validateIp")
 */
class UserACL
{
    use DynamicConstantsTrait;

    /**
     * @ODM\Field(type="string")
     * @Assert\NotBlank()
     */
    protected $role;

    /**
     * @ODM\Field(type="collection")
     */
    protected $ips = [];

    /**
     * @ODM\Field(type="collection")
     */
    protected $tags = [];


    /*
        GETTERS / SETTERS
    */

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * @return mixed
     */
    public function getIps()
    {
        return $this->ips;
    }

    /**
     * @param mixed $ip
     */
    public function setIps($ips)
    {
        $this->ips = $ips;
        return $this;
    }

    /**=
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param mixed $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }


    public function validateIp(ExecutionContextInterface $context)
    {
        if ($this->getIp() && inet_pton($this->getIp()) === false) {
            $context->addViolation('Invalid IPV4 or IPV6 ip given');
        }
    }

    public function validateRole(ExecutionContextInterface $context)
    {
        if ($this->getRole()) {
            if (false === Authorizer::dynamicConstantExist('ROLE_', $this->getRole())) {
                $context->addViolation('Unexisting user authorizer\'s role given');
            }
        }
    }
}