<?php

namespace SeoBundle\MetaData\Extractor\ThirdParty\CoreShop;

use Pimcore\Tool;
use SeoBundle\MetaData\Extractor\ExtractorInterface;
use SeoBundle\Model\SeoMetaDataInterface;

final class OGExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof \CoreShop\Component\SEO\Model\SEOOpenGraphAwareInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetadata($element, ?string $locale, SeoMetaDataInterface $seoMetadata)
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
            $ogImage = Tool::getHostUrl() . $element->getImage()->getThumbnail('seo');
            $seoMetadata->addExtraProperty('og:image', $ogImage);
        }
    }
}
