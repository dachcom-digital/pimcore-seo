<?php

namespace SeoBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use SeoBundle\Model\ElementMetaData;
use SeoBundle\Model\ElementMetaDataInterface;

class ElementMetaDataRepository implements ElementMetaDataRepositoryInterface
{
    protected EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(ElementMetaData::class);
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('e');
    }

    public function findAll(string $elementType, int $elementId, ?string $releaseType = ElementMetaDataInterface::RELEASE_TYPE_PUBLIC): array
    {
        $conditions = [
            'elementType' => $elementType,
            'elementId'   => $elementId
        ];

        if ($releaseType !== null) {
            $conditions['releaseType'] = $releaseType;
        }

        return $this->repository->findBy($conditions);
    }

    public function findByIntegrator(
        string $elementType,
        int $elementId,
        string $integrator,
        string $releaseType = ElementMetaDataInterface::RELEASE_TYPE_PUBLIC
    ): ?ElementMetaDataInterface {
        return $this->repository->findOneBy([
            'elementType' => $elementType,
            'elementId'   => $elementId,
            'integrator'  => $integrator,
            'releaseType' => $releaseType,
        ]);
    }
}
