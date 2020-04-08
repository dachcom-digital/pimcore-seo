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

class QueueManager implements QueueManagerInterface
{
    /**
     * @var array
     */
    protected $enabledWorker;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var QueueEntryRepositoryInterface
     */
    protected $queueEntryRepository;

    /**
     * @var ResourceProcessorRegistryInterface
     */
    protected $resourceProcessorRegistry;

    /**
     * @var IndexWorkerRegistryInterface
     */
    protected $indexWorkerRegistry;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param array                              $enabledWorker
     * @param EntityManagerInterface             $entityManager
     * @param QueueEntryRepositoryInterface      $queueEntryRepository
     * @param ResourceProcessorRegistryInterface $resourceProcessorRegistry
     * @param IndexWorkerRegistryInterface       $indexWorkerRegistry
     * @param LoggerInterface                    $logger
     */
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

    /**
     * {@inheritDoc}
     */
    public function addToQueue(string $processType, $resource)
    {
        foreach ($this->enabledWorker as $workerIdentifier) {

            $entry = new QueueEntry();
            $entry->setType($processType);

            foreach ($this->resourceProcessorRegistry->getAll() as $resourceProcessorIdentifier => $resourceProcessor) {

                if ($resourceProcessor->supportsWorker($workerIdentifier) === false) {
                    continue;
                }

                if (null !== $processedQueueEntry = $resourceProcessor->processQueueEntry($entry, $workerIdentifier, $resource)) {
                    $this->createQueueEntry($processType, $workerIdentifier, $resourceProcessorIdentifier, $processedQueueEntry);
                    break;  // only one process allowed!
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function processQueue()
    {
        foreach ($this->enabledWorker as $workerIdentifier) {

            if ($this->hasQueueData($workerIdentifier) === false) {
                continue;
            }

            // we cannot stream here
            $queuedData = $this->getQueuedData($workerIdentifier);

            try {
                $worker = $this->indexWorkerRegistry->get($workerIdentifier);
                $worker->process($queuedData, [$this, 'processResponse']);
            } catch (\Throwable $e) {
                $this->logger->log('error', sprintf('Error sending queued entries to worker %s. Message was: %s', $workerIdentifier, $e->getMessage()));
            }
        }
    }

    /**
     * @param WorkerResponseInterface $workerResponse
     *
     * @throws \Exception
     * @internal
     */
    public function processResponse(WorkerResponseInterface $workerResponse)
    {
        $resourceProcessorIdentifier = $workerResponse->getQueueEntry()->getResourceProcessor();
        $resourceProcessor = $this->resourceProcessorRegistry->get($resourceProcessorIdentifier);

        $logContext = [];
        if ($workerResponse->getQueueEntry()->getDataType() === 'pimcore_object') {
            $logContext = ['relatedObject' => $workerResponse->getQueueEntry()->getDataId()];
        }

        try {
            $resourceProcessor->processWorkerResponse($workerResponse);
        } catch (\Throwable $e) {
            $message = sprintf('Error parsing worker response in "%s" processor. Message was: %s', $resourceProcessorIdentifier, $e->getMessage());
            $this->logger->log('error', $message, $logContext);
            return;
        }

        if ($workerResponse->isDone() === false) {
            $message = sprintf('Processing data with worker %s failed. %s', $workerResponse->getQueueEntry()->getWorker(), $workerResponse->getMessage());
            $this->logger->log('warning', $message, $logContext);
            return;
        }

        $message = sprintf('Processing data with worker %s was successfully. %s', $workerResponse->getQueueEntry()->getWorker(), $workerResponse->getMessage());

        $this->logger->log('info', $message, $logContext);

        $this->removeFromQueue($workerResponse->getQueueEntry());
        unset($workerResponse);
    }

    /**
     * @param string $workerIdentifier
     *
     * @return bool
     */
    protected function hasQueueData(string $workerIdentifier)
    {
        return $this->queueEntryRepository->findAtLeastOneForWorker($workerIdentifier) instanceof QueueEntryInterface;
    }

    /**
     * @param string $workerIdentifier
     *
     * @return array
     */
    protected function getQueuedData(string $workerIdentifier)
    {
        $queuedEntries = $this->queueEntryRepository->findAllForWorker($workerIdentifier, ['creationDate' => 'DESC']);
        $data = [];
        $removableEntries = [];
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

    /**
     * @param string              $processType
     * @param string              $workerIdentifier
     * @param string              $resourceProcessorIdentifier
     * @param QueueEntryInterface $queueEntry
     */
    protected function createQueueEntry(string $processType, string $workerIdentifier, string $resourceProcessorIdentifier, QueueEntryInterface $queueEntry)
    {
        if (!$queueEntry instanceof QueueEntryInterface) {
            return;
        }

        try {
            $queueEntry->setType($processType); // override it again to ensure type has no manipulated data during processing!
            $queueEntry->setCreationDate(new \DateTime());
            $queueEntry->setResourceProcessor($resourceProcessorIdentifier);
            $queueEntry->setWorker($workerIdentifier);
            $this->storeInQueue($queueEntry);
        } catch (\Throwable $e) {
            $this->logger->log('error', sprintf('Error creating queue entry. %s', $e->getMessage()));
        }
    }

    /**
     * @param QueueEntryInterface $queueEntry
     *
     * @return string
     */
    protected function generateEntryKey(QueueEntryInterface $queueEntry)
    {
        return md5(sprintf('%s_%s',
            $queueEntry->getDataId(),
            $queueEntry->getDataType()
        ));
    }

    /**
     * @param QueueEntryInterface $queueEntry
     */
    protected function storeInQueue(QueueEntryInterface $queueEntry)
    {
        $this->entityManager->persist($queueEntry);
        $this->entityManager->flush();
    }

    /**
     * @param QueueEntryInterface $queueEntry
     */
    protected function removeFromQueue(QueueEntryInterface $queueEntry)
    {
        $this->entityManager->remove($queueEntry);
        $this->entityManager->flush();
    }

    /**
     * @param QueueEntryInterface[] $queueEntries
     */
    protected function removeMultipleFromQueue(array $queueEntries)
    {
        foreach ($queueEntries as $queueEntry) {
            $this->entityManager->remove($queueEntry);
        }

        $this->entityManager->flush();
    }
}
