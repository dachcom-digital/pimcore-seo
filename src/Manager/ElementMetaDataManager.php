<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace SeoBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Page;
use SeoBundle\MetaData\Integrator\XliffAwareIntegratorInterface;
use SeoBundle\Model\ElementMetaData;
use SeoBundle\Model\ElementMetaDataInterface;
use SeoBundle\Registry\MetaDataIntegratorRegistryInterface;
use SeoBundle\Repository\ElementMetaDataRepositoryInterface;

class ElementMetaDataManager implements ElementMetaDataManagerInterface
{
    public function __construct(
        protected array $integratorConfiguration,
        protected EntityManagerInterface $entityManager,
        protected MetaDataIntegratorRegistryInterface $metaDataIntegratorRegistry,
        protected ElementMetaDataRepositoryInterface $elementMetaDataRepository
    ) {
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

    public function getElementData(string $elementType, int $elementId, bool $allowDraftReleaseType = false): array
    {
        $fetchingReleaseType = ElementMetaDataInterface::RELEASE_TYPE_PUBLIC;
        if ($allowDraftReleaseType === true && $this->elementMetaDataExistsWithReleaseType($elementType, $elementId, ElementMetaDataInterface::RELEASE_TYPE_DRAFT)) {
            $fetchingReleaseType = ElementMetaDataInterface::RELEASE_TYPE_DRAFT;
        }

        $elementValues = $this->elementMetaDataRepository->findAll($elementType, $elementId, $fetchingReleaseType);

        // BC Reason: If old document metadata is available, use it!
        // @todo: make this decision configurable? We don't need this within fresh installations!

        return $this->checkForLegacyData($elementValues, $elementType, $elementId, $fetchingReleaseType);
    }

    public function getElementDataForBackend(string $elementType, int $elementId): array
    {
        $isDraft = false;
        $parsedData = [];
        $data = $this->getElementData($elementType, $elementId, true);

        foreach ($data as $element) {
            if ($element->getReleaseType() === ElementMetaDataInterface::RELEASE_TYPE_DRAFT) {
                $isDraft = true;
            }

            $metaDataIntegrator = $this->metaDataIntegratorRegistry->get($element->getIntegrator());
            $parsedData[$element->getIntegrator()] = $metaDataIntegrator->validateBeforeBackend($elementType, $elementId, $element->getData());
        }

        // BC Reason: If old document metadata is available, use it!
        // @todo: make this decision configurable? We don't need this within fresh installations!

        return [
            'isDraft' => $isDraft,
            'data'    => $this->checkForLegacyBackendData($parsedData, $elementType, $elementId)
        ];
    }

    public function getElementDataForXliffExport(string $elementType, int $elementId, string $locale): array
    {
        $parsedData = [];
        $data = $this->getElementData($elementType, $elementId);

        foreach ($data as $element) {
            $metaDataIntegrator = $this->metaDataIntegratorRegistry->get($element->getIntegrator());
            if (!$metaDataIntegrator instanceof XliffAwareIntegratorInterface) {
                continue;
            }

            $parsedData[$element->getIntegrator()] = $metaDataIntegrator->validateBeforeXliffExport(
                $elementType,
                $elementId,
                $element->getData(),
                $locale
            );
        }

        return $parsedData;
    }

    public function saveElementDataFromXliffImport(string $elementType, int $elementId, array $rawData, string $locale): void
    {
        $integratorValues = [];

        foreach ($rawData as $integrator => $integratorRawData) {
            if (!is_array($integratorRawData)) {
                continue;
            }

            $metaDataIntegrator = $this->metaDataIntegratorRegistry->get($integrator);
            if (!$metaDataIntegrator instanceof XliffAwareIntegratorInterface) {
                continue;
            }

            $integratorValues[$integrator] = $metaDataIntegrator->validateBeforeXliffImport($elementType, $elementId, $integratorRawData, $locale);
        }

        foreach ($integratorValues as $integratorName => $integratorData) {
            $sanitizedData = is_array($integratorData) ? $integratorData : [];
            $this->saveElementData($elementType, $elementId, $integratorName, $sanitizedData, true);
        }
    }

    public function saveElementData(
        string $elementType,
        int $elementId,
        string $integratorName,
        array $data,
        bool $merge = false,
        string $releaseType = ElementMetaDataInterface::RELEASE_TYPE_PUBLIC
    ): void {
        $elementMetaData = $this->determinateElementMetaEntity($elementType, $elementId, $integratorName, $releaseType);

        if (!$elementMetaData instanceof ElementMetaDataInterface) {
            $elementMetaData = new ElementMetaData();
            $elementMetaData->setElementType($elementType);
            $elementMetaData->setElementId($elementId);
            $elementMetaData->setIntegrator($integratorName);
            $elementMetaData->setReleaseType($releaseType);
        }

        $metaDataIntegrator = $this->metaDataIntegratorRegistry->get($integratorName);
        $sanitizedData = $metaDataIntegrator->validateBeforePersist($elementType, $elementId, $data, $elementMetaData->getData(), $merge);

        // remove empty meta data
        if ($sanitizedData === null) {
            if ($releaseType === ElementMetaDataInterface::RELEASE_TYPE_DRAFT) {
                // if draft, we still persist an empty element
                // to determinate reset when publish state is incoming

                if (
                    $elementMetaData->getId() > 0 ||
                    $this->elementMetaDataExistsWithReleaseType($elementType, $elementId, ElementMetaDataInterface::RELEASE_TYPE_PUBLIC, $integratorName)
                ) {
                    $this->persistElementMetaData($elementMetaData, []);
                }

                return;
            }

            if ($elementMetaData->getId() > 0) {
                $this->entityManager->remove($elementMetaData);
                $this->entityManager->flush();
            }

            return;
        }

        $this->persistElementMetaData($elementMetaData, $sanitizedData);
    }

    private function persistElementMetaData(ElementMetaDataInterface $elementMetaData, array $data): void
    {
        $elementMetaData->setData($data);
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

    public function deleteElementData(string $elementType, int $elementId, ?string $releaseType = ElementMetaDataInterface::RELEASE_TYPE_PUBLIC): void
    {
        $elementData = $this->elementMetaDataRepository->findAll($elementType, $elementId, $releaseType);

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
    protected function checkForLegacyData(array $elements, string $elementType, int $elementId, string $releaseType = ElementMetaDataInterface::RELEASE_TYPE_PUBLIC): array
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
            $legacyTitleDescription->setReleaseType($releaseType);
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

        $hasTitleDescriptionIntegrator = array_search(
            'title_description',
            array_column($enabledIntegrator, 'integrator_name'),
            true
        );

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
            'hasTitleDescriptionIntegrator' => true
        ];
    }

    private function determinateElementMetaEntity(
        string $elementType,
        int $elementId,
        string $integratorName,
        string $releaseType = ElementMetaDataInterface::RELEASE_TYPE_PUBLIC
    ): ?ElementMetaDataInterface {
        $hasDraft = $this->elementMetaDataExistsWithReleaseType($elementType, $elementId, ElementMetaDataInterface::RELEASE_TYPE_DRAFT);

        if ($releaseType === ElementMetaDataInterface::RELEASE_TYPE_PUBLIC && $hasDraft === true) {
            // delete draft
            $this->deleteElementData($elementType, $elementId, ElementMetaDataInterface::RELEASE_TYPE_DRAFT);

            return $this->elementMetaDataRepository->findByIntegrator($elementType, $elementId, $integratorName, $releaseType);
        }

        return $this->elementMetaDataRepository->findByIntegrator($elementType, $elementId, $integratorName, $releaseType);
    }

    private function elementMetaDataExistsWithReleaseType(string $elementType, int $elementId, string $releaseType, ?string $integratorName = null): bool
    {
        $qb = $this->elementMetaDataRepository->getQueryBuilder();

        $qb
            ->select('COUNT(e.id)')
            ->andWhere('e.elementType = :elementType')
            ->andWhere('e.elementId = :elementId')
            ->andWhere('e.releaseType = :releaseType')
            ->setParameter('elementType', $elementType)
            ->setParameter('elementId', $elementId)
            ->setParameter('releaseType', $releaseType);

        if ($integratorName !== null) {
            $qb
                ->andWhere('e.integrator = :integratorName')
                ->setParameter('integratorName', $integratorName);
        }

        return $qb
                ->getQuery()
                ->getSingleScalarResult() > 0;
    }
}
