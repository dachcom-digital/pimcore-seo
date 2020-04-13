<?php

namespace SeoBundle\Manager;

use SeoBundle\Model\ElementMetaDataInterface;

interface ElementMetaDataManagerInterface
{
    /**
     * @param string $elementType
     * @param int    $elementId
     *
     * @return ElementMetaDataInterface[]
     */
    public function getElementData(string $elementType, int $elementId);

    /**
     * @param string $elementType
     * @param int    $elementId
     * @param string $integratorName
     * @param array  $data
     */
    public function saveElementData(string $elementType, int $elementId, string $integratorName, array $data);

    /**
     * @param string $elementType
     * @param int    $elementId
     */
    public function deleteElementData(string $elementType, int $elementId);
}
