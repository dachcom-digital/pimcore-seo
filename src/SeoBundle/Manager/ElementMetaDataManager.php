<?php

namespace SeoBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use SeoBundle\Model\ElementMetaData;
use SeoBundle\Model\ElementMetaDataInterface;
use SeoBundle\Repository\ElementMetaDataRepositoryInterface;

class ElementMetaDataManager implements ElementMetaDataManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ElementMetaDataRepositoryInterface
     */
    protected $elementMetaDataRepository;

    /**
     * @param EntityManagerInterface             $entityManager
     * @param ElementMetaDataRepositoryInterface $elementMetaDataRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ElementMetaDataRepositoryInterface $elementMetaDataRepository
    ) {
        $this->entityManager = $entityManager;
        $this->elementMetaDataRepository = $elementMetaDataRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getElementData(string $elementType, int $elementId)
    {
        return $this->elementMetaDataRepository->findAll($elementType, $elementId);
    }

    /**
     * {@inheritDoc}
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

        // @todo: sanitize / validate data before saving?
        $elementMetaData->setData($data);

        $this->entityManager->persist($elementMetaData);
        $this->entityManager->flush();
    }

    /**
     * {@inheritDoc}
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
}
