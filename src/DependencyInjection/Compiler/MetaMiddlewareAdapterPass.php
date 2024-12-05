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

use SeoBundle\Middleware\MiddlewareDispatcher;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

final class MetaMiddlewareAdapterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
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
