<?php

namespace Us\Bundle\SecurityBundle\Document\Embedded;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

//use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @ODM\EmbeddedDocument
 * @codeCoverageIgnore
 */
class AdminTimelineAction extends UserTimelineAction
{
    const ROLE_ADDING = 'ROLE_ADDING';
    const ROLE_REMOVAL = 'ROLE_REMOVAL';

    const PRODUCT_DELETE = 'PRODUCT_DELETE';
    const ADMIN_USER_DELETE = 'ADMIN_USER_DELETE';
    const CUSTOMER_USER_DEACTIVATION = 'CUSTOMER_USER_DEACTIVATION';


    protected $types = [

        self::ACCOUNT_CREATE => 'Création du compte',
        self::ACCOUNT_LOG_IN => 'Connexion',
        self::ACCOUNT_UPDATE => 'Mise à jour d\'informations',
        self::ACCOUNT_ADDRESS_UPDATE => 'Mise à jour d\'adresse',
        self::ROLE_ADDING => 'Nouveau rôle',
        self::ROLE_REMOVAL => 'Rôle retiré',
        self::PRODUCT_DELETE => 'Suppression d\'un produit',
        self::ADMIN_USER_DELETE => 'Suppression d\'un admin',
        self::CUSTOMER_USER_DEACTIVATION => 'Désactivation d\'un client'
    ];

    // ---------------------------------

    public function toJSON()
    {
        // @todo...
    }

    public function toVerbose()
    {
        // @todo...
    }
} 