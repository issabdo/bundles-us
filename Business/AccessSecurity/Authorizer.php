<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 06/05/16
 * Time: 22:34
 */

namespace Us\Bundle\SecurityBundle\Business\AccessSecurity;

use _MediaFileBundle\Document\BaseFile;
use Us\Bundle\SecurityBundle\Document\Embedded\ACLStrategy;
use Us\Bundle\SecurityBundle\Document\Embedded\ResourceACL;
use Us\Bundle\SecurityBundle\Document\Embedded\UserACL;
use Documents\Strategy;
use Us\Bundle\SecurityBundle\Dispatcher\AuthorizationEvent;
use Us\Bundle\SecurityBundle\Document\Customer;
use Us\Bundle\SecurityBundle\Document\User;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Route;
use Us\Bundle\SecurityBundle\Document\Traits\DynamicConstantsTrait;

class Authorizer
{
    use DynamicConstantsTrait;

    const STRATEGY_PUBLIC = 'PUBLIC';
    const STRATEGY_ROLE = 'ROLE';
    const STRATEGY_ROLE_TAG = 'ROLE_TAG';
    const STRATEGY_TAG = 'TAG';
    const STRATEGY_USER = 'USER';
    const STRATEGY_IP = 'IP';
    const STRATEGY_PARTNER = 'PARTNER';
    const STRATEGY_SPECIAL_ALLOW = 'SPECIAL_ALLOW';
    const STRATEGY_SPECIAL_FORBID = 'SPECIAL_FORBID';

    const ROLE_CUSTOMER = 'CUSTOMER';
    const ROLE_SYSTEM = 'SYSTEM'; // A accès à toutes les resources
    const ROLE_OWNER = 'OWNER'; // A accès à toutes les resources
    const ROLE_SUPER_ADMIN = 'SUPER_ADMIN'; // Accès à toutes les resources sauf celles de Owner
    const ROLE_ADMIN_CRUD = 'ADMIN_CRUD'; // CREATE READ UPDATE DELETE
    const ROLE_ADMIN_CRU = 'ADMIN_CRU'; // CREATE READ UPDATE
    const ROLE_ADMIN_READ = 'ADMIN_READ';
    const ROLE_PARTNER = 'PARTNER';

    public static $rolesRanking = [
        self::ROLE_OWNER => 1,
        self::ROLE_SUPER_ADMIN => 2,
        self::ROLE_ADMIN_CRUD => 3,
        self::ROLE_ADMIN_CRU => 4,
        self::ROLE_ADMIN_READ => 5,
        self::ROLE_CUSTOMER => 6
    ];

    const _EVENT_NAME_PREFIX = 'ON_STRATEGY_';
    const _EVENT_NAME_ALLOWANCE_SUFIX = '_ALLOWANCE';
    const _EVENT_NAME_FORBIDDING_SUFIX = '_FORBIDDING';
    protected $authorized = false;

    protected $strategy = null;

    protected $eventDispatcher = null;

    /** @var null|User */
    protected $user = null;

    protected $route = null;

    // @todo DEFINE Exceptions with a system of "business timeline logs" ?

    public function __construct(EventDispatcherInterface $dispatcher, User $user = null)
    {
        $this->eventDispatcher = $dispatcher;

        if ($user) {
            $this->user = $user;
        }
    }

    /**
     * @param array $startegyTagsArray
     * @param array $overloadTags
     */
    public static function overloadStrategyTags(&$startegyTagsArray, $overloadTags = [])
    {
        // {centerId}
        foreach ($startegyTagsArray as &$tag) {
            $matches = [];
            if (preg_match('/\{.+\}/', $tag, $matches)) {
                $tagName = str_replace(['{','}'], '', $matches[0]);
                if (empty($overloadTags[$tagName])) {
                    throw new \LogicException('Missing existing & non-empty overload tag');
                }
                $tag = str_replace('{'.$tagName.'}', $overloadTags[$tagName], $tag);
            }
        }
    }

