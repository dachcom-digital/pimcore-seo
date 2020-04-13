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
     * @param object $resource
     *
     * @return boolean
     */
    public function supportsResource($resource);

    /**
     * @param object $resource
     *
     * @return mixed
     */
    public function generateQueueContext($resource);

    /**
     * @param QueueEntryInterface $queueEntry
     * @param string              $workerIdentifier
     * @param array               $context
     * @param mixed               $resource
     *
     * @return QueueEntryInterface|null
     */
    public function processQueueEntry(QueueEntryInterface $queueEntry, string $workerIdentifier, array $context, $resource);

    /**
     * @param WorkerResponseInterface $workerResponse
     *
     * @throws \Exception
     */
    public function processWorkerResponse(WorkerResponseInterface $workerResponse);
}