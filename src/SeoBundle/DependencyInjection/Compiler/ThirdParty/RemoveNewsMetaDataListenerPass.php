<?php

namespace SeoBundle\DependencyInjection\Compiler\ThirdParty;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RemoveNewsMetaDataListenerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = 'NewsBundle\EventListener\MetaDataListener';
        $bundles = $container->getParameter('kernel.bundles');

        if (!array_key_exists('NewsBundle', $bundles)) {
            return;
        }

        if ($container->hasDefinition($definition)) {
            $container->removeDefinition($definition);
        }
    }
}
