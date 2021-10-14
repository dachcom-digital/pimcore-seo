<?php

namespace SeoBundle\Manager;

use SeoBundle\Model\ElementMetaDataInterface;

interface ElementMetaDataManagerInterface
{
    public function getMetaDataIntegratorConfiguration(): array;

    public function getMetaDataIntegratorBackendConfiguration(mixed $correspondingElement): array;

    /**
     * @return array<int, ElementMetaDataInterface>
     */
    public function getElementData(string $elementType, int $elementId): array;

    public function getElementDataForBackend(string $elementType, int $elementId): array;

    public function saveElementData(string $elementType, int $elementId, string $integratorName, array $data): void;

    public function generatePreviewDataForElement(string $elementType, int $elementId, string $integratorName, ?string $template, array $data): array;

    public function deleteElementData(string $elementType, int $elementId): void;
}
