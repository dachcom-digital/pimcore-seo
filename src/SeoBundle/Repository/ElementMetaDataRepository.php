<?php

namespace SeoBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SeoBundle\Model\ElementMetaData;

class ElementMetaDataRepository implements ElementMetaDataRepositoryInterface
{
    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(ElementMetaData::class);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(string $elementType, int $elementId)
    {
        return $this->repository->findBy([
            'elementType' => $elementType,
            'elementId'   => $elementId
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function findByIntegrator(string $elementType, int $elementId, string $integrator)
    {
        return $this->repository->findOneBy([
            'elementType' => $elementType,
            'elementId'   => $elementId,
            'integrator'  => $integrator
        ]);
    }
}
