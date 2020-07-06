<?php

namespace Us\Bundle\SecurityBundle\Dispatcher;

use Doctrine\Common\Persistence\ManagerRegistry;
use Us\Bundle\SecurityBundle\Document\BaseDocument;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Routing\Route;

/**
 * Created by PhpStorm.
 * User: florian
 * Date: 09/05/16
 * Time: 02:11
 */
class DocumentHydratationEvent extends Event
{
    protected $document = null;
    protected $data = null;
    protected $documentName = null;
    protected $replace;
    protected $isNewDocument = true;

    protected $persisted = null;

    public function __construct($data, BaseDocument $document, $replace = false)
    {
        $this->data = $data;
        $this->document = $document;
        $this->documentName = get_class($document);
        $this->replace = $replace;
    }

    public function documentEventName($baseEventName)
    {
        return $baseEventName . '_' . strtoupper($this->documentName);
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

    public function getReplace()
    {
        return $this->replace;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this->data;
    }
}