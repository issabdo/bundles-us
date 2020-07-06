<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 09/05/16
 * Time: 04:58
 */

namespace Us\Bundle\SecurityBundle\Subscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Us\Bundle\SecurityBundle\Business\AccessSecurity\USJWTTokenAuthenticatorUi;
use Us\Bundle\SecurityBundle\Dispatcher\AuthenticationEvent;
use Us\Bundle\SecurityBundle\Events\AuthenticationEvents;

class AuthSubscriberUi extends EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::JWT_INVALID     => ['onUiJWTInvalidOrExpiredOrNotFound', 0],
            Events::JWT_EXPIRED     => ['onUiJWTInvalidOrExpiredOrNotFound', 0],
            Events::JWT_NOT_FOUND   => ['onUiJWTInvalidOrExpiredOrNotFound', 0],
            AuthenticationEvents::AUTHENTICATION_INIT => ['onAuthenticationInit', -255]
        ];
    }

    public function onUiJWTInvalidOrExpiredOrNotFound(AuthenticationFailureEvent $event)
    {
        $exception = $event->getException();
        if ($exception->getCode() === USJWTTokenAuthenticatorUi::UI_MISSING_TOKEN_CODE) {
            $routeId = $this->container->getParameter('us_security.ui_auth_fail_redirect_route');
            $event->stopPropagation();
            return $event->setResponse(
                (new RedirectResponse($this->container->get('router')->generate($routeId)))
            );
        }
    }

    public function onAuthenticationInit(AuthenticationEvent $event)
    {

    }
}