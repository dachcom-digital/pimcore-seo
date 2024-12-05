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
