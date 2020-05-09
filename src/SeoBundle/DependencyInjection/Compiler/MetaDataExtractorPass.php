<?php

namespace SeoBundle\DependencyInjection\Compiler;

use SeoBundle\Registry\MetaDataExtractorRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

final class MetaDataExtractorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $i = 0;
        $services = [];
        $definition = $container->getDefinition(MetaDataExtractorRegistry::class);

        foreach ($container->findTaggedServiceIds('seo.meta_data.extractor', true) as $serviceId => $attributes) {
            foreach ($attributes as $attribute) {
                $priority = $attribute['priority'] ?? 0;
                $services[] = [$priority, ++$i, $serviceId, $attribute];
            }
        }

        uasort($services, static function ($a, $b) {
            return $b[0] <=> $a[0] ?: $a[1] <=> $b[1];
        });

        foreach ($services as [, $index, $serviceId, $attributes]) {
            if (!isset($attributes['identifier'])) {
                throw new InvalidArgumentException(sprintf('Attribute "identifier" missing for meta data extractor "%s".', $serviceId));
            }

            $definition->addMethodCall('register', [new Reference($serviceId), $attributes['identifier']]);
        }
    }
}
