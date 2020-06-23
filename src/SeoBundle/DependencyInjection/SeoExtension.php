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

        $enabledWorkerNames = [];
        foreach ($config['index_provider_configuration']['enabled_worker'] as $enabledWorker) {
            $enabledWorkerNames[] = $enabledWorker['worker_name'];
            $container->setParameter(sprintf('seo.index.worker.config.%s', $enabledWorker['worker_name']), $enabledWorker['worker_config']);
        }

        $container->setParameter('seo.persistence.doctrine.enabled', true);
        $container->setParameter('seo.persistence.doctrine.manager', $entityManagerName);
        $container->setParameter('seo.index.worker.enabled', $enabledWorkerNames);
        $container->setParameter('seo.meta_data_provider.configuration', $config['meta_data_configuration']['meta_data_provider']);
        $container->setParameter('seo.meta_data_integrator.configuration', $config['meta_data_configuration']['meta_data_integrator']);
        $container->setParameter('seo.index.pimcore_element_watcher.enabled', $config['index_provider_configuration']['pimcore_element_watcher']['enabled']);

        $this->checkThirdPartyExtractors($container, $loader, $config['meta_data_configuration']['meta_data_provider']['third_party']);
    }

    /**
     * @param ContainerBuilder $container
     * @param YamlFileLoader   $loader
     * @param array            $thirdPartyOptions
     *
     * @throws \Exception
     */
    protected function checkThirdPartyExtractors(ContainerBuilder $container, YamlFileLoader $loader, array $thirdPartyOptions)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (array_key_exists('CoreShopSEOBundle', $bundles) && $thirdPartyOptions['coreshop']['disable_default_extractors'] === false) {
            $loader->load('services/extractors/coreshop.yml');
        }

        if (array_key_exists('NewsBundle', $bundles) && $thirdPartyOptions['news']['disable_default_extractors'] === false) {
            $loader->load('services/extractors/news.yml');
        }
    }
}
