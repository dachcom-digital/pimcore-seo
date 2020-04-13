<?php

namespace SeoBundle\MetaData\Extractor;

use SeoBundle\Model\SeoMetaDataInterface;

class DescriptionExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($element)
    {
        return true; //$element instanceof DescriptionReadInterface; // ???
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetaData($element, SeoMetaDataInterface $seoMetadata)
    {
        //$seoMetadata->setMetaDescription(get_class($element));
        //$seoMetadata->setTitle("asdfdsafafsdasdf");
    }
}
