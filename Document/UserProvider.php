<?php

namespace Us\Bundle\SecurityBundle\Document;

use Doctrine\Common\Persistence\ManagerRegistry;
use Us\Bundle\SecurityBundle\Document\Admin;
use Us\Bundle\SecurityBundle\Document\Customer;
use Us\Bundle\SecurityBundle\Document\Partner;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserProvider;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UserProvider implements UserProviderInterface
{
    /** @var ManagerRegistry */
    protected $documentRegistry;

    public function __construct(ManagerRegistry $doctrineMongoDbRegistry)
    {
        $this->documentRegistry = $doctrineMongoDbRegistry;
    }

    public function loadUserByUsernamePassword($username, $password)
    {
        $user = $this->retrieveUserBy($username);

        if ($user && password_verify($password, $user->getPassword())) {
            return $user;
        }

        return null;
    }

    public function loadUserByUsername($username)
    {
        $user = $this->retrieveUserBy($username);

        if (!$user) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $username)
            );
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return in_array(
            $class,
            [
                // @todo Add child type users if created later (extends) ? ex. customer, admin, partner
                'Store\BaseBundle\Document\Security\User',
                'Store\BaseBundle\Document\Security\Partner',
                'Store\BaseBundle\Document\Security\Customer',
                'Store\BaseBundle\Document\Security\Admin'
            ]
        );
    }

    /**
     * @param $username
     * @param array $by
     * @return User
     */
    protected function retrieveUserBy($username, $is = null, Array $by = [])
    {
        $by = array_merge(['username' => $username], $by);
        /** @var User $user */
        $user = $this->documentRegistry->getRepository(get_class(new User()))->findOneBy($by);
        if ($user) {
            $className = User::class;
            $type = $user->getType();
            switch($type) {
                case 'PARTNER':
                    $className = Partner::class;
                    continue;
                case 'ADMIN':
                    $className = Admin::class;
                    continue;
                case 'CUSTOMER':
                    $className = Customer::class;
                    continue;
            }
            $user = $this->documentRegistry->getRepository($className)->findOneBy($by);
        }

        return $user;
    }
}
