<?php

namespace Us\Bundle\SecurityBundle\Response;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JsonNotFoundResponse extends ApiJsonResponse
{
    const REASON = 'Resource not existing';

    public function __construct($headers = [])
    {
        parent::__construct(self::REASON, self::HTTP_NOT_FOUND, $headers);
    }
}