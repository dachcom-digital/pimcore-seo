<?php

namespace SeoBundle\MetaData\Extractor;

use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Page;
use SeoBundle\Manager\ElementMetaDataManagerInterface;
use SeoBundle\Model\SeoMetaDataInterface;
use SeoBundle\Registry\MetaDataIntegratorRegistryInterface;

class IntegratorExtractor implements ExtractorInterface
{
    protected array $integratorConfiguration;
    protected ElementMetaDataManagerInterface $elementMetaDataManager;
    protected MetaDataIntegratorRegistryInterface $metaDataIntegratorRegistry;

    public function __construct(
        array $integratorConfiguration,
        ElementMetaDataManagerInterface $elementMetaDataManager,
        MetaDataIntegratorRegistryInterface $metaDataIntegratorRegistry
    ) {
        $this->integratorConfiguration = $integratorConfiguration;
        $this->elementMetaDataManager = $elementMetaDataManager;
        $this->metaDataIntegratorRegistry = $metaDataIntegratorRegistry;
    }

    public function supports(mixed $element): bool
    {
        if ($element instanceof Concrete) {
            if ($this->integratorConfiguration['objects']['enabled'] === false) {
                return false;
            }

            return in_array($element->getClassName(), $this->integratorConfiguration['objects']['data_classes'], true);
        }

        if ($element instanceof Page) {
            return $this->integratorConfiguration['documents']['enabled'] === true;
        }

        return false;
    }

    public function updateMetaData(mixed $element, ?string $locale, SeoMetaDataInterface $seoMetadata): void
    {
        $elementId = null;
        $elementType = null;

        if ($element instanceof Concrete) {
            $elementId = $element->getId();
            $elementType = 'object';
        } elseif ($element instanceof Document) {
            $elementId = $element->getId();
            $elementType = 'document';
        }

        if ($elementType === null || !elementId) {
            return;
        }

        $elementMetaData = $this->elementMetaDataManager->getElementData($elementType, $elementId);

        foreach ($elementMetaData as $elementMeta) {
            try {
                $metaDataIntegrator = $this->metaDataIntegratorRegistry->get($elementMeta->getIntegrator());
            } catch (\Exception $e) {
                // fail silently
                continue;
            }

            $metaDataIntegrator->updateMetaData($element, $elementMeta->getData(), $locale, $seoMetadata);
        }
    }
}
