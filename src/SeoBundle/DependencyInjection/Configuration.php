<?php

namespace SeoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('seo');

        $rootNode->append($this->createIndexProviderConfigurationNode());
        $rootNode->append($this->createMetaDataConfigurationNode());
        $rootNode->append($this->createPersistenceNode());

        return $treeBuilder;
    }

    private function createIndexProviderConfigurationNode()
    {
        $treeBuilder = new TreeBuilder('index_provider_configuration');
        $node = $treeBuilder->root('index_provider_configuration');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('pimcore_element_watcher')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('enabled_worker')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('worker_name')->cannotBeEmpty()->isRequired()->end()
                            ->variableNode('worker_config')->defaultValue([])->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    private function createMetaDataConfigurationNode()
    {
        $treeBuilder = new TreeBuilder('meta_data_configuration');
        $node = $treeBuilder->root('meta_data_configuration');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('meta_data_provider')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('auto_detect_documents')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('meta_data_integrator')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('enabled_integrator')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('documents')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->booleanNode('hide_pimcore_default_seo_panel')->defaultFalse()->end()
                            ->end()
                        ->end()
                        ->arrayNode('objects')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->arrayNode('data_classes')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    private function createPersistenceNode()
    {
        $treeBuilder = new TreeBuilder('persistence');
        $node = $treeBuilder->root('persistence');

        $node
            ->addDefaultsIfNotSet()
            ->performNoDeepMerging()
            ->children()
                ->arrayNode('doctrine')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('entity_manager')
                            ->info('Name of the entity manager that you wish to use for managing form builder entities.')
                            ->cannotBeEmpty()
                            ->defaultValue('default')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
