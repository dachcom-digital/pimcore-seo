<?php

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
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
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

    /**
     * @param string     $identifier
     * @param array      $workerConfiguration
     * @param Definition $definition
     *
     * @throws \Exception
     */
    public function setDefinitionConfiguration(string $identifier, array $workerConfiguration, Definition $definition)
    {
        $options = new OptionsResolver();
        /** @var IndexWorkerInterface $class */
        $class = $definition->getClass();
        $class::configureOptions($options);

        try {
            $resolvedOptions = $options->resolve($workerConfiguration);
        } catch (\Throwable $e) {
            throw new \Exception(sprintf('Invalid "%s" worker options. %s', $identifier, $e->getMessage()));
        }

        $definition->addMethodCall('setConfiguration', [$resolvedOptions]);
    }
}
