<?php

namespace Us\Bundle\SecurityBundle\Document\Embedded;

use Doctrine\Common\Util\Inflector;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ODM\EmbeddedDocument
 */
class Address
{
    /**
     * @ODM\Id
     */
    protected $id;

    /**
     * @ODM\Field(type="boolean")
     */
    protected $isBillingOne;

    /**
     * @ODM\Field(type="string")
     */
    protected $label;

    /**
     * @ODM\Field(type="string")
     */
    protected $firstName;

    /**
     * @ODM\Field(type="string")
     */
    protected $lastName;

    /**
     * @ODM\Field(type="string")
     */
    protected $address1;

    /**
     * @ODM\Field(type="string")
     */
    protected $address2;

    /**
     * @ODM\Field(type="string")
     */
    protected $postalCode;

    /**
     * @ODM\Field(type="string")
     */
    protected $city;

    /**
     * @ODM\Field(type="string")
     */
    protected $country;

    /**
     * @return mixed
     */
    public function getIsBillingOne()
    {
        return $this->isBillingOne;
    }

    /**
     * @param mixed $isBillingOne
     */
    public function setIsBillingOne($isBillingOne)
    {
        $this->isBillingOne = $isBillingOne;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param mixed $address1
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param mixed $address2
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param mixed $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }
}