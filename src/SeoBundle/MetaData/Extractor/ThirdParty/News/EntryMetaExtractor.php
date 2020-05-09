<?php

namespace SeoBundle\MetaData\Extractor\ThirdParty\News;

use NewsBundle\Model\EntryInterface;
use NewsBundle\Generator\HeadMetaGeneratorInterface;
use SeoBundle\MetaData\Extractor\ExtractorInterface;
use SeoBundle\Model\SeoMetaDataInterface;

final class EntryMetaExtractor implements ExtractorInterface
{
    /**
     * @var HeadMetaGeneratorInterface
     */
    protected $headMetaGenerator;

    /**
     * @param HeadMetaGeneratorInterface $headMetaGenerator
     */
    public function __construct(HeadMetaGeneratorInterface $headMetaGenerator)
    {
        $this->headMetaGenerator = $headMetaGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof EntryInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetadata($element, ?string $locale, SeoMetaDataInterface $seoMetadata)
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
