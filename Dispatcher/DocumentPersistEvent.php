<?php

namespace Us\Bundle\SecurityBundle\Dispatcher;

use Doctrine\Common\Persistence\ManagerRegistry;
use Us\Bundle\SecurityBundle\Document\User;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Routing\Route;

/**
 * Created by PhpStorm.
 * User: florian
 * Date: 09/05/16
 * Time: 02:11
 */
class DocumentPersistEvent extends Event
{
    protected $document = null;
    protected $documentName;
    protected $isNewDocument = true;

    protected $persisted = null;

    public function __construct($document)
    {
        $this->document = $document;
        $this->documentName = get_class($document);
        if (!isset($document->ref)) {
            $this->isNewDocument = false;
        }
    }

    public function documentEventName($baseEventName)
    {
        return $baseEventName . '_' . strtoupper($this->documentName);
    }


    public function persist(ManagerRegistry $doctrineMongoDbRegistry)
    {
        try {
            $doctrineMongoDbRegistry->getManager()->persist($this->document);
            $doctrineMongoDbRegistry->getManager()->flush();
            $this->persisted = true;
        } catch (\Exception $e) {
            $this->persisted = false;
        }
    }

    /**
     * @return null
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @return mixed
     */
    public function getDocumentName()
    {
        return $this->documentName;
    }

    /**
     * @return boolean
     */
    public function isIsNewDocument()
    {
        return $this->isNewDocument;
    }


}