<?php

namespace Us\Bundle\SecurityBundle\Command;

use Us\Bundle\SecurityBundle\Business\AccessSecurity\Authorizer;
use Us\Bundle\SecurityBundle\Document\Admin;
use Us\Bundle\SecurityBundle\Document\Embedded\UserACL;
use Us\Bundle\SecurityBundle\Document\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class UserCreateAdminCommand extends ContainerAwareCommand
{
    protected $adminRoles = [
        Authorizer::ROLE_SUPER_ADMIN,
        Authorizer::ROLE_ADMIN_CRUD,
        Authorizer::ROLE_ADMIN_CRU,
        Authorizer::ROLE_ADMIN_READ
    ];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('user:create:admin')
            ->setDescription('Create a nex business user')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('role', InputArgument::REQUIRED, 'Role among ' . implode('|', $this->adminRoles))
            ->addArgument('firstname', InputArgument::REQUIRED, 'Firstname')
            ->addArgument('lastname', InputArgument::REQUIRED, 'Lastname')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email')
            ->addArgument('tags', InputArgument::OPTIONAL, 'Tags as a string like tag1+tag2+tag3 ...')
            ->addArgument('ips', InputArgument::OPTIONAL, 'User ip(s) like ip1+ip2...');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dm = $this->getContainer()->get('doctrine_mongodb')->getManager();

        $password = $input->getArgument('password');
        if (!$password) {
            $password = (string)(new \DateTime())->getTimestamp();
        }
        $role = $input->getArgument('role');
        $username = $input->getArgument('username');
        $firstname = $input->getArgument('firstname');
        $lastname = $input->getArgument('lastname');
        $email = $input->getArgument('email');
        $tags = $input->getArgument('tags');
        if ($tags) {
            $tagList = explode('+', $tags);
        }
        $ips = $input->getArgument('ips');
        if ($ips) {
            $ipList = explode('+', $ips);
        }

        if (!in_array($role, $this->adminRoles)) {
            throw new \Exception('Unknown role given.');
        }

        $user = new User();
        $user->setType('ADMIN');
        $user->setUsername($username);
        $user->setFirstName($firstname);
        $user->setLastName($lastname);
        $user->setPassword(password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]));
        $userAcl = new UserACL();
        $userAcl->setRole($role);
        if (isset($tagList)) {
            $userAcl->setTags($tagList);
        }
        if (isset($ipList)) {
            $userAcl->setIps($ipList);
        }
        $user->setAcl($userAcl);
//        if (isset($tagList)) {
//            foreach ($tagList as $tag) {
//                $user->addTag($tag);
//            }
//        }

        $dm->persist($user);
        $dm->flush();

        echo sprintf('New admin created with credentials : %s / %s', $username, $password);
    }
}