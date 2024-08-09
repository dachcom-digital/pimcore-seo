<?php

namespace SeoBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use SeoBundle\Model\ElementMetaDataInterface;

interface ElementMetaDataRepositoryInterface
{
    public function getQueryBuilder(): QueryBuilder;

    /**
     * @return array<int, ElementMetaDataInterface>
     */
    public function findAll(string $elementType, int $elementId, ?string $releaseType = ElementMetaDataInterface::RELEASE_TYPE_PUBLIC): array;

    public function findByIntegrator(string $elementType, int $elementId, string $integrator, string $releaseType = ElementMetaDataInterface::RELEASE_TYPE_PUBLIC): ?ElementMetaDataInterface;
}
