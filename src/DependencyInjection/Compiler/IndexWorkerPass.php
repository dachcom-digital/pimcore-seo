<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace SeoBundle\DependencyInjection\Compiler;

use SeoBundle\Registry\IndexWorkerRegistry;
use SeoBundle\Worker\IndexWorkerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class IndexWorkerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(IndexWorkerRegistry::class);

        foreach ($container->findTaggedServiceIds('seo.index.worker', true) as $id => $tags) {
            foreach ($tags as $attributes) {
                $workerConfiguration = sprintf('seo.index.worker.config.%s', $attributes['identifier']);
                if (!$container->hasParameter($workerConfiguration)) {
                    continue;
                }

                $workerConfiguration = $container->getParameter($workerConfiguration);
                $definition->addMethodCall('register', [new Reference($id), $attributes['identifier']]);
                $this->setDefinitionConfiguration($attributes['identifier'], $workerConfiguration, $container->getDefinition($id));
            }
        }
    }

    public function setDefinitionConfiguration(string $identifier, array $workerConfiguration, Definition $definition): void
    {
        $options = new OptionsResolver();
        $class = $definition->getClass();

        if (is_string($class) && is_subclass_of($class, IndexWorkerInterface::class)) {
            $class::configureOptions($options);
        }

        try {
            $resolvedOptions = $options->resolve($workerConfiguration);
        } catch (\Throwable $e) {
            throw new \Exception(sprintf('Invalid "%s" worker options. %s', $identifier, $e->getMessage()));
        }

        $definition->addMethodCall('setConfiguration', [$resolvedOptions]);
    }
}
