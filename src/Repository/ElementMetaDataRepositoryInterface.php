<?php

namespace SeoBundle\Repository;

use SeoBundle\Model\ElementMetaDataInterface;

interface ElementMetaDataRepositoryInterface
{
    /**
     * @return array<int, ElementMetaDataInterface>
     */
    public function findAll(string $elementType, int $elementId): array;

    public function findByIntegrator(string $elementType, int $elementId, string $integrator): ?ElementMetaDataInterface;
}
