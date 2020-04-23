<?php

namespace SeoBundle\MetaData\Extractor;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Page;
use SeoBundle\Manager\ElementMetaDataManagerInterface;
use SeoBundle\Model\SeoMetaDataInterface;
use SeoBundle\Registry\MetaDataIntegratorRegistryInterface;

class IntegratorExtractor implements ExtractorInterface
{
    /**
     * @var array
     */
    protected $integratorConfiguration;

    /**
     * @var ElementMetaDataManagerInterface
     */
    protected $elementMetaDataManager;

    /**
     * @var MetaDataIntegratorRegistryInterface
     */
    protected $metaDataIntegratorRegistry;

    /**
     * @param array                               $integratorConfiguration
     * @param ElementMetaDataManagerInterface     $elementMetaDataManager
     * @param MetaDataIntegratorRegistryInterface $metaDataIntegratorRegistry
     */
    public function __construct(
        array $integratorConfiguration,
        ElementMetaDataManagerInterface $elementMetaDataManager,
        MetaDataIntegratorRegistryInterface $metaDataIntegratorRegistry
    ) {
        $this->integratorConfiguration = $integratorConfiguration;
        $this->elementMetaDataManager = $elementMetaDataManager;
        $this->metaDataIntegratorRegistry = $metaDataIntegratorRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element)
    {
        if ($element instanceof Concrete) {
            if ($this->integratorConfiguration['objects']['enabled'] === false) {
                return false;
            }

            return in_array($element->getClassName(), $this->integratorConfiguration['objects']['data_classes']);
        }

        if ($element instanceof Page) {
            return $this->integratorConfiguration['documents']['enabled'] === true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetaData($element, ?string $locale, SeoMetaDataInterface $seoMetadata)
    {
        $elementId = null;
        $elementType = null;

        if ($element instanceof DataObject) {
            $elementId = $element->getId();
            $elementType = 'object';
        } elseif ($element instanceof Document) {
            $elementId = $element->getId();
            $elementType = 'document';
        }

        if ($elementType === null) {
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
