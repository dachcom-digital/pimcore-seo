<?php

namespace SeoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class SeoExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator([__DIR__ . '/../../config']));
        $loader->load('services.yaml');

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
    }

    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $enabledThirdPartyConfigs = [];

        $xliffBundleEnabled = $container->hasExtension('pimcore_xliff');
        $pimcoreSeoBundleEnabled = $container->hasExtension('pimcore_seo');
        $newsBundleEnabled = $container->hasExtension('news');
        $coreShopSeoBundleEnabled = $container->hasExtension('core_shop_seo');

        foreach ($configs as $config) {

            $thirdPartyConfig = $config['meta_data_configuration']['meta_data_provider']['third_party'] ?? null;

            if ($thirdPartyConfig === null) {
                continue;
            }

            if ($coreShopSeoBundleEnabled && ($thirdPartyConfig['coreshop']['disable_default_extractors'] ?? false) === false) {
                $enabledThirdPartyConfigs['core_shop_seo'] = 'services/third_party/coreshop.yaml';
            }

            if ($newsBundleEnabled && ($thirdPartyConfig['news']['disable_default_extractors'] ?? false) === false) {
                $enabledThirdPartyConfigs['dachcom_news'] = 'services/third_party/news.yaml';
            }
        }

        if ($xliffBundleEnabled) {
            $enabledThirdPartyConfigs['pimcore_xliff'] = 'services/third_party/pimcore_xliff.yaml';
        }

        if ($pimcoreSeoBundleEnabled) {
            $enabledThirdPartyConfigs['pimcore_seo'] = 'services/third_party/pimcore_seo.yaml';
        }

        foreach ($enabledThirdPartyConfigs as $enabledThirdPartyConfig) {
            $loader->load($enabledThirdPartyConfig);
        }

        $container->setParameter('seo.third_party.enabled', array_keys($enabledThirdPartyConfigs));

    }

    private function validateConfiguration(array $config): void
    {
        $enabledIntegrators = [];
        foreach ($config['meta_data_configuration']['meta_data_integrator']['enabled_integrator'] as $dataIntegrator) {
            if (in_array($dataIntegrator['integrator_name'], $enabledIntegrators, true)) {
                throw new InvalidConfigurationException(sprintf('Meta data integrator "%s" already has been added', $dataIntegrator['integrator_name']));
            }

            $enabledIntegrators[] = $dataIntegrator['integrator_name'];
        }
    }
}
