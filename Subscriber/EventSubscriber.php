<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 16/05/16
 * Time: 13:03
 */

namespace Us\Bundle\SecurityBundle\Subscriber;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Kernel;


abstract class EventSubscriber implements EventSubscriberInterface
{
    /** @var  ContainerInterface */
    protected $container;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ManagerRegistry */
    protected $documentManager;

    /** @var Kernel */
    protected $kernel;

    protected $request;

    public function __construct(ContainerInterface $serviceContainer, EventDispatcherInterface $eventDispatcher, ManagerRegistry $doctrineMongoDbRegistry, Kernel $kernel)
    {
        $this->container = $serviceContainer;
        $this->eventDispatcher = $eventDispatcher;
        $this->documentManager = $doctrineMongoDbRegistry;
        $this->kernel = $kernel;
    }

    public function setRequest(RequestStack $request)
    {
        $this->request = $request;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param mixed $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @return ManagerRegistry
     */
    public function getDocumentManager()
    {
        return $this->documentManager;
    }

    /**
     * @param ManagerRegistry $documentManager
     */
    public function setDocumentManager($documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher($eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }


}