<?php

namespace SeoBundle\Repository;

use SeoBundle\Model\QueueEntryInterface;

interface QueueEntryRepositoryInterface
{
    /**
     * @param string $workerName
     *
     * @return QueueEntryInterface|null
     */
    public function findAtLeastOneForWorker(string $workerName);

    /**
     * @param array|null $orderBy
     *
     * @return QueueEntryInterface[]
     */
    public function findAll(array $orderBy = null);

    /**
     * @param string     $workerName
     * @param array|null $orderBy
     *
     * @return QueueEntryInterface[]
     */
    public function findAllForWorker(string $workerName, array $orderBy = null);
}
