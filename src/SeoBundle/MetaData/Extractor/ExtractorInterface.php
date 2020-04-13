<?php

namespace SeoBundle\MetaData\Extractor;

use SeoBundle\Model\SeoMetaDataInterface;

interface ExtractorInterface
{
    /**
     * @param object $element
     *
     * @return bool
     */
    public function supports($element);

    /**
     * @param object               $element
     * @param SeoMetaDataInterface $seoMetadata
     */
    public function updateMetaData($element, SeoMetaDataInterface $seoMetadata);
}
