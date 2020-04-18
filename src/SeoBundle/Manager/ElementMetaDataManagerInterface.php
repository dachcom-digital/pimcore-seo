<?php

namespace SeoBundle\Manager;

use SeoBundle\Model\ElementMetaDataInterface;

interface ElementMetaDataManagerInterface
{
    /**
     * @return array
     */
    public function getMetaDataIntegratorConfiguration();

    /**
     * @param mixed $correspondingElement
     *
     * @return array
     */
    public function getMetaDataIntegratorBackendConfiguration($correspondingElement);

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
     *
     * @return array
     */
    public function getElementDataForBackend(string $elementType, int $elementId);

    /**
     * @param string $elementType
     * @param int    $elementId
     * @param string $integratorName
     * @param array  $data
     */
    public function saveElementData(string $elementType, int $elementId, string $integratorName, array $data);

    /**
     * @param string      $elementType
     * @param int         $elementId
     * @param string      $integratorName
     * @param string|null $template
     * @param array       $data
     */
    public function generatePreviewDataForElement(string $elementType, int $elementId, string $integratorName, ?string $template, array $data);

    /**
     * @param string $elementType
     * @param int    $elementId
     */
    public function deleteElementData(string $elementType, int $elementId);
}
