<?php

namespace SeoBundle\Repository;

use SeoBundle\Model\ElementMetaDataInterface;

interface ElementMetaDataRepositoryInterface
{
    /**
     * @param string $elementType
     * @param int    $elementId
     *
     * @return  ElementMetaDataInterface[]
     */
    public function findAll(string $elementType, int $elementId);

    /**
     * @param string $elementType
     * @param int    $elementId
     * @param string $integrator
     *
     * @return ElementMetaDataInterface|null
     */
    public function findByIntegrator(string $elementType, int $elementId, string $integrator);
}
