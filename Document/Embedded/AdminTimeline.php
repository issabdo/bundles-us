<?php

namespace Us\Bundle\SecurityBundle\Document\Embedded;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
 * @codeCoverageIgnore
 */
class AdminTimeline extends UserTimeline
{
    /**
     * @ODM\EmbedMany(targetDocument="AdminTimelineAction")
     * @var ArrayCollection Actions
     */
    protected $actions;

    /**
     * {@inheritdoc}
     */
    public function addAction(UserTimelineAction $action = null)
    {
        $this->actions->add($action);
        return $this;
    }

//
//    /**
//     * @return CustomerTimelineAction|null
//     */
//    public function getLastAction()
//    {
//        $nActions = count($this->actions);
//
//        if ($nActions > 0) {
//            $index = $nActions - 1;
//            return $this->actions[$index];
//        }
//
//        return null;
//    }
//
//    /**
//     * @return CustomerTimelineAction|null
//     */
//    public function getLastUpdateAction()
//    {
//        foreach($this->actions as $index => $action)
//        {
//            /** @var CustomerTimelineAction $action */
//
//            if ($action->getType() === CustomerTimelineAction::QUOTE_ACTION_UPDATE) {
//                return $action;
//            }
//        }
//
//        return null;
//    }

} 