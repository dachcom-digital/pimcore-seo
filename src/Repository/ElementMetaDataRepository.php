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
