<?php

namespace Us\Bundle\SecurityBundle\Dispatcher;

use Us\Bundle\SecurityBundle\Document\User;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Routing\Route;

/**
 * Created by PhpStorm.
 * User: florian
 * Date: 09/05/16
 * Time: 02:11
 */
class AuthorizationEvent extends Event
{
    protected $user = null;
    protected $authorized = null;
    protected $strategy = null;
    protected $route = null;

    public function __construct(User $user, bool $authorized, string $strategy, string $route)
    {
        $this->user = $user;
        $this->authorized = $authorized;
        $this->strategy = $strategy;
        $this->route = $route;
        // @todo use route to dispatch annother event just for IT
    }

    /**
     * @return null|Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param null|Route $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * @return null|User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param null|User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return bool|null
     */
    public function getAuthorized()
    {
        return $this->authorized;
    }

    /**
     * @param bool|null $authorized
     */
    public function setAuthorized($authorized)
    {
        $this->authorized = $authorized;
    }

    /**
     * @return null|string
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * @param null|string $strategy
     */
    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;
    }

}