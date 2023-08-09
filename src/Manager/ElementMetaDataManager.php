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
    protected array $integratorConfiguration;
    protected EntityManagerInterface $entityManager;
    protected MetaDataIntegratorRegistryInterface $metaDataIntegratorRegistry;
    protected ElementMetaDataRepositoryInterface $elementMetaDataRepository;

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

    public function getMetaDataIntegratorConfiguration(): array
    {
        return $this->integratorConfiguration;
    }

    public function getMetaDataIntegratorBackendConfiguration(mixed $correspondingElement): array
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

    public function getElementData(string $elementType, int $elementId): array
    {
        $elementValues = $this->elementMetaDataRepository->findAll($elementType, $elementId);

        // BC Reason: If old document metadata is available, use it!
        // @todo: make this decision configurable? We don't need this within fresh installations!
        return $this->checkForLegacyData($elementValues, $elementType, $elementId);
    }

    public function getElementDataForBackend(string $elementType, int $elementId): array
    {
        $parsedData = [];
        $data = $this->getElementData($elementType, $elementId);

        foreach ($data as $element) {
            $metaDataIntegrator = $this->metaDataIntegratorRegistry->get($element->getIntegrator());
            $parsedData[$element->getIntegrator()] = $metaDataIntegrator->validateBeforeBackend($elementType, $elementId, $element->getData());
        }

        // BC Reason: If old document metadata is available, use it!
        // @todo: make this decision configurable? We don't need this within fresh installations!
        return $this->checkForLegacyBackendData($parsedData, $elementType, $elementId);
    }

    public function saveElementData(string $elementType, int $elementId, string $integratorName, array $data): void
    {
        $elementMetaData = $this->elementMetaDataRepository->findByIntegrator($elementType, $elementId, $integratorName);

        if (!$elementMetaData instanceof ElementMetaDataInterface) {
            $elementMetaData = new ElementMetaData();
            $elementMetaData->setElementType($elementType);
            $elementMetaData->setElementId($elementId);
            $elementMetaData->setIntegrator($integratorName);
        }

        $metaDataIntegrator = $this->metaDataIntegratorRegistry->get($integratorName);
        $sanitizedData = $metaDataIntegrator->validateBeforePersist($elementType, $elementId, $data, $elementMetaData->getData());

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

    public function generatePreviewDataForElement(string $elementType, int $elementId, string $integratorName, ?string $template, array $data): array
    {
        if ($elementType === 'object') {
            $element = DataObject::getById($elementId);
        } else {
            $element = Document::getById($elementId);
        }

        $metaDataIntegrator = $this->metaDataIntegratorRegistry->get($integratorName);

        return $metaDataIntegrator->getPreviewParameter($element, $template, $data);
    }

    public function deleteElementData(string $elementType, int $elementId): void
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
     * @return array<int, ElementMetaDataInterface>
     */
    protected function checkForLegacyData(array $elements, string $elementType, int $elementId): array
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

        return $elements;
    }

    protected function checkForLegacyBackendData(array $parsedData, string $elementType, int $elementId): array
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

        return $parsedData;
    }

    protected function getDocumentLegacyData(int $documentId): ?array
    {
        $enabledIntegrator = $this->integratorConfiguration['enabled_integrator'];
        if (!is_array($enabledIntegrator) || count($enabledIntegrator) === 0) {
            return null;
        }

        $hasTitleDescriptionIntegrator = array_search('title_description', array_column($enabledIntegrator, 'integrator_name'), true);

        // no required integrators are active. skip this task...
        if ($hasTitleDescriptionIntegrator === false) {
            return null;
        }

        $document = Document::getById($documentId);
        if (!$document instanceof Page) {
            return null;
        }

        return [
            'description'                   => $document->getDescription(),
            'title'                         => $document->getTitle(),
            'hasTitleDescriptionIntegrator' => $hasTitleDescriptionIntegrator !== false
        ];
    }
}
