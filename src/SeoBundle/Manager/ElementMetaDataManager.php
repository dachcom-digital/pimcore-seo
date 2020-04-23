<?php

namespace SeoBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Page;
use SeoBundle\Model\ElementMetaData;
use SeoBundle\Model\ElementMetaDataInterface;
use SeoBundle\Registry\MetaDataIntegratorRegistryInterface;
use SeoBundle\Repository\ElementMetaDataRepositoryInterface;

class ElementMetaDataManager implements ElementMetaDataManagerInterface
{
    /**
     * @var array
     */
    protected $integratorConfiguration;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var MetaDataIntegratorRegistryInterface
     */
    protected $metaDataIntegratorRegistry;

    /**
     * @var ElementMetaDataRepositoryInterface
     */
    protected $elementMetaDataRepository;

    /**
     * @param array                               $integratorConfiguration
     * @param EntityManagerInterface              $entityManager
     * @param MetaDataIntegratorRegistryInterface $metaDataIntegratorRegistry
     * @param ElementMetaDataRepositoryInterface  $elementMetaDataRepository
     */
    public function __construct(
        array $integratorConfiguration,
        EntityManagerInterface $entityManager,
        MetaDataIntegratorRegistryInterface $metaDataIntegratorRegistry,
        ElementMetaDataRepositoryInterface $elementMetaDataRepository
    ) {
        $this->integratorConfiguration = $integratorConfiguration;
        $this->entityManager = $entityManager;
        $this->metaDataIntegratorRegistry = $metaDataIntegratorRegistry;
        $this->elementMetaDataRepository = $elementMetaDataRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaDataIntegratorConfiguration()
    {
        return $this->integratorConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaDataIntegratorBackendConfiguration($correspondingElement)
    {
        $configuration = [];

        foreach ($this->integratorConfiguration['enabled_integrator'] as $enabledIntegrator) {
            $enabledIntegratorName = $enabledIntegrator['integrator_name'];
            $metaDataIntegrator = $this->metaDataIntegratorRegistry->has($enabledIntegratorName) ? $this->metaDataIntegratorRegistry->get($enabledIntegratorName) : null;
            $config = $metaDataIntegrator === null ? [] : $metaDataIntegrator->getBackendConfiguration($correspondingElement);
            $configuration[$enabledIntegratorName] = $config;
        }

        return $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getElementData(string $elementType, int $elementId)
    {
        $elementValues = $this->elementMetaDataRepository->findAll($elementType, $elementId);

        // BC Reason: If old document metadata is available, use it!
        // @todo: make this decision configurable? We don't need this within fresh installations!
        $elementValues = $this->checkForLegacyData($elementValues, $elementType, $elementId);

        return $elementValues;
    }

    /**
     * {@inheritdoc}
     */
    public function getElementDataForBackend(string $elementType, int $elementId)
    {
        $parsedData = [];
        $data = $this->getElementData($elementType, $elementId);

        foreach ($data as $element) {
            $metaDataIntegrator = $this->metaDataIntegratorRegistry->get($element->getIntegrator());
            $parsedData[$element->getIntegrator()] = $metaDataIntegrator->validateBeforeBackend($elementType, $elementId, $element->getData());
        }

        // BC Reason: If old document metadata is available, use it!
        // @todo: make this decision configurable? We don't need this within fresh installations!
        $parsedData = $this->checkForLegacyBackendData($parsedData, $elementType, $elementId);

        return $parsedData;
    }

    /**
     * {@inheritdoc}
     */
    public function saveElementData(string $elementType, int $elementId, string $integratorName, array $data)
    {
        $elementMetaData = $this->elementMetaDataRepository->findByIntegrator($elementType, $elementId, $integratorName);

        if (!$elementMetaData instanceof ElementMetaDataInterface) {
            $elementMetaData = new ElementMetaData();
            $elementMetaData->setElementType($elementType);
            $elementMetaData->setElementId($elementId);
            $elementMetaData->setIntegrator($integratorName);
        }

        $metaDataIntegrator = $this->metaDataIntegratorRegistry->get($integratorName);
        $sanitizedData = $metaDataIntegrator->validateBeforePersist($elementType, $elementId, $data);

        // remove empty meta data
        if ($sanitizedData === null) {
            if ($elementMetaData->getId() > 0) {
                $this->entityManager->remove($elementMetaData);
                $this->entityManager->flush();
            }

            return;
        }

        $elementMetaData->setData($sanitizedData);

        $this->entityManager->persist($elementMetaData);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function generatePreviewDataForElement(string $elementType, int $elementId, string $integratorName, ?string $template, array $data)
    {
        if ($elementType === 'object') {
            $element = DataObject::getById($elementId);
        } else {
            $element = Document::getById($elementId);
        }

        $metaDataIntegrator = $this->metaDataIntegratorRegistry->get($integratorName);

        return $metaDataIntegrator->getPreviewParameter($element, $template, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteElementData(string $elementType, int $elementId)
    {
        $elementData = $this->elementMetaDataRepository->findAll($elementType, $elementId);

        if (count($elementData) === 0) {
            return;
        }

        foreach ($elementData as $element) {
            $this->entityManager->remove($element);
        }

        $this->entityManager->flush();
    }

    /**
     * @param ElementMetaDataInterface[] $elements
     * @param string                     $elementType
     * @param int                        $elementId
     *
     * @return array|ElementMetaDataInterface[]
     */
    protected function checkForLegacyData(array $elements, string $elementType, int $elementId)
    {
        // as soon we have configured seo elements,
        // we'll never check the document again. It's all about performance.
        if (count($elements) > 0) {
            return $elements;
        }

        if ($elementType !== 'document') {
            return $elements;
        }

        $legacyData = $this->getDocumentLegacyData($elementId);
        if ($legacyData === null) {
            return $elements;
        }

        if ($legacyData['hasTitleDescriptionIntegrator'] === true) {
            $legacyTitleDescription = new ElementMetaData();
            $legacyTitleDescription->setElementType($elementType);
            $legacyTitleDescription->setElementId($elementId);
            $legacyTitleDescription->setIntegrator('title_description');
            $legacyTitleDescription->setData(['title' => $legacyData['title'], 'description' => $legacyData['description']]);
            $elements[] = $legacyTitleDescription;
        }

        if ($legacyData['hasHtmlTagIntegrator'] === true && is_array($legacyData['metaData']) && count($legacyData['metaData']) > 0) {
            $legacyMetaData = new ElementMetaData();
            $legacyMetaData->setElementType($elementType);
            $legacyMetaData->setElementId($elementId);
            $legacyMetaData->setIntegrator('html_tag');
            $legacyMetaData->setData($legacyData['metaData']);
            $elements[] = $legacyMetaData;
        }

        return $elements;
    }

    /**
     * @param array  $parsedData
     * @param string $elementType
     * @param int    $elementId
     *
     * @return array
     */
    protected function checkForLegacyBackendData(array $parsedData, string $elementType, int $elementId)
    {
        // as soon we have configured seo elements,
        // we'll never check the document again. It's all about performance.
        if (count($parsedData) !== 0) {
            return $parsedData;
        }

        if ($elementType !== 'document') {
            return $parsedData;
        }

        $legacyData = $this->getDocumentLegacyData($elementId);
        if ($legacyData === null) {
            return $parsedData;
        }

        if ($legacyData['hasTitleDescriptionIntegrator'] === true) {
            $legacyTitleDescription = [];
            if (!empty($legacyData['title'])) {
                $legacyTitleDescription['title'] = $legacyData['title'];
            }

            if (!empty($legacyData['description'])) {
                $legacyTitleDescription['description'] = $legacyData['description'];
            }

            if (count($legacyTitleDescription) > 0) {
                $parsedData['title_description'] = $legacyTitleDescription;
            }
        }

        if ($legacyData['hasHtmlTagIntegrator'] === true) {
            $legacyHtmlTags = [];
            if (is_array($legacyData['metaData'])) {
                foreach ($legacyData['metaData'] as $metaDataRow) {
                    $legacyHtmlTags[] = $metaDataRow;
                }
            }

            if (count($legacyHtmlTags) > 0) {
                $parsedData['html_tag'] = $legacyHtmlTags;
            }
        }

        return $parsedData;
    }

    /**
     * @param int $documentId
     *
     * @return array|null
     */
    protected function getDocumentLegacyData($documentId)
    {
        $enabledIntegrator = $this->integratorConfiguration['enabled_integrator'];
        if (!is_array($enabledIntegrator) || count($enabledIntegrator) === 0) {
            return null;
        }

        $hasTitleDescriptionIntegrator = array_search('title_description', array_column($enabledIntegrator, 'integrator_name'));
        $hasHtmlTagIntegrator = array_search('html_tag', array_column($enabledIntegrator, 'integrator_name'));

        // no required integrators are active. skip this task...
        if ($hasTitleDescriptionIntegrator === false && $hasHtmlTagIntegrator === false) {
            return null;
        }

        $document = Document::getById($documentId);
        if (!$document instanceof Page) {
            return null;
        }

        return [
            'description'                   => $document->getDescription(),
            'title'                         => $document->getTitle(),
            'metaData'                      => $document->getMetaData(),
            'hasTitleDescriptionIntegrator' => $hasTitleDescriptionIntegrator !== false,
            'hasHtmlTagIntegrator'          => $hasHtmlTagIntegrator !== false,
        ];
    }
}
