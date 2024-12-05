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

use Pimcore\Tool;
use SeoBundle\MetaData\Extractor\ExtractorInterface;
use SeoBundle\Model\SeoMetaDataInterface;

final class OGExtractor implements ExtractorInterface
{
    public function supports(mixed $element): bool
    {
        return $element instanceof \CoreShop\Component\SEO\Model\SEOOpenGraphAwareInterface;
    }

    public function updateMetadata(mixed $element, ?string $locale, SeoMetaDataInterface $seoMetadata): void
    {
        if (method_exists($element, 'getMetaTitle') && !empty($element->getOGTitle($locale))) {
            $seoMetadata->addExtraProperty('og:title', $element->getOGTitle($locale));
        } elseif (method_exists($element, 'getName') && !empty($element->getName($locale))) {
            $seoMetadata->addExtraProperty('og:title', $element->getName($locale));
        }

        if (method_exists($element, 'getOGDescription') && !empty($element->getOGDescription($locale))) {
            $seoMetadata->addExtraProperty('og:description', $element->getOGDescription($locale));
        } elseif (method_exists($element, 'getShortDescription') && !empty($element->getShortDescription($locale))) {
            $seoMetadata->addExtraProperty('og:description', $element->getShortDescription($locale));
        }

        if (method_exists($element, 'getOGType') && !empty($element->getOGType())) {
            $seoMetadata->addExtraProperty('og:type', $element->getOGType());
        }

        if (method_exists($element, 'getImage') && !empty($element->getImage())) {
            $path = $element->getImage()->getThumbnail('coreshop_seo');
            $ogImage = (str_starts_with('http', $path) ? '' : Tool::getHostUrl()) . $path;
            $seoMetadata->addExtraProperty('og:image', $ogImage);
        }
    }
}