    private function roleAuthorized($checkedRole, $limiterRole)
    {
        if ($this->overtake($checkedRole)) {
            return true;
        }

        if ($checkedRole === $limiterRole) {
            return true;
        }

        if ($checkedRole === self::ROLE_SUPER_ADMIN && !in_array($limiterRole,[self::ROLE_OWNER, self::ROLE_SYSTEM])) {
            return true;
        }

        if (!isset($this->rolesRanking[$checkedRole]) || !isset($this->rolesRanking[$limiterRole])) {
            return false;
        }

        $checkedRoleRank = $this->rolesRanking[$checkedRole];
        $limiterRoleRank = $this->rolesRanking[$limiterRole];

        if ($checkedRole <= $limiterRole) {
            return true;
        }

        return false;
    }

// authorizeRoleCRU

    public static function convertToResourceACL($strategies)
    {
        $resourceACL = new ResourceACL();
        foreach ($strategies as $strategy) {
            $strat = (new ACLStrategy())
                ->setType($strategy['type'])
                ->setValue($strategy['value']);
            $resourceACL->addStrategies($strat);
        }
        return $resourceACL;
    }

    /**
     * @param $resource mixed
     */
    public function authorizeUserResource($resource)
    {
        $hasRoleStrategy = false;

        if ($this->overtake()) {
            return true;
        }

        if ($resource instanceof BaseFile) {

            /** @var ResourceACL $acl */
            if (null == $acl = $resource->getAcl()) {
                throw new \LogicExcepStion('[Authorizer] Missing $acl in given BaseFile instance');
            }
            if (null == $strategies = $acl->getStrategies()) {
                throw new \LogicExcepStion('[Authorizer] Missing $strategies in given ResourceACL instance');
            }
        } else if ($resource instanceof ResourceACL) {
            if (null == $strategies = $resource->getStrategies()) {
                throw new \LogicExcepStion('[Authorizer] Missing $strategies in given ResourceACL instance');
            }
        }

        if (!isset($strategies)) {
            throw new \LogicException('[Authorizer] Wrong type given for $resource to check');
        }

        /** @var UserACL $userAcl */
        $userAcl = $this->user->getAcl();

        /** @var ACLStrategy $aclStrategy */
        foreach ($strategies as $aclStrategy) {

            $type = $aclStrategy->getType();
            $value = $aclStrategy->getValue();

            if ($type === Authorizer::STRATEGY_ROLE) {
                $hasRoleStrategy = true;
                if ($this->authorizeRole($value)) {
                    return true;
                }
                continue;
            }
            if ($type === Authorizer::STRATEGY_TAG) {
                if (is_array($value)) {
                    $value = $value['tags'];
                }
                if ($this->checkTag($userAcl->getTags(),$value)) {
                    return true;
                }
                continue;
            }
            if ($type === Authorizer::STRATEGY_ROLE_TAG) {
                if (is_array($value)) {
                    $value = $value['tags'];
                }
                if ($this->authorizeRole($value) && $this->checkTag($userAcl->getTags(),$value)) {
                    return true;
                }
                continue;
            }
            if ($type === Authorizer::STRATEGY_USER) {
                if (is_string($value)) {
                    if ($value === $this->user->getId()) {
                        return true;
                    }
                    continue;
                } else if (is_array($value)) {
                    if (in_array($this->user->getId(), $value)) {
                        return true;
                    }
                    continue;
                }
            }
            if ($type === Authorizer::STRATEGY_IP) {
                // @todo ...
            }
            if ($type === Authorizer::STRATEGY_PARTNER) {
                // @todo ...
            }
        }

        if (false === $hasRoleStrategy && $this->user->getAcl()->getRole() === self::ROLE_SUPER_ADMIN) {
            return true;
        }

        return false;
    }

    private function overtake()
    {
        $userRole = $this->user->getAcl()->getRole();

        if (in_array($userRole,[self::ROLE_OWNER, self::ROLE_SYSTEM, self::ROLE_SUPER_ADMIN])) {
            return true;
        }

        return false;
    }

