<?php

namespace Us\Bundle\SecurityBundle\Business\AccessSecurity;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\MissingTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Us\Bundle\SecurityBundle\Dispatcher\UiJWTNotFound;


/**
 * USJWTTokenAuthenticatorUi (Guard implementation).
 */
class USJWTTokenAuthenticatorUi extends USJWTTokenAuthenticatorAbstract
{
    const UI_MISSING_TOKEN_CODE = 99;

    /**
     * {@inheritdoc}
     *
     * @return JWTAuthenticationFailureResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $exception = new MissingTokenException('JWT Token not found', self::UI_MISSING_TOKEN_CODE, $authException);
        $event = new JWTNotFoundEvent($exception, new JWTAuthenticationFailureResponse($exception->getMessageKey()));

        $this->dispatcher->dispatch(Events::JWT_NOT_FOUND, $event);

        return $event->getResponse();
    }
}
