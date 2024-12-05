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

namespace SeoBundle\DependencyInjection\Compiler\ThirdParty;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RemoveCoreShopExtractorListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!in_array('core_shop_seo', $container->getParameter('seo.third_party.enabled'), true)) {
            return;
        }

        $definitions = [
            'coreshop.seo.extractor.description' => 'CoreShop\Component\SEO\Extractor\DescriptionExtractor',
            'coreshop.seo.extractor.title'       => 'CoreShop\Component\SEO\Extractor\TitleExtractor',
            'coreshop.seo.extractor.og'          => 'CoreShop\Component\SEO\Extractor\OGExtractor',
            'coreshop.seo.extractor.image'       => 'CoreShop\Component\SEO\Extractor\ImageExtractor',
            'coreshop.seo.extractor.document'    => 'CoreShop\Component\SEO\Extractor\DocumentExtractor'
        ];

        foreach ($definitions as $aliasDefinition => $definition) {
            if ($container->hasAlias($aliasDefinition)) {
                $container->removeAlias($aliasDefinition);
            }

            if ($container->hasDefinition($definition)) {
                $container->removeDefinition($definition);
            }
        }
    }
}
