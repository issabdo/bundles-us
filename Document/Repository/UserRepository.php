<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 16/05/16
 * Time: 21:32
 */

namespace Us\Bundle\SecurityBundle\Document\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Us\Bundle\SecurityBundle\Document\User as SecurityUser;
use Us\Bundle\SecurityBundle\Document\Embedded\Address;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Us\Bundle\SecurityBundle\Document\Traits\UserBaseInformations;
use Symfony\Component\Validator\Constraints as Assert;
use Us\Bundle\SecurityBundle\Document\Embedded\AdminTimeline;
use Us\Bundle\SecurityBundle\Document\Embedded\AdminTimelineAction;

class UserRepository extends DocumentRepository
{
}