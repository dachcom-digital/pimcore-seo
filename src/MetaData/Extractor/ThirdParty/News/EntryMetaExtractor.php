<?php

namespace SeoBundle\MetaData\Extractor\ThirdParty\News;

use NewsBundle\Model\EntryInterface;
use NewsBundle\Generator\HeadMetaGeneratorInterface;
use SeoBundle\MetaData\Extractor\ExtractorInterface;
use SeoBundle\Model\SeoMetaDataInterface;

final class EntryMetaExtractor implements ExtractorInterface
{
    private HeadMetaGeneratorInterface $headMetaGenerator;

    public function __construct(HeadMetaGeneratorInterface $headMetaGenerator)
    {
        $this->headMetaGenerator = $headMetaGenerator;
    }

    public function supports(mixed $element): bool
    {
        return $element instanceof EntryInterface;
    }

    public function updateMetadata(mixed $element, ?string $locale, SeoMetaDataInterface $seoMetadata): void
    {
        $seoMetadata->setMetaDescription($this->headMetaGenerator->generateDescription($element));
        $seoMetadata->setTitle($this->headMetaGenerator->generateTitle($element));

        foreach ($this->headMetaGenerator->generateMeta($element) as $property => $content) {
            if (!empty($content)) {
                $seoMetadata->addExtraProperty($property, $content);
            }
        }
    }
}