    /**
     * @param array $userTags
     * @param mixed $resourceMixedTag (string|array)
     */
    private function checkTag($userTags, $resourceMixedTag)
    {
        if (is_string($resourceMixedTag)) {
            if (in_array($resourceMixedTag, $userTags)) {
                return true;
            }
        } else if (is_array($resourceMixedTag)) {
            if (count(array_intersect($resourceMixedTag, $userTags)) === count($resourceMixedTag)) {
                return true;
            }
        }

        return false;
    }

    private function checkRoleTag($role, $tag, $tags, $throwException = true)
    {
        if ($this->user->getAcl()->getRole() === self::ROLE_PARTNER) {
            if (!in_array($tag, $tags)) {
                if ($throwException === true) {
                    throw new AccessDeniedHttpException();
                }

                return false;
            }
        }

        return true;
    }

    public function authorizeRolePartner()
    {
        if ($this->user->getAcl()->getRole() === self::ROLE_PARTNER) {
            return true;
        }

        return $this->authorizeRole(self::ROLE_PARTNER);
    }

    /**
     * @param $tag
     * @param bool $throwException
     * @return bool
     */
    public function authorizeRolePartnerTag($tag, $throwException = true)
    {
        $check = $this->checkRoleTag(self::ROLE_PARTNER, $tag, $this->user->getAcl()->getTags(), $throwException);

        if ($throwException === false && $check === false) {
            return false;
        }

        return $this->authorizeRole(self::ROLE_PARTNER);
    }

    public function authorizeRoleCustomer()
    {
        if ($this->user->getAcl()->getRole() === self::ROLE_CUSTOMER) {
            return true;
        }

        return $this->authorizeRole(self::ROLE_CUSTOMER);
    }

    public function authorizeRoleSuperAdmin()
    {
        if ($this->user->getAcl()->getRole() === self::ROLE_OWNER) {
            return true;
        }

        return $this->authorizeRole(self::ROLE_SUPER_ADMIN);
    }

    public function authorizeRoleAdminCRUD()
    {
        if ($this->user->getAcl()->getRole() === self::ROLE_ADMIN_CRUD) {
            return true;
        }

        return $this->authorizeRole(self::ROLE_ADMIN_CRUD);
    }

    public function authorizeRoleAdminCRU()
    {
        if ($this->user->getAcl()->getRole() === self::ROLE_ADMIN_CRU) {
            return true;
        }

        return $this->authorizeRole(self::ROLE_ADMIN_CRU);
    }

    public function authorizeRoleAdminREAD()
    {
        if ($this->user->getAcl()->getRole() === self::ROLE_ADMIN_READ) {
            return true;
        }

        return $this->authorizeRole(self::ROLE_ADMIN_READ);
    }

    public function authorizeRole($role, $userRole = null)
    {
        if ($role === self::ROLE_SYSTEM && $userRole !== self::ROLE_SYSTEM) {
            return false;
        }

        if ($role === self::ROLE_OWNER && !in_array($userRole, [self::ROLE_SYSTEM, self::ROLE_OWNER])) {
            return false;
        }

        $userRole = $userRole ? $userRole : $this->user->getAcl()->getRole();
        if ($userRole === self::ROLE_SYSTEM || $userRole === self::ROLE_OWNER) {
            return true;
        }

        $this->setRole($role);

        $authorization = $this->check();

        return $authorization;
    }


    public function check()
    {
        /** @var AuthorizationEvent $event */

        // STRATEGY AUTHORIZATION EVENT @todo here is a STORE CORE EVENT entry
        $eventIdentifier = self::_EVENT_NAME_PREFIX . $this->strategy;
        $eventIdentifier .= $this->authorized === true ? self::_EVENT_NAME_ALLOWANCE_SUFIX : self::_EVENT_NAME_FORBIDDING_SUFIX; // EX. ON_STRATEGY_ROLE_ALLOWANCE
        $event = new AuthorizationEvent($this->user, $this->authorized, $this->strategy, $this->route);
        $event = $this->eventDispatcher->dispatch($eventIdentifier, $event);

        // ROUTE AUTHORIZATION EVENT @todo here is a STORE CORE EVENT entry
        $eventIdentifier = 'ON_ROUTE_AUTHORIZATION_' . strtoupper($this->route);
        $event = $this->eventDispatcher->dispatch($eventIdentifier, $event);

        $this->authorized = $event->getAuthorized();

        if ($this->authorized === false) {
            throw new AccessDeniedHttpException();
        }

        return true;
    }


    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    public function setRoute(string $route)
    {
        $this->route = $route;
        return $this;
    }

