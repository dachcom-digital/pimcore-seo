<?php

namespace SeoBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use SeoBundle\Logger\LoggerInterface;
use SeoBundle\Model\QueueEntry;
use SeoBundle\Model\QueueEntryInterface;
use SeoBundle\Registry\IndexWorkerRegistryInterface;
use SeoBundle\Registry\ResourceProcessorRegistryInterface;
use SeoBundle\Repository\QueueEntryRepositoryInterface;
use SeoBundle\Worker\WorkerResponseInterface;
use SeoBundle\Exception\WorkerResponseInterceptException;

class QueueManager implements QueueManagerInterface
{
    protected array $enabledWorker;
    protected EntityManagerInterface $entityManager;
    protected QueueEntryRepositoryInterface $queueEntryRepository;
    protected ResourceProcessorRegistryInterface $resourceProcessorRegistry;
    protected IndexWorkerRegistryInterface $indexWorkerRegistry;
    protected LoggerInterface $logger;

    public function __construct(
        array $enabledWorker,
        EntityManagerInterface $entityManager,
        QueueEntryRepositoryInterface $queueEntryRepository,
        ResourceProcessorRegistryInterface $resourceProcessorRegistry,
        IndexWorkerRegistryInterface $indexWorkerRegistry,
        LoggerInterface $logger
    ) {
        $this->enabledWorker = $enabledWorker;
        $this->entityManager = $entityManager;
        $this->queueEntryRepository = $queueEntryRepository;
        $this->resourceProcessorRegistry = $resourceProcessorRegistry;
        $this->indexWorkerRegistry = $indexWorkerRegistry;
        $this->logger = $logger;
    }

    public function addToQueue(string $processType, mixed $resource): void
    {
        foreach ($this->enabledWorker as $workerIdentifier) {
            foreach ($this->resourceProcessorRegistry->getAll() as $resourceProcessorIdentifier => $resourceProcessor) {
                if ($resourceProcessor->supportsWorker($workerIdentifier) === false) {
                    continue;
                }

                if ($resourceProcessor->supportsResource($resource) === false) {
                    continue;
                }

                foreach ($resourceProcessor->generateQueueContext($resource) as $context) {
                    $entry = new QueueEntry();
                    $entry->setType($processType);
                    if (null !== $processedQueueEntryData = $resourceProcessor->processQueueEntry($entry, $workerIdentifier, $context, $resource)) {
                        $this->createQueueEntry($processType, $workerIdentifier, $resourceProcessorIdentifier, $processedQueueEntryData);
                    }
                }
            }
        }
    }

    public function processQueue(): void
    {
        foreach ($this->enabledWorker as $workerIdentifier) {
            if ($this->hasQueueData($workerIdentifier) === false) {
                continue;
            }

            try {
                $queuedData = $this->getQueuedData($workerIdentifier);
                $worker = $this->indexWorkerRegistry->get($workerIdentifier);

                if (true !== $processState = $worker->canProcess()) {
                    if (is_string($processState)) {
                        $this->logger->log('warning', sprintf('Worker %s process skipped. %s', $workerIdentifier, $processState));
                    }
                } else {
                    $worker->process($queuedData, [$this, 'processResponse']);
                }
            } catch (\Throwable $e) {
                $this->logger->log('error', sprintf('Error sending queued entries to worker %s. Message was: %s', $workerIdentifier, $e->getMessage()));
            }
        }
    }

