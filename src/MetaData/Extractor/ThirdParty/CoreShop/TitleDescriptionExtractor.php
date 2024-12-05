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

namespace SeoBundle\MetaData\Extractor\ThirdParty\CoreShop;

use SeoBundle\MetaData\Extractor\ExtractorInterface;
use SeoBundle\Model\SeoMetaDataInterface;

final class TitleDescriptionExtractor implements ExtractorInterface
{
    public function supports(mixed $element): bool
    {
        return $element instanceof \CoreShop\Component\SEO\Model\SEOAwareInterface;
    }

    public function updateMetadata(mixed $element, ?string $locale, SeoMetaDataInterface $seoMetadata): void
    {
        if (method_exists($element, 'getMetaTitle') && !empty($element->getMetaTitle($locale))) {
            $seoMetadata->setTitle($element->getMetaTitle($locale));
        } elseif (method_exists($element, 'getName') && !empty($element->getName($locale))) {
            $seoMetadata->setTitle($element->getName($locale));
        }

        if (method_exists($element, 'getMetaDescription') && !empty($element->getMetaDescription($locale))) {
            $seoMetadata->setMetaDescription($element->getMetaDescription($locale));
        } elseif (method_exists($element, 'getShortDescription') && !empty($element->getShortDescription($locale))) {
            $seoMetadata->setMetaDescription($element->getShortDescription($locale));
        }
    }
}
