<?php

namespace Us\Bundle\SecurityBundle\Dispatcher;

use Store\BaseBundle\Document\User;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * Created by PhpStorm.
 * User: florian
 * Date: 09/05/16
 * Time: 02:11
 */
class AuthenticationEvent extends Event
{
    /** @var  Request */
    protected $request;

    protected $user = null;


    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param null $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}