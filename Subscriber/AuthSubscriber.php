<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 09/05/16
 * Time: 04:58
 */

namespace Us\Bundle\SecurityBundle\Subscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use Us\Bundle\SecurityBundle\Document\UserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\AuthenticationEvents as CoreAuthenticationEvents;
//use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Us\Bundle\SecurityBundle\Dispatcher\AuthenticationEvent;
use Us\Bundle\SecurityBundle\Dispatcher\AuthorizationEvent;
use Us\Bundle\SecurityBundle\Events\AuthenticationEvents;
use Us\Bundle\SecurityBundle\Events\AuthorizationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\Cookie;

class AuthSubscriber extends EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CoreAuthenticationEvents::AUTHENTICATION_FAILURE => ['onAuthenticationFailure', 100],
            Events::JWT_AUTHENTICATED => ['onJwtValid', 100],
            Events::JWT_INVALID => ['onJwtInvalidOrExpiredOrNotFound', 100],
            Events::JWT_EXPIRED => ['onJwtInvalidOrExpiredOrNotFound', 100],
            Events::JWT_NOT_FOUND => ['onJwtInvalidOrExpiredOrNotFound', 100],
            Events::JWT_CREATED => ['onJwtCreated', 100],
            AuthenticationEvents::AUTHENTICATION_INIT => ['onAuthenticationInit', 192],
            AuthorizationEvents::STRATEGY_ROLE_TAG_ALLOWANCE => ['onStrategyRoleTagAllowance', 100],
        ];

    }

    public function onJwtCreated($event)
    {
        //
    }

    /**
     * @param AuthenticationEvent $event
     */
    public function onJwtValid($event)
    {
        //
    }

    /**
     * @param JWTInvalidEvent $event
     */
    public function onJwtInvalidOrExpiredOrNotFound($event)
    {
        /** @var RequestStack $request */
        $request = $this->request;
        /** @var Request $request */
        $request = $request->getCurrentRequest();

        $username = $request->headers->get('username');
        $password = $request->headers->get('password');

        if ($username && $password) {

            /** @var UserProvider $userProvider */
            $userProvider = $this->container->get('api_user_provider');
            $user = $userProvider->loadUserByUsernamePassword($username, $password);

//            $token = $this->container->get('security.token_storage')->getToken();
//            $token->setUser($user);

            /** @var JWTManagerInterface $manager */
            $manager = $this->container->get('lexik_jwt_authentication.jwt_manager');

            // create new token for user
            $token = $manager->create($user);
            $JWTUserToken = new JWTUserToken([], $user, $token);
            $this->getContainer()->get('security.token_storage')->setToken($JWTUserToken);

            $response = $this->kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
            $response->headers->set('BEARER', $token);
//             @TODO ... NO MORE COOKIE NEEDED
//            $jwtCookie = new Cookie('BEARER', $token, (new \DateTime())->add(new \DateInterval('P6M')));
//            $response->headers->setCookie($jwtCookie);

            $event->setResponse($response);
        }
    }

    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailure($event)
    {
        if ($event instanceof AuthenticationFailureEvent) {
        }
    }

    public function onAuthenticationInit(AuthenticationEvent $event)
    {

//        $session = $event->getRequest()->getSession();
//        $headers = $event->getRequest()->headers;
//
//        $toto = 888;
    }

    public function onStrategyRoleTagAllowance(AuthorizationEvent $event)
    {
        $toto = 888;
    }
}