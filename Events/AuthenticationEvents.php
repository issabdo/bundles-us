<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 09/05/16
 * Time: 05:05
 */

namespace Us\Bundle\SecurityBundle\Events;


interface AuthenticationEvents
{
    const AUTHENTICATION_INIT = 'AUTHENTICATION_INIT';
    const UI_JWT_NOT_FOUND = 'UI_JWT_NOT_FOUND';
}