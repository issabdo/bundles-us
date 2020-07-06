<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 06/05/16
 * Time: 01:51
 */

namespace Us\Bundle\SecurityBundle\Listener;

use _PayZenBundle\Exceptions\PayZenPaymentError;
use HttpRequestException;
use Monolog\Logger;
use OzmoAPIBundle\Exceptions\Ozmo500Exception;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Us\Bundle\SecurityBundle\Response\ApiJsonResponse;
use Us\Bundle\SecurityBundle\Response\JsonNotFoundResponse;
//use Store\BaseBundle\Exception\DocumentValidationException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class ExceptionListener
{
    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var */
    protected $logger;


    public function __construct(EventDispatcherInterface $eventDispatcher, Logger $logger)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $e = $event->getException();

        if ($e instanceof MissingMandatoryParametersException || $e instanceof \Exception && $e->getCode() === 400) {
            $message = $e->getMessage();
            if (json_decode($message)) {
                $message = json_decode($message, true);
            }
            $event->setResponse(
                new ApiJsonResponse($message, 400)
            );
            return;
        }

        // 401
        if ($e instanceof AuthenticationException || ($e instanceof \Exception && $e->getCode() === 401)) {
            $event->setResponse(
                new ApiJsonResponse($e->getMessage(), 401)
            );
            return;
        }
        if ($e instanceof \Exception && $e->getCode() === 412) {
            $event->setResponse(
                new ApiJsonResponse($e->getMessage(), 412)
            );
            return;
        }

        // 403
        if ($e instanceof AccessDeniedHttpException) {
            $event->setResponse(
                new ApiJsonResponse($e->getMessage(), 403)
            );
            return;
        }

        // 404
        if ($e instanceof NotFoundHttpException) {
            $event->setResponse(
                new ApiJsonResponse($e->getMessage(), 404)
            );
            return;
        }
        // NotFoundHttpException

//        if ($e instanceof DocumentValidationException) {
//            $event->setResponse(
//                new ApiJsonResponse($e->getParametersList(), 400)
//            );
//            return;
//        }

        if ($e instanceof NotFoundHttpException || $e instanceof ResourceNotFoundException) {
            $event->setResponse(
                (new JsonNotFoundResponse())->setData($e->getMessage())
            );
            return;
        }


        if ($e instanceof PayZenPaymentError || $e instanceof \Exception && $e->getCode() === 409) {
            $event->setResponse(
                new ApiJsonResponse($e->getMessage(), 409)
            );
            return;
        }

        if ($e instanceof Ozmo500Exception) {
            $event->setResponse(
                new ApiJsonResponse($e->getMessage(), 500)
            );
            return;
        }

        $this->logger->addError('[KERNEL EXCEPTION]||[' . $e->getFile() . '][L.' . $e->getLine() . '] ' . $e->getMessage());

        $event->setResponse(
//            new ApiJsonResponse('Internal Error', 500)
//            new ApiJsonResponse($e->getMessage(), 500)
            new ApiJsonResponse($e->getMessage(), 500)
//            new ApiJsonResponse($e->get, 500)
        );
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