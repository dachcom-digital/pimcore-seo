<?php

namespace SeoBundle\Repository;

use SeoBundle\Model\QueueEntryInterface;

interface QueueEntryRepositoryInterface
{
    /**
     * @return array<int, QueueEntryInterface>
     */
    public function findAll(?array $orderBy = null): array;

    /**
     * @return array<int, QueueEntryInterface>
     */
    public function findAllForWorker(string $workerName, ?array $orderBy = null): array;

    public function findAtLeastOneForWorker(string $workerName): ?QueueEntryInterface;
}
