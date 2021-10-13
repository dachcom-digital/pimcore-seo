<?php

namespace SeoBundle\MetaData\Extractor;

use SeoBundle\Model\SeoMetaDataInterface;

interface ExtractorInterface
{
    public function supports(mixed $element): bool;

    public function updateMetaData(mixed $element, ?string $locale, SeoMetaDataInterface $seoMetadata): void;
}
