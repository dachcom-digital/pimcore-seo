<?php

namespace SeoBundle\ResourceProcessor;

use SeoBundle\Exception\WorkerResponseInterceptException;
use SeoBundle\Model\QueueEntryInterface;
use SeoBundle\Worker\WorkerResponseInterface;

interface ResourceProcessorInterface
{
    /**
     * @param string $workerIdentifier
     *
     * @return bool
     */
    public function supportsWorker(string $workerIdentifier);

    /**
     * @param object $resource
     *
     * @return bool
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
     * @throws WorkerResponseInterceptException
     */
    public function processWorkerResponse(WorkerResponseInterface $workerResponse);
}
