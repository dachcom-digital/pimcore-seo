<?php

namespace SeoBundle\DependencyInjection\Compiler;

use SeoBundle\Registry\IndexWorkerRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class IndexWorkerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(IndexWorkerRegistry::class);
        $workerConfiguration = $container->getParameter('seo.index.worker.config');

        foreach ($container->findTaggedServiceIds('seo.index.worker', true) as $id => $tags) {
            $workerDefinition = $container->getDefinition($id);
            foreach ($tags as $attributes) {

                if (isset($workerConfiguration[$attributes['identifier']])) {
                    $workerDefinition->addMethodCall('setConfiguration', [$workerConfiguration[$attributes['identifier']]]);
                }

                $definition->addMethodCall('register', [new Reference($id), $attributes['identifier']]);
            }
        }
    }
}
