<?php

namespace SeoBundle\DependencyInjection;

use SeoBundle\Tool\Bundle;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class SeoExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config']));
        $loader->load('services.yml');

        $this->validateConfiguration($config);

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

    protected function validateConfiguration(array $config): void
    {
        $enabledIntegrators = [];
        foreach ($config['meta_data_configuration']['meta_data_integrator']['enabled_integrator'] as $dataIntegrator) {
            if (in_array($dataIntegrator['integrator_name'], $enabledIntegrators, true)) {
                throw new InvalidConfigurationException(sprintf('Meta data integrator "%s" already has been added', $dataIntegrator['integrator_name']));
            }

            $enabledIntegrators[] = $dataIntegrator['integrator_name'];
        }
    }

    protected function checkThirdPartyExtractors(ContainerBuilder $container, YamlFileLoader $loader, array $thirdPartyOptions): void
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (Bundle::hasBundle('CoreShopSEOBundle', $bundles) === true && $thirdPartyOptions['coreshop']['disable_default_extractors'] === false) {
            $loader->load('services/extractors/coreshop.yml');
        }

        if (Bundle::hasDachcomBundle('NewsBundle', $bundles) === true && $thirdPartyOptions['news']['disable_default_extractors'] === false) {
            $loader->load('services/extractors/news.yml');
        }
    }
}