    /**
     * @throws \Exception
     *
     * @internal
     */
    public function processResponse(WorkerResponseInterface $workerResponse): void
    {
        $resourceProcessorIdentifier = $workerResponse->getQueueEntry()->getResourceProcessor();
        $resourceProcessor = $this->resourceProcessorRegistry->get($resourceProcessorIdentifier);

        $logContext = [];
        if ($workerResponse->getQueueEntry()->getDataType() === 'pimcore_object') {
            $logContext = ['relatedObject' => $workerResponse->getQueueEntry()->getDataId()];
        }

        try {
            $resourceProcessor->processWorkerResponse($workerResponse);
        } catch (WorkerResponseInterceptException $e) {
            // nothing to do, processor intercepted response. remove queue entry and return.
            $this->removeFromQueue($workerResponse->getQueueEntry());

            return;
        } catch (\Throwable $e) {
            $message = sprintf('Error parsing worker response in "%s" processor. Message was: %s', $resourceProcessorIdentifier, $e->getMessage());
            $this->logger->log('error', $message, $logContext);

            return;
        }

        // always remove from queue
        $this->removeFromQueue($workerResponse->getQueueEntry());

        if ($workerResponse->isDone() === false) {
            $message = sprintf('Processing data with worker %s failed. %s', $workerResponse->getQueueEntry()->getWorker(), $workerResponse->getMessage());
            $this->logger->log('warning', $message, $logContext);

            return;
        }

        $message = sprintf('Processing data with worker %s was successfully. %s', $workerResponse->getQueueEntry()->getWorker(), $workerResponse->getMessage());

        $this->logger->log('info', $message, $logContext);
    }

    protected function hasQueueData(string $workerIdentifier): bool
    {
        return $this->queueEntryRepository->findAtLeastOneForWorker($workerIdentifier) instanceof QueueEntryInterface;
    }

    protected function getQueuedData(string $workerIdentifier): array
    {
        $data = [];
        $removableEntries = [];
        $queuedEntries = $this->queueEntryRepository->findAllForWorker($workerIdentifier, ['creationDate' => 'DESC']);

        foreach ($queuedEntries as $queuedEntry) {
            $key = $this->generateEntryKey($queuedEntry);
            if (isset($data[$key])) {
                $removableEntries[] = $queuedEntry;

                continue;
            }

            $data[$key] = $queuedEntry;
        }

        if (count($removableEntries) > 0) {
            $this->removeMultipleFromQueue($removableEntries);
        }

        return $data;
    }

    protected function createQueueEntry(string $processType, string $workerIdentifier, string $resourceProcessorIdentifier, QueueEntryInterface $queueEntry): void
    {
        if (empty($queueEntry->getDataUrl())) {
            $this->logger->log(
                'warning',
                sprintf(
                    'Queue entry (type: %s, id: %s) has no valid data url. Skipping persistence...',
                    $queueEntry->getDataType(),
                    $queueEntry->getDataId()
                )
            );

            return;
        }

        try {
            $queueEntry->setType($processType); // override it again to ensure type contains no manipulated data
            $queueEntry->setCreationDate(new \DateTime());
            $queueEntry->setResourceProcessor($resourceProcessorIdentifier);
            $queueEntry->setWorker($workerIdentifier);
            $this->storeInQueue($queueEntry);
        } catch (\Throwable $e) {
            $this->logger->log('error', sprintf('Error creating queue entry. %s', $e->getMessage()));
        }
    }

    protected function generateEntryKey(QueueEntryInterface $queueEntry): string
    {
        return md5(sprintf(
            '%s_%s_%s',
            $queueEntry->getDataId(),
            $queueEntry->getDataType(),
            $queueEntry->getDataUrl()
        ));
    }

    protected function storeInQueue(QueueEntryInterface $queueEntry): void
    {
        $this->entityManager->persist($queueEntry);
        $this->entityManager->flush();
    }

    protected function removeFromQueue(QueueEntryInterface $queueEntry): void
    {
        $this->entityManager->remove($queueEntry);
        $this->entityManager->flush();
    }

    /**
     * @param QueueEntryInterface[] $queueEntries
     */
    protected function removeMultipleFromQueue(array $queueEntries): void
    {
        foreach ($queueEntries as $queueEntry) {
            $this->entityManager->remove($queueEntry);
        }

        $this->entityManager->flush();
    }
}
