<?php

namespace SeoBundle\MetaData;

use Pimcore\Templating\Helper\HeadMeta;
use Pimcore\Templating\Helper\HeadTitle;
use SeoBundle\MetaData\Extractor\ExtractorInterface;
use SeoBundle\Model\SeoMetaData;
use SeoBundle\Registry\MetaDataExtractorRegistryInterface;

class MetaDataProvider implements MetaDataProviderInterface
{
    /**
     * @var HeadMeta
     */
    protected $headMeta;

    /**
     * @var HeadTitle
     */
    protected $headTitle;

    /**
     * @var MetaDataExtractorRegistryInterface
     */
    protected $extractorRegistry;

    /**
     * @param HeadMeta                           $headMeta
     * @param HeadTitle                          $headTitle
     * @param MetaDataExtractorRegistryInterface $extractorRegistry
     */
    public function __construct(
        HeadMeta $headMeta,
        HeadTitle $headTitle,
        MetaDataExtractorRegistryInterface $extractorRegistry
    ) {
        $this->headMeta = $headMeta;
        $this->headTitle = $headTitle;
        $this->extractorRegistry = $extractorRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function updateSeoElement($element, ?string $locale)
    {
        $seoMetadata = $this->getSeoMetaData($element, $locale);

        if ($extraProperties = $seoMetadata->getExtraProperties()) {
            foreach ($extraProperties as $key => $value) {
                $this->headMeta->appendProperty($key, $value);
            }
        }

        if ($extraNames = $seoMetadata->getExtraNames()) {
            foreach ($extraNames as $key => $value) {
                $this->headMeta->appendName($key, $value);
            }
        }

        if ($extraHttp = $seoMetadata->getExtraHttp()) {
            foreach ($extraHttp as $key => $value) {
                $this->headMeta->appendHttpEquiv($key, $value);
            }
        }

        if ($schemaBlocks = $seoMetadata->getSchema()) {
            foreach ($schemaBlocks as $schemaBlock) {
                if (is_array($schemaBlock)) {
                    $this->headMeta->addRaw(sprintf('<script type="application/ld+json">%s</script>', json_encode($schemaBlock)));
                }
            }
        }

        if ($raw = $seoMetadata->getRaw()) {
            foreach ($raw as $rawValue) {
                $this->headMeta->addRaw($rawValue);
            }
        }

        if ($seoMetadata->getTitle()) {
            $this->headTitle->set($seoMetadata->getTitle());
        }

        if ($seoMetadata->getMetaDescription()) {
            $this->headMeta->setDescription($seoMetadata->getMetaDescription());
        }
    }

    /**
     * @param mixed       $element
     * @param string|null $locale
     * @param array       $excludedExtractors
     *
     * @return SeoMetaData
     * @internal
     */
    public function getSeoMetaDataForBackend($element, ?string $locale, array $excludedExtractors = [])
    {
        // @todo: check if element has a given SeoMetaData Element?
        $seoMetaData = new SeoMetaData();
        $extractors = $this->getExtractorsForElement($element);
        foreach ($extractors as $extractorName => $extractor) {
            if (in_array($extractorName, $excludedExtractors)) {
                continue;
            }

            $extractor->updateMetadata($element, $locale, $seoMetaData);
        }

        return $seoMetaData;
    }

    /**
     * @param mixed       $element
     * @param string|null $locale
     *
     * @return SeoMetaData
     */
    protected function getSeoMetaData($element, ?string $locale)
    {
        // @todo: check if element has a given SeoMetaData Element?
        $seoMetaData = new SeoMetaData();
        $extractors = $this->getExtractorsForElement($element);
        foreach ($extractors as $extractor) {
            $extractor->updateMetadata($element, $locale, $seoMetaData);
        }

        return $seoMetaData;
    }

    /**
     * @param object $element
     *
     * @return ExtractorInterface[]
     */
    protected function getExtractorsForElement($element)
    {
        return array_filter($this->extractorRegistry->getAll(), function (ExtractorInterface $extractor) use ($element) {
            return $extractor->supports($element);
        });
    }
}
