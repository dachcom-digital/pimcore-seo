<?php

namespace SeoBundle\ResourceProcessor;

use SeoBundle\Exception\WorkerResponseInterceptException;
use SeoBundle\Model\QueueEntryInterface;
use SeoBundle\Worker\WorkerResponseInterface;

interface ResourceProcessorInterface
{
    public function supportsWorker(string $workerIdentifier): bool;

    public function supportsResource(mixed $resource): bool;

    public function generateQueueContext(mixed $resource): mixed;

    public function processQueueEntry(QueueEntryInterface $queueEntry, string $workerIdentifier, array $context, mixed $resource): ?QueueEntryInterface;

    /**
     * @throws \Exception
     * @throws WorkerResponseInterceptException
     */
    public function processWorkerResponse(WorkerResponseInterface $workerResponse);
}
