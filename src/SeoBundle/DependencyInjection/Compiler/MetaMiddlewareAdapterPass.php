<?php

namespace SeoBundle\DependencyInjection\Compiler;

use SeoBundle\Middleware\MiddlewareDispatcher;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

final class MetaMiddlewareAdapterPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(MiddlewareDispatcher::class);

        foreach ($container->findTaggedServiceIds('seo.meta_data.middleware.adapter', true) as $serviceId => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['identifier'])) {
                    throw new InvalidArgumentException(sprintf('Attribute "identifier" missing for meta middleware "%s".', $serviceId));
                }
                $definition->addMethodCall('registerMiddlewareAdapter', [$attribute['identifier'], new Reference($serviceId)]);
            }
        }
    }
}
