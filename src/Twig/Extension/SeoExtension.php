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

namespace SeoBundle\Twig\Extension;

use SeoBundle\MetaData\MetaDataProviderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SeoExtension extends AbstractExtension
{
    protected MetaDataProviderInterface $metaDataProvider;

    public function __construct(MetaDataProviderInterface $metaDataProvider)
    {
        $this->metaDataProvider = $metaDataProvider;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('seo_update_metadata', [$this, 'updateMetadata']),
        ];
    }

    public function updateMetadata(mixed $element, ?string $locale): void
    {
        $this->metaDataProvider->updateSeoElement($element, $locale);
    }

    public function getName(): string
    {
        return 'seo_metadata';
    }
}
