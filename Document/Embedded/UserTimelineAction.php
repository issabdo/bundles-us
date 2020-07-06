<?php

namespace Us\Bundle\SecurityBundle\Document\Embedded;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

//use Symfony\Component\Validator\ExecutionContextInterface;


abstract class UserTimelineAction
{
    const ACCOUNT_CREATE = 'ACCOUNT_CREATED';
    const ACCOUNT_LOG_IN = 'ACCOUNT_LOG_IN';
    const ACCOUNT_UPDATE = 'ACCOUNT_UPDATED';
    const ACCOUNT_ADDRESS_UPDATE = 'ACCOUNT_ADDRESS_UPDATE';

    /**
     * @param $type
     * @param $user
     */
//    public function __construct($type, $user)
    public function __construct()
    {

        // @todo REFACTO Following (from copy)...
//
//        if ($user instanceof SubscriberInterface) {
//
//            /** @var Subscriber $user */
//            $userId = $user->getEmail();
//            $userType = self::USER_TYPE_PROSPECT;
//            $firstName = $user->getFirstName();
//            $lastName = $user->getLastName();
//
//
//        } else if(is_a($user, '\Finaxy\BaseControlCenterBundle\Document\FccUser')) {
//
//            /** @var FccUser $user */
//            $userId = $user->getUsername();
//            $userType =  $user->getType();
//            $firstName = $user->getFirstName();
//            $lastName = $user->getLastName();
//
//        } else {
//            return;
//        }
//
//        $this->setType($type);
//        $this->setDate(new \DateTime());
//        $this->setUserId($userId);
//        $this->setFccUserType($userType);
//        $this->setFirstName($firstName);
//        $this->setLastName($lastName);
    }


    /**
     * @ODM\Field(type="string")
     * @Assert\NotNull(message="Type manquant")
     */
    protected $type;

    /**
     * @ODM\Date
     * @Assert\NotNull(message="Date manquante")
     */
    protected $date;

    /**
     * @ODM\Field(type="string")
     * @Assert\NotNull(message="Username manquant")
     */
    protected $username;

    /**
     * @ODM\Field(type="string")
     * @Assert\NotNull(message="Type utilisateur manquant")
     */
    protected $userType;

    /**
     * @ODM\Field(type="string")
     */
    protected $details;

    // ---------------------------------


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * @param mixed $userType
     */
    public function setUserType($userType)
    {
        $this->userType = $userType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param mixed $details
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        if (array_key_exists($type, $this->types)) {

            $this->type = $type;
        }

        return $this;
    }

    // --------------------------------

    public function typeValidator(ExecutionContextInterface $context)
    {
        if (!array_key_exists($this->type, $this->types)) {
            $context->addViolationAt('type', 'Type d\'action invalide');
        }
    }

    // ---------------------------------

    public function toJSON()
    {
        // @todo...
    }

    public function toVerbose()
    {
        // @todo...
    }
} 