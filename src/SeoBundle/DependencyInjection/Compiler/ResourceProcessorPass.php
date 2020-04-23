<?php

namespace SeoBundle\DependencyInjection\Compiler;

use SeoBundle\Registry\ResourceProcessorRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

final class ResourceProcessorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $i = 0;
        $services = [];
        $definition = $container->getDefinition(ResourceProcessorRegistry::class);

        foreach ($container->findTaggedServiceIds('seo.index.resource_processor', true) as $serviceId => $attributes) {
            foreach ($attributes as $attribute) {
                if (isset($attribute['priority'])) {
                    $priority = $attribute['priority'];
                }

                $priority = $priority ?? 0;
                $services[] = [$priority, ++$i, $serviceId, $attribute];
            }
        }

        uasort($services, static function ($a, $b) {
            return $b[0] <=> $a[0] ?: $a[1] <=> $b[1];
        });

        foreach ($services as [, $index, $serviceId, $attributes]) {
            if (!isset($attributes['identifier'])) {
                throw new InvalidArgumentException(sprintf('Attribute "identifier" missing for resource processor "%s".', $serviceId));
            }

            $definition->addMethodCall('register', [new Reference($serviceId), $attributes['identifier']]);
        }
    }
}
