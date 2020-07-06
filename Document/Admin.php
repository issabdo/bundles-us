<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 16/05/16
 * Time: 21:32
 */

namespace Us\Bundle\SecurityBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Us\Bundle\SecurityBundle\Document\User as SecurityUser;
use Us\Bundle\SecurityBundle\Document\Embedded\Address;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Us\Bundle\SecurityBundle\Document\Traits\UserBaseInformations;
use Symfony\Component\Validator\Constraints as Assert;
use Us\Bundle\SecurityBundle\Document\Embedded\AdminTimeline;
use Us\Bundle\SecurityBundle\Document\Embedded\AdminTimelineAction;

/**
 * @ODM\Document(collection="users")
 */
class Admin extends SecurityUser
{
    use UserBaseInformations;

    protected $type = 'ADMIN';

    /**
     * @ODM\Field(type="string")
     */
    protected $email;

    /**
     * @ODM\EmbedOne(targetDocument="\Us\Bundle\SecurityBundle\Document\Embedded\Address")
     */
    protected $address;

    /**
     * @ODM\EmbedOne(targetDocument="\Us\Bundle\SecurityBundle\Document\Embedded\CustomerTimeline")
     */
    protected $timeline;

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
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
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
    }
}