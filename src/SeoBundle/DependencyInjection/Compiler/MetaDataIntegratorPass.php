<?php

namespace SeoBundle\DependencyInjection\Compiler;

use SeoBundle\Registry\MetaDataIntegratorRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

final class MetaDataIntegratorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(MetaDataIntegratorRegistry::class);
        foreach ($container->findTaggedServiceIds('seo.meta_data.integrator', true) as $id => $tags) {
            foreach ($tags as $attributes) {

                if (!isset($attributes['identifier'])) {
                    throw new InvalidArgumentException(sprintf('Attribute "identifier" missing for meta data integrator "%s".', $id));
                }

                $definition->addMethodCall('register', [new Reference($id), $attributes['identifier']]);
            }
        }
    }
}
