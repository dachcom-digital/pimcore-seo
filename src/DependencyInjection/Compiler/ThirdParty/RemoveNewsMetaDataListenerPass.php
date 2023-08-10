<?php

namespace SeoBundle\DependencyInjection\Compiler\ThirdParty;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RemoveNewsMetaDataListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!in_array('dachcom_news', $container->getParameter('seo.third_party.enabled'), true)) {
            return;
        }

        $definition = 'NewsBundle\EventListener\MetaDataListener';

        if ($container->hasDefinition($definition)) {
            $container->removeDefinition($definition);
        }
    }
}
