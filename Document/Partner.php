<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 16/05/16
 * Time: 21:32
 */

namespace Us\Bundle\SecurityBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Us\Bundle\SecurityBundle\Document\Embedded\Address;
use Us\Bundle\SecurityBundle\Document\User as SecurityUser;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Us\Bundle\SecurityBundle\Document\Traits\UserBaseInformations;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\Document(collection="users")
 */
class Partner extends SecurityUser
{
    protected $type = 'PARTNER';

    /**
     * @ODM\Field(type="string")
     */
    protected $name;

    /**
     * @ODM\Field(type="string")
     */
    protected $token;

    /**
     * @ODM\Field(type="string")
     */
    protected $email;

    /**
     * @ODM\Field(type="string")
     */
    protected $phone;


    /**
     * @ODM\EmbedMany(targetDocument="\Us\Bundle\SecurityBundle\Document\Embedded\Address")
     */
    protected $addresses;

    /**
     * @ODM\EmbedOne(targetDocument="\Us\Bundle\SecurityBundle\Document\Embedded\CustomerTimeline")
     */
    protected $timeline;

    public function __construct()
    {
        if (!$this->addresses) {
            $this->addresses = new ArrayCollection();
        }
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param mixed $addresses
     */
    public function setAddresses($addresses)
    {
        $this->addresses = $addresses;
    }

    public function addAddress(Address $address)
    {
        $this->addresses->add($address);
        return $this;
    }


    /**
     * @return mixed
     */
    public function getTimeline()
    {
        return $this->timeline;
    }

    /**
     * @param mixed $timeline
     */
    public function setTimeline($timeline)
    {
        $this->timeline = $timeline;
        return $this;
    }

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
        return $this;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }
}