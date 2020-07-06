<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 06/05/16
 * Time: 01:51
 */

namespace Us\Bundle\SecurityBundle\Listener;

//use AppBundle\Controller\TokenAuthenticatedController;
use Us\Bundle\SecurityBundle\Business\AccessSecurity\Authorizer;
use Us\Bundle\SecurityBundle\Business\SecurityController;
use Us\Bundle\SecurityBundle\Dispatcher\AuthenticationEvent;
use Us\Bundle\SecurityBundle\Events\AuthenticationEvents;
use Us\Bundle\SecurityBundle\Document\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RequestListener
{
    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var  ContainerInterface */
    protected $serviceContainer;

    /** @var ManagerRegistry */
    protected $documentManager;

    public function __construct(EventDispatcherInterface $eventDispatcher, ContainerInterface $serviceContainer, ManagerRegistry $doctrineMongoDbRegistry)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->serviceContainer = $serviceContainer;
        $this->documentManager = $doctrineMongoDbRegistry;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        /** @var Request $request */
        $request = $event->getRequest();

        if ($controller[0] instanceof SecurityController) {

            /** SecurityController $controller */
            $controller = $controller[0];

            $token = $this->getServiceContainer()->get('security.token_storage')->getToken();

            if ($token) {
                $currentUser = $token->getUser();
            }
            if (!$token || !$currentUser || $currentUser === 'anon.') {
                $currentUser = new \Us\Bundle\SecurityBundle\Document\User();
            }

            $this->serviceContainer->set('_apiUser', $currentUser);
            $controller->setUser($currentUser);

            $data = [];

            if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
//                if (null == $data = json_decode($request->getContent(), true)) {
//                    throw new \Exception('Content must be a valid and non empty JSON string');
//                }
            }

            $controller->requestData = $data;
            $controller->currentRequest = $request;

            $jsonResponse = new JsonResponse();
            $jsonResponse->setPublic();
            $controller->setJsonResponse($jsonResponse);

            $controller->preAction();
        }
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

    /**
     * @return ContainerInterface
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * @param ContainerInterface $serviceContainer
     */
    public function setServiceContainer($serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
    }
}