<?php

namespace SeoBundle\MetaData\Extractor\ThirdParty\CoreShop;

use SeoBundle\MetaData\Extractor\ExtractorInterface;
use SeoBundle\Model\SeoMetaDataInterface;

final class TitleDescriptionExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof \CoreShop\Component\SEO\Model\SEOAwareInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetadata($element, ?string $locale, SeoMetaDataInterface $seoMetadata)
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
