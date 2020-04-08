<?php

namespace SeoBundle\ResourceProcessor;

use SeoBundle\Model\QueueEntryInterface;
use SeoBundle\Worker\WorkerResponseInterface;

interface ResourceProcessorInterface
{
    /**
     * @param string $workerIdentifier
     *
     * @return boolean
     */
    public function supportsWorker(string $workerIdentifier);

    /**
     * @param QueueEntryInterface $queueEntry
     * @param string              $workerIdentifier
     * @param mixed               $resource
     *
     * @return QueueEntryInterface|null
     */
    public function processQueueEntry(QueueEntryInterface $queueEntry, string $workerIdentifier, $resource);

    /**
     * @param WorkerResponseInterface $workerResponse
     *
     * @throws \Exception
     */
    public function processWorkerResponse(WorkerResponseInterface $workerResponse);
}