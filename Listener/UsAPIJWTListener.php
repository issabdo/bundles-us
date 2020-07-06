<?php

namespace Us\Bundle\SecurityBundle\Listener;

use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 *
 */
class UsAPIJWTListener implements ListenerInterface
{
    /**
     * @var SecurityContextInterface|TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $tokenExtractors;

    protected $token;

    protected $container;

    /**
     * @param SecurityContextInterface|TokenStorageInterface $tokenStorage
     * @param AuthenticationManagerInterface $authenticationManager
     * @param array $config
     */
    public function __construct($tokenStorage, AuthenticationManagerInterface $authenticationManager, array $config = [])
    {
        if (!$tokenStorage instanceof TokenStorageInterface && !$tokenStorage instanceof SecurityContextInterface) {
            throw new \InvalidArgumentException('Argument 1 should be an instance of Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface or Symfony\Component\Security\Core\SecurityContextInterface');
        }

        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->config = array_merge(['throw_exceptions' => false], $config);
        $this->tokenExtractors = [];
    }

    /**
     * {@inheritdoc}
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        try {

            $this->token = new JWTUserToken();

            if (!$requestToken = $this->getRequestToken($request)) {
                throw new AuthenticationException();
            }

            $this->token->setRawToken($requestToken);

            $authToken = $this->authenticationManager->authenticate($this->token);
            $this->tokenStorage->setToken($authToken);

            return;

        } catch (AuthenticationException $failed) {

            if ($this->config['throw_exceptions']) {
                throw $failed;
            }

            $data = [
                'code' => 401,
                'message' => $failed->getMessage(),
            ];

            $response = new JsonResponse($data, $data['code']);
            $response->headers->set('WWW-Authenticate', 'Bearer');

            $authToken = new JWTUserToken([]);
            $authToken->setRawToken($this->token->getCredentials());
            $this->container->get('security.token_storage')->setToken($authToken);

//            $jwtInvalidEvent = new JWTInvalidEvent($request, $failed, $response);
            $jwtInvalidEvent = new JWTInvalidEvent($failed, $response);
            $this->dispatcher->dispatch(Events::JWT_INVALID, $jwtInvalidEvent);

            $event->setResponse($jwtInvalidEvent->getResponse());
        }
    }

    /**
     * @param TokenExtractorInterface $extractor
     */
    public function addTokenExtractor(TokenExtractorInterface $extractor)
    {
        $this->tokenExtractors[] = $extractor;
    }

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Request $request
     *
     * @return boolean|string
     */
    protected function getRequestToken(Request $request)
    {
        /** @var TokenExtractorInterface $tokenExtractor */
        foreach ($this->tokenExtractors as $tokenExtractor) {
            if (($token = $tokenExtractor->extract($request))) {
                return $token;
            }
        }

        return false;
    }
}
