<?php

namespace SeoBundle\Queue;

use Pimcore\Model\Tool\TmpStore;
use SeoBundle\Logger\LoggerInterface;
use SeoBundle\Manager\QueueManagerInterface;

class QueueDataProcessor implements QueueDataProcessorInterface
{
    protected const LOCK_KEY = 'process_index';

    /**
     * @var QueueManagerInterface
     */
    protected $queueManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param QueueManagerInterface $queueManager
     * @param LoggerInterface       $logger
     */
    public function __construct(
        QueueManagerInterface $queueManager,
        LoggerInterface $logger
    ) {
        $this->queueManager = $queueManager;
        $this->logger = $logger;
    }

    /**
     * @param array $options
     */
    public function process(array $options)
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

    /**
     * {@inheritdoc}
     */
    public function isLocked(string $token)
    {
        return $this->getLockToken($token) instanceof TmpStore;
    }

    /**
     * {@inheritdoc}
     */
    public function getLockMessage(string $token)
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
            $token, $startDate, $executor, $failOverDate
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function lock(string $token, string $executor, $lifeTime = 14400)
    {
        if ($this->isLocked($token)) {
            return;
        }

        TmpStore::add($this->getNamespacedToken($token), $executor, null, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    protected function unlock(string $token)
    {
        TmpStore::delete($this->getNamespacedToken($token));
    }

    /**
     * @param string $token
     *
     * @return TmpStore|null
     */
    protected function getLockToken(string $token)
    {
        return TmpStore::get($this->getNamespacedToken($token));
    }

    /**
     * @param string $token
     * @param string $namespace
     *
     * @return string
     */
    protected function getNamespacedToken(string $token, string $namespace = 'seo')
    {
        return sprintf('%s_%s', $namespace, $token);
    }
}
