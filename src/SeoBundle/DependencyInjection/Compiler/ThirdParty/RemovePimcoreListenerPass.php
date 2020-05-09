<?php

namespace SeoBundle\DependencyInjection\Compiler\ThirdParty;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RemovePimcoreListenerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // Remove pimcore default DocumentMetaDataListener. We take care about that now!
        $dataIntegratorConfiguration = $container->getParameter('seo.meta_data_integrator.configuration');
        if ($dataIntegratorConfiguration['documents']['hide_pimcore_default_seo_panel'] === false) {
            return;
        }

        $class = 'Pimcore\Bundle\CoreBundle\EventListener\Frontend\DocumentMetaDataListener';
        if (!class_exists($class)) {
            return;
        }

        if (!$container->hasDefinition($class)) {
            return;
        }

        $container->removeDefinition($class);
    }
}
