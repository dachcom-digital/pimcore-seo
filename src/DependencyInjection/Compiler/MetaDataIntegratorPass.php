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

use SeoBundle\MetaData\Integrator\IntegratorInterface;
use SeoBundle\Registry\MetaDataIntegratorRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MetaDataIntegratorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $integratorConfiguration = $container->getParameter('seo.meta_data_integrator.configuration');

        $definition = $container->getDefinition(MetaDataIntegratorRegistry::class);
        foreach ($container->findTaggedServiceIds('seo.meta_data.integrator', true) as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes['identifier'])) {
                    throw new InvalidArgumentException(sprintf('Attribute "identifier" missing for meta data integrator "%s".', $id));
                }

                $definition->addMethodCall('register', [new Reference($id), $attributes['identifier']]);
                $this->setDefinitionConfiguration($attributes['identifier'], $integratorConfiguration, $container->getDefinition($id));
            }
        }
    }

    public function setDefinitionConfiguration(string $identifier, array $integratorConfiguration, Definition $definition): void
    {
        $integratorConfig = null;
        foreach ($integratorConfiguration['enabled_integrator'] as $enabledIntegrator) {
            if ($enabledIntegrator['integrator_name'] === $identifier) {
                $integratorConfig = $enabledIntegrator['integrator_config'];

                break;
            }
        }

        if ($integratorConfig === null) {
            return;
        }

        $options = new OptionsResolver();
        $class = $definition->getClass();

        if (is_string($class) && is_subclass_of($class, IntegratorInterface::class)) {
            $class::configureOptions($options);
        }

        try {
            $resolvedOptions = $options->resolve($integratorConfig);
        } catch (\Throwable $e) {
            throw new \Exception(sprintf('Invalid "%s" meta data integrator options. %s', $identifier, $e->getMessage()));
        }

        $definition->addMethodCall('setConfiguration', [$resolvedOptions]);
    }
}
