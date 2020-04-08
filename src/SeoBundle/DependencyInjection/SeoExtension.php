<?php

namespace SeoBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class SeoExtension extends Extension
{
    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config']));
        $loader->load('services.yml');

        $persistenceConfig = $config['persistence']['doctrine'];
        $entityManagerName = $persistenceConfig['entity_manager'];

        $container->setParameter('seo.persistence.doctrine.enabled', true);
        $container->setParameter('seo.persistence.doctrine.manager', $entityManagerName);

        $enabledWorkerNames = [];
        $enabledWorkerConfig = [];
        foreach ($config['index_provider_configuration']['enabled_worker'] as $enabledWorker) {
            $enabledWorkerNames[] = $enabledWorker['worker_name'];
            $enabledWorkerConfig[$enabledWorker['worker_name']] = $enabledWorker['worker_config'];
        }

        $container->setParameter('seo.index.worker.enabled', $enabledWorkerNames);
        $container->setParameter('seo.index.worker.config', $enabledWorkerConfig);
        $container->setParameter('seo.index.pimcore_element_watcher.enabled', $config['index_provider_configuration']['pimcore_element_watcher']['enabled']);

    }
}
