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

use SeoBundle\Model\ElementMetaDataInterface;

interface ElementMetaDataManagerInterface
{
    public function getMetaDataIntegratorConfiguration(): array;

    public function getMetaDataIntegratorBackendConfiguration(mixed $correspondingElement): array;

    /**
     * @return array<int, ElementMetaDataInterface>
     */
    public function getElementData(string $elementType, int $elementId, bool $allowDraftReleaseType = false): array;

    public function getElementDataForBackend(string $elementType, int $elementId): array;

    public function getElementDataForXliffExport(string $elementType, int $elementId, string $locale): array;

    public function saveElementDataFromXliffImport(string $elementType, int $elementId, array $rawData, string $locale): void;

    public function saveElementData(
        string $elementType,
        int $elementId,
        string $integratorName,
        array $data,
        bool $merge = false,
        string $releaseType = ElementMetaDataInterface::RELEASE_TYPE_PUBLIC
    ): void;

    public function generatePreviewDataForElement(string $elementType, int $elementId, string $integratorName, ?string $template, array $data): array;

    public function deleteElementData(string $elementType, int $elementId, ?string $releaseType = ElementMetaDataInterface::RELEASE_TYPE_PUBLIC): void;
}
