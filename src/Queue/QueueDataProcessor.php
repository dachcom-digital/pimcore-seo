<?php

namespace SeoBundle\Queue;

use Pimcore\Model\Tool\TmpStore;
use SeoBundle\Logger\LoggerInterface;
use SeoBundle\Manager\QueueManagerInterface;

class QueueDataProcessor implements QueueDataProcessorInterface
{
    protected const LOCK_KEY = 'process_index';

    public function __construct(
       protected QueueManagerInterface $queueManager,
       protected LoggerInterface $logger
    ) {
    }

    public function process(array $options): void
    {
        if ($this->isLocked(self::LOCK_KEY)) {
            return;
        }

        $this->lock(self::LOCK_KEY, 'queue index data processor via maintenance/command');

        try {
            $this->queueManager->processQueue();
        } catch (\Throwable $e) {
            $this->logger->log('error', sprintf('Error while processing queued index data. %s', $e->getMessage()));
        }

        $this->unlock(self::LOCK_KEY);
    }

    public function isLocked(string $token): bool
    {
        return $this->getLockToken($token) instanceof TmpStore;
    }

    public function getLockMessage(string $token): string
    {
        if (!$this->isLocked($token)) {
            return 'not-locked';
        }

        $tmpStore = $this->getLockToken($token);
        $startDate = date('m-d-Y H:i:s', $tmpStore->getDate());
        $failOverDate = date('m-d-Y H:i:s', $tmpStore->getExpiryDate());
        $executor = $tmpStore->getData();

        return sprintf(
            'Process "%s" has been locked at %s by "%s" and will stay locked until process is finished with a self-delete failover at %s',
            $token,
            $startDate,
            $executor,
            $failOverDate
        );
    }

    protected function lock(string $token, string $executor, $lifeTime = 14400): void
    {
        if ($this->isLocked($token)) {
            return;
        }

        TmpStore::add($this->getNamespacedToken($token), $executor, null, $lifeTime);
    }

    protected function unlock(string $token): void
    {
        TmpStore::delete($this->getNamespacedToken($token));
    }

    protected function getLockToken(string $token): ?TmpStore
    {
        return TmpStore::get($this->getNamespacedToken($token));
    }

    protected function getNamespacedToken(string $token, string $namespace = 'seo'): string
    {
        return sprintf('%s_%s', $namespace, $token);
    }
}
