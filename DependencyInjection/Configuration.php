<?php

namespace Us\Bundle\SecurityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('us_security');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('document_validation_handler')
                    ->defaultNull()
                    ->end()
                ->scalarNode('ui_auth_fail_redirect_route')
                    ->defaultNull()
                    ->end()
                ->scalarNode('request_handler')
                    ->defaultNull()
                    ->end();
//                // authentication.partners_token
//                ->arrayNode('authentication')
////                    ->prototype('array')
//                    ->children()
//                        ->booleanNode('verify_partner_token')
//                            ->end()
//                        ->variableNode('partners_token')
//                            ->end();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
