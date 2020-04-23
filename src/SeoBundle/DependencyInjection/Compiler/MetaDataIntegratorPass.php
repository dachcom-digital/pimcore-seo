<?php

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
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
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

    /**
     * @param string     $identifier
     * @param array      $integratorConfiguration
     * @param Definition $definition
     *
     * @throws \Exception
     */
    public function setDefinitionConfiguration(string $identifier, array $integratorConfiguration, Definition $definition)
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
        /** @var IntegratorInterface $class */
        $class = $definition->getClass();
        $class::configureOptions($options);

        try {
            $resolvedOptions = $options->resolve($integratorConfig);
        } catch (\Throwable $e) {
            throw new \Exception(sprintf('Invalid "%s" meta data integrator options. %s', $identifier, $e->getMessage()));
        }

        $definition->addMethodCall('setConfiguration', [$resolvedOptions]);
    }
}
