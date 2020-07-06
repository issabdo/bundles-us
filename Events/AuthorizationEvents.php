<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 09/05/16
 * Time: 05:05
 */

namespace Us\Bundle\SecurityBundle\Events;


interface AuthorizationEvents
{
    const STRATEGY_ROLE_ALLOWANCE = 'ON_STRATEGY_ROLE_ALLOWANCE';
    const STRATEGY_ROLE_FORBIDDING = 'ON_STRATEGY_ROLE_FORBIDDING';
    const STRATEGY_ROLE_TAG_ALLOWANCE = 'ON_STRATEGY_ROLE_TAG_ALLOWANCE';
    const STRATEGY_ROLE_TAG_FORBIDDING = 'ON_STRATEGY_ROLE_TAG_FORBIDDING';
}