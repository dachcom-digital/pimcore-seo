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
use SeoBundle\Model\QueueEntry;
use SeoBundle\Model\QueueEntryInterface;

class QueueEntryRepository implements QueueEntryRepositoryInterface
{
    protected EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(QueueEntry::class);
    }

    public function findAll(?array $orderBy = null): array
    {
        return $this->repository->findBy([], $orderBy);
    }

    public function findAllForWorker(string $workerName, ?array $orderBy = null): array
    {
        return $this->repository->findBy(['worker' => $workerName], $orderBy);
    }

    public function findAtLeastOneForWorker(string $workerName): ?QueueEntryInterface
    {
        return $this->repository->findOneBy(['worker' => $workerName]);
    }
}
