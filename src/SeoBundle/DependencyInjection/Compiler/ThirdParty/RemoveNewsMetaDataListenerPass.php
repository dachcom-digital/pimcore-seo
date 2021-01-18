<?php

namespace SeoBundle\DependencyInjection\Compiler\ThirdParty;

use SeoBundle\Tool\Bundle;
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

        if (Bundle::hasDachcomBundle('NewsBundle', $container->getParameter('kernel.bundles')) === false) {
            return;
        }

        if ($container->hasDefinition($definition)) {
            $container->removeDefinition($definition);
        }
    }
}
