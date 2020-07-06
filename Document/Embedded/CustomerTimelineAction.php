<?php

namespace Us\Bundle\SecurityBundle\Document\Embedded;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

//use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @ODM\EmbeddedDocument
 */
class CustomerTimelineAction extends UserTimelineAction
{
    const CART_PRODUCT_ADD = 'CART_PRODUCT_ADD';
    const CART_PRODUCT_REMOVE = 'CART_PRODUCT_REMOVE';

    const ORDER_PROCESS_STEP_1 = 'ORDER_PROCESS_STEP_1';
    const ORDER_PROCESS_STEP_2 = 'ORDER_PROCESS_STEP_2';
    const ORDER_PROCESS_STEP_3 = 'ORDER_PROCESS_STEP_3';
    const ORDER_PROCESS_STEP_4 = 'ORDER_PROCESS_STEP_4';
    const ORDER_PROCESS_PAYMENT_SUCCESS = 'ORDER_PROCESS_PAYMENT_SUCCESS';
    const ORDER_PROCESS_PAYMENT_FAIL = 'ORDER_PROCESS_PAYMENT_FAIL';


    protected $types = [

        self::ACCOUNT_CREATE => 'Création du compte',
        self::ACCOUNT_LOG_IN => 'Connexion',
        self::ACCOUNT_UPDATE => 'Mise à jour d\'informations',
        self::ACCOUNT_ADDRESS_UPDATE => 'Mise à jour d\'adresse',
        self::CART_PRODUCT_ADD => 'Ajout d\'un produit au panier',
        self::CART_PRODUCT_REMOVE => 'Enlèvement d\'un produit du panier',
        self::ORDER_PROCESS_STEP_1 => 'Atteinte de l\'étape 1 du tunnel de paiement',
        self::ORDER_PROCESS_STEP_2 => 'Atteinte de l\'étape 2 du tunnel de paiement',
        self::ORDER_PROCESS_STEP_3 => 'Atteinte de l\'étape 3 du tunnel de paiement',
        self::ORDER_PROCESS_STEP_4 => 'Atteinte de l\'étape 4 du tunnel de paiement',
        self::ORDER_PROCESS_PAYMENT_SUCCESS => 'Paiement de commande validé',
        self::ORDER_PROCESS_PAYMENT_FAIL => 'Paiement de commandé en échec'
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