    public function setRole($role)
    {
        $this->strategy = self::STRATEGY_ROLE;

        $this->authorized = $this->roleAuthorized($this->user->getAcl()->getRole(), $role);
    }

    public function setRoleTag($role, $tag)
    {
        $this->strategy = self::STRATEGY_ROLE_TAG;

        $this->authorized = $this->roleAuthorized($this->user->getAcl()->getRole(), $role);

        if (!in_array($tag, $this->user->getTags())) {
            $this->authorized = false;
        }
    }

    public function setRoleSpecialAllow($value)
    {
        $this->strategy = self::STRATEGY_SPECIAL_ALLOW . '_' . self::STRATEGY_ROLE;

    }

    public function setRoleSpecialForbid($value)
    {
        $this->strategy = self::STRATEGY_SPECIAL_FORBID . '_' . self::STRATEGY_ROLE;

    }

    public function setRoleTagSpecialAllow($value)
    {
        $this->strategy = self::STRATEGY_SPECIAL_ALLOW . '_' . self::STRATEGY_ROLE_TAG;
    }

    public function setRoleTagSpecialForbid($value)
    {
        $this->strategy = self::STRATEGY_SPECIAL_FORBID . '_' . self::STRATEGY_ROLE_TAG;
    }

    public function setUserSpecialAllow($value)
    {
        $this->strategy = self::STRATEGY_SPECIAL_ALLOW . '_' . self::STRATEGY_USER;

        if (is_string($value)) {
            if ($this->user->getId() === $value) {
                $this->authorized = true;
            } else {
                $this->authorized = false;
            }
        }

        if (is_array($value)) {
            if (in_array($this->user->getId(), $value)) {
                $this->authorized = true;
            } else {
                $this->authorized = false;
            }
        }
    }

    public function setUserSpecialForbid($value)
    {
        $this->strategy = self::STRATEGY_SPECIAL_FORBID . '_' . self::STRATEGY_USER;

        if (is_string($value)) {
            if ($this->user->getId() === $value) {
                $this->authorized = false;
            } else {
                $this->authorized = true;
            }
        }

        if (is_array($value)) {
            if (in_array($this->user->getId(), $value)) {
                $this->authorized = false;
            } else {
                $this->authorized = true;
            }
        }
    }

    /*
        DEV API
    */


    /* OWNER */

    public function setRoleTagOWNER($tag)
    {
        $this->setRoleTag(self::ROLE_OWNER, $tag);
        return $this;
    }

    public function setRoleOWNER()
    {

    }

    /* SUPER ADMIN */

    public function setRoleTagSUPER_ADMIN($tag)
    {
        $this->setRoleTag(self::ROLE_SUPER_ADMIN, $tag);
        return $this;
    }

    public function setRoleSUPER_ADMIN()
    {
        $this->setRole(self::ROLE_SUPER_ADMIN);
        return $this;
    }

    /* ADMIN CRUD */

    public function setRoleTagADMIN_CRUD($tag)
    {
        $this->setRoleTag(self::ROLE_ADMIN_CRUD, $tag);
        return $this;
    }

    public function setRoleADMIN_CRUD()
    {

    }

    /* ADMIN CRU */

    public function setRoleTagADMIN_CRU($tag)
    {
        $this->setRoleTag(self::ROLE_ADMIN_CRU, $tag);
        return $this;
    }

    public function setRoleADMIN_CRU()
    {

    }

    /* ADMIN READ */

    public function setRoleTagADMIN_READ($tag)
    {
        $this->setRoleTag(self::ROLE_ADMIN_READ, $tag);
        return $this;
    }

    public function setRoleADMIN_READ()
    {

    }


